import React, {Component} from 'react';
import ListItems from "../components/basket/ListItems";
import Totals from "../components/basket/Totals";
import {connect} from "react-redux";
import {bindActionCreators} from "redux";
import * as basketActions from "../actions/basketActions";
import Pluralize from 'react-pluralize';
import CmsApi from "../api/CmsApi";
import RawHtml from "../components/common/RawHtml";
import * as checkoutSteps from '../store/checkoutSteps';
import * as ReactDOM from 'react-dom';

class BasketContainer extends Component {
    constructor(props, context) {
        super(props, context);

        let itemQtyMap = {};
        props.basket.items.forEach((item) => itemQtyMap[item.item_id] = item.qty);

        this.state = {
            itemsSaving: [],
            itemsExpanded: false,
            itemQty: itemQtyMap,
            crossSellContent: '',
            deliveryContent: ''
        };
    }

    componentWillMount()
    {

        CmsApi.loadCmsContent('delivery').then((resp) => {
            this.setState({deliveryContent: resp.html});
        }).catch(() => {
            this.setState({deliveryContent: 'Delivery content on network error'});
        });

        CmsApi.loadCmsContent('crosssell').then((resp) => {
            this.setState({crossSellContent: resp.html});
        }).catch(() => {
            this.setState({crossSellContent: 'Crossell content on network error'});
        })
    }

    componentWillReceiveProps(nextProps) {
        let itemQtyMap = {};
        nextProps.basket.items.forEach((item) => itemQtyMap[item.item_id] = item.qty);

        this.setState({ itemQty: itemQtyMap });
    }

    componentDidUpdate() {
        this.scrollToView();
    }

    onQuantityChange(item, itemQty, event) {
        event.preventDefault();

        let itemsSaving = this.state.itemsSaving;
        itemsSaving.push(item.item_id);
        this.setState({itemsSaving});

        const originalItem = this.props.basket.items.filter(basketItem => basketItem.item_id === item.item_id)[0];
        this.props.basketActions.changeQuantity(item, itemQty).then((basket) => {
            let itemQtyMap = {};
            basket.items.forEach((item) => itemQtyMap[item.item_id] = item.qty);

            let newItemsSaving = this.state.itemsSaving.filter((itemId) => itemId !== item.item_id);
            this.setState({itemsSaving: newItemsSaving, itemQty: itemQtyMap});
        }).catch(err => {
            let newItemsSaving = this.state.itemsSaving.filter((itemId) => itemId !== item.item_id);
            this.setState({
                itemsSaving: newItemsSaving,
                itemQty: {
                    ...this.state.itemQty,
                    [item.item_id]: originalItem.qty
                }
            });
        })
    }

    onQuantityKeyChange(item, event) {
        this.setState({
            itemQty: {
                ...this.state.itemQty,
                [item.item_id]: event.target.value
            }
        });
    }

    onQuantityKeyPress(item, event) {
        if (event.key !== 'Enter') {
            return;
        }

        this.onQuantityChange(item, this.state.itemQty[item.item_id], event)
    }

    toggleItemsExpanded() {
        this.setState({itemsExpanded: !this.state.itemsExpanded});
    }

    scrollToView(){
        const node = ReactDOM.findDOMNode(this.refs.STATE_BASKET);

        if(node && typeof this.props.scrollToView === 'undefined' && this.props.scrollToView()){
            node.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
                inline: 'start'
            });
        }
    }

    render() {
        const {basket, checkout} = this.props;

        const hideDelivery = !checkout.step;

        return (
            <div className="checkout-layout__column checkout-layout__column--basket" ref={checkoutSteps.STATE_BASKET}>
                <div className={'checkout-section checkout-section--basket ' + (this.state.itemsExpanded ? 'expanded' : '')}>
                    <div className="checkout-section__title">
                        <div className="checkout-section__title-text">
                            <Pluralize singular="item" count={parseInt(basket.item_count)} /> in your bag
                        </div>
                        <div className="checkout-section__title-actions">
                            <button className="button button--small button--primary" onClick={() => this.props.onContinue()}>
                                Checkout
                            </button>
                        </div>
                        <a className="checkout-section__title-link" onClick={(e) => this.toggleItemsExpanded(e)}>
                            <span className="checkout-section__title-count">{basket.item_count}</span> items in your bag
                        </a>
                    </div>
                    <div className="checkout-section__container">
                        <ListItems items={basket.items}
                                   itemsSaving={this.state.itemsSaving}
                                   itemQuantities={this.state.itemQty}
                                   onQuantityKeyChange={(item, event) => this.onQuantityKeyChange(item, event)}
                                   onQuantityKeyPress={(item, event) => this.onQuantityKeyPress(item, event)}
                                   onQuantityChange={(item, itemQty, event) => this.onQuantityChange(item, itemQty, event)}
                                   showFinalPrice/>

                        <Totals basket={basket}
                                displayType="primary"
                                includeGiftCard
                                addDiscountCode={((code) => this.props.basketActions.addDiscountCode(code))}
                                removeDiscountCode={() => this.props.basketActions.removeDiscountCode()}
                                onContinue={() => this.props.onContinue()}
                                hideDelivery={hideDelivery} />

                        {this.state.crossSellContent !=='' && <RawHtml className="checkout-basket__crosssell" output={this.state.crossSellContent}/>}

                        {this.state.deliveryContent !== '' && <div className="checkout-basket__deliveryinfo">
                            <div className="checkout-deliveryinfo">
                                <div className="checkout-deliveryinfo__container">
                                    <RawHtml className="cms" output={this.state.deliveryContent}/>
                                </div>
                            </div>
                        </div>}

                    </div>
                </div>
            </div>
        );
    }
}

const mapStateToProps = (state) => {
    return {
        basket: state.basket,
        checkout: state.checkout,
        errors: state.errors
    }
};

const mapDispatchToProps = (dispatch) => {
    return {
        basketActions: bindActionCreators(basketActions, dispatch)
    }
};

export default connect(mapStateToProps, mapDispatchToProps)(BasketContainer);