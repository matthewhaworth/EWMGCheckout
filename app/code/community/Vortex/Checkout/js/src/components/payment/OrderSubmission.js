import React, {Component} from 'react';
import Checkbox from "../common/Checkbox";
import Totals from "../basket/Totals";
import {withRouter} from 'react-router-dom';
import CmsApi from "../../api/CmsApi";
import RawHtml from "../../components/common/RawHtml";

class OrderSubmission extends Component {
    constructor(props, context) {
        super(props, context);

        this.state = {
            newsletter: true,
            thirdParty: true,
            processingSubmit: false,
            termsContent: '',
            termsActive: false
        };
    }

    componentWillMount(){
        CmsApi.loadCmsContent('terms').then((resp) => {
            this.setState({termsContent: resp.html});
        }).catch(() => {
            this.setState({termsContent: 'Terms content on network error'});
        });

    }

    onOrderSubmit(history) {
        if (!this.props.basket.shipping_address.hasOwnProperty('address_id') || this.props.basket.shipping_address.address_id == '') {
            this.props.addGlobalError('Please scroll up and enter your delivery address.');
            return;
        }

        this.setState({processingSubmit: true});
        this.props.onOrderSubmit(history, this.state.newsletter, this.state.thirdParty).catch(() => {
            this.setState({processingSubmit: false});
        });
    }

    toggleTerms(e){
        e.preventDefault();
        this.setState({termsActive: !this.state.termsActive})
    }

    render() {
        const {basket, canPlaceOrder, addDiscountCode, removeDiscountCode} = this.props;

        const isZeroOrder = parseFloat(basket.total) === parseFloat(0);

        const OrderSubmitButton = withRouter(({history}) =>
            <button type='button'
                    disabled={!canPlaceOrder || this.state.processingSubmit}
                    onClick={(e) => this.onOrderSubmit(history)}
                    className={'button button--primary ' + (this.state.processingSubmit ? 'button--loading' : 'button--arrow-right')}>
                <span>{isZeroOrder ? `Place order` : `Pay securely - ${basket.total_with_symbol}`}</span>
            </button>
        );

        return (
            <div>
                <Totals basket={basket}
                        displayType="secondary"
                        includeGiftCard
                        addDiscountCode={code => addDiscountCode(code)}
                        removeDiscountCode={() => removeDiscountCode()}/>

                <Checkbox label='Sign up to our newsletter? We’ll send you discounts and personalised offers!'
                          name='newsletter'
                          checked={this.state.newsletter}
                          onToggle={(e) => this.setState({ newsletter: e.target.checked })}
                          checkboxAdditionalClassNames="form__checkbox--smalltext"/>

                <div className="form__control form__control--actions">
                    <OrderSubmitButton/>
                    <p className="note">By placing your order, you agree to our<br/><a href="#" onClick={(e) => this.toggleTerms(e)}>Terms & Conditions</a></p>
                </div>

                { this.state.termsContent !== '' && <div className={'checkout-modal ' + (this.state.termsActive ? 'active' : '')}>
                    <div onClick={(e) => this.toggleTerms(e)} className="checkout-loader checkout-loader--large checkout-loader--dark checkout-loader--overlay">
                        <div className="checkout-loader__spinner"><span /></div>
                    </div>
                    <div className="checkout-modal__container">
                        <a className="checkout-modal__close button--icon-close"
                           onClick={(e) => this.toggleTerms(e)} />

                        <div className="checkout-terms">
                            <div className="checkout-terms__container">
                                <RawHtml className="cms" output={this.state.termsContent}/>
                            </div>
                        </div>

                    </div>
                </div>}

            </div>
        );
    }
};

export default OrderSubmission;