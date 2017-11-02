import React, {Component} from 'react';
import {connect} from "react-redux";
import {bindActionCreators} from "redux";
import * as basketActions from "../actions/basketActions";
import ListMethods from "../components/shippingmethod/ListMethods";

class ShippingMethodContainer extends Component {
    constructor(props, context) {
        super(props, context);

        this.state = { shippingMethod: this.props.basket.shipping_method };
    }

    setShippingMethod(event) {
        this.setState({shippingMethod: event.target.value});
    }

    onContinue() {
        this.props.basketActions.setShippingMethod(this.state.shippingMethod).then(() => this.props.onContinue());
    }

    render() {
        const {basket} = this.props;

        let NoMethods = () => <div>No Methods</div>;
        let ShippingMethods = basket.available_shipping_methods.length > 0 ? ListMethods : NoMethods;

        return (
            <div className="checkout-section checkout-section--checkout">
                <div className="checkout-section__container">
                    <p>Delivery Methods</p>

                    <ShippingMethods shippingMethods={basket.available_shipping_methods}
                                     selectedShippingMethod={this.state.shippingMethod}
                                     onSetShippingMethod={(e) => this.setShippingMethod(e)} />

                    <div className="form__control form__control--actions">
                        <button type='button'
                                onClick={(e) => this.onContinue(e)}
                                className="button button--primary button--arrow-right"
                                disabled={this.state.shippingMethod === null}>
                            <span>Pay by card</span>
                        </button>
                    </div>
                    <div className={'checkout-section__fader ' + (this.props.active ? '' : 'active')} />
                </div>
            </div>
        );
    }
}

const mapStateToProps = (state) => {
    return {
        basket: state.basket
    }
};

const mapDispatchToProps = (dispatch) => {
    return {
        basketActions: bindActionCreators(basketActions, dispatch)
    }
};

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(ShippingMethodContainer);