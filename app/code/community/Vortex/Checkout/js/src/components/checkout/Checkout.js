import React, {Component} from 'react';
import BodyClass from '../common/BodyClass';
import EmptyBasket from '../common/EmptyBasket';
import BasketContainer from "../../containers/BasketContainer";
import Header from "../header/Header";
import ShippingContainer from "../../containers/ShippingContainer";
import PaymentContainer from "../../containers/PaymentContainer";
import LoginContainer from "../../containers/LoginContainer";
import {STATE_DELIVERY, STATE_EMAIL, STATE_PAYMENT} from "../../store/checkoutSteps";
import GlobalErrors from '../common/GlobalErrors';
import DataLayer from "../common/DataLayer";

class Checkout extends Component {
    constructor(props, context) {
        super(props, context);

        this.state = { loading: true };

        props.fetchBasket().then(() => {
            props.getLoggedInCustomer().then(() => {
                this.setState({ displayLoginContainer: false });
            }).catch(() => {
                // Customer will not return if it does not exist, as server returns 4xx
                this.setState({ displayLoginContainer: true });
            }).then(() => {
                this.setState({ loading: false });
            });
        });
    }

    render() {
        if (this.state.loading) {
            return this.renderLoading();
        }

        if (this.props.basket && this.props.basket.item_count > 0) {
            return this.renderCheckout();
        } else {
            return this.renderNoItems();
        }
    }

    onBasketContinue() {
        if (this.props.customer.hasOwnProperty('id')) {
            this.props.changeCheckoutSection(STATE_DELIVERY);
        } else {
            this.props.changeCheckoutSection(STATE_EMAIL);
        }
    }

    /**
     * There are two contexts in which you can proceed from the login box
     *
     * 1. Continue as guest
     * 2. Continue as logged in user
     */
    onLoginContinue() {
        // Context 2, continue as logged in user.
        if (this.props.customer.hasOwnProperty('id')) {
            const fetchBasket = this.props.fetchBasket();
            const changeCheckoutSection = this.props.changeCheckoutSection(STATE_DELIVERY);

            Promise.all([fetchBasket, changeCheckoutSection]).then(() => {
                this.setState({ displayLoginContainer: false });
            });
        } else {
            this.props.changeCheckoutSection(STATE_DELIVERY);
        }
    }

    onShippingContinue() {
        this.props.changeCheckoutSection(STATE_PAYMENT);
    }

    onPaymentMethodContinue() {
        //this.props.changeCheckoutSection(DONE);
    }

    renderCheckout() {
        const {checkout, basket} = this.props;

        const isZeroOrder = parseFloat(basket.total) === parseFloat(0);

        return (
            <div>
                <Header checkoutStep={checkout.step}
                        shippingCost={basket.shipping}
                        shippingCostWithSymbol={basket.shipping_with_symbol}
                        basketTotalWithSymbol={basket.total_with_symbol} />

                <GlobalErrors />

                <div className="checkout-maincontent">
                    <div className="checkout-maincontent__wrapper">
                        <div className="checkout-maincontent__container">
                            <div className="checkout-layout checkout-layout--animate">
                                <BasketContainer onContinue={() => this.onBasketContinue()}/>

                                <div className="checkout-layout__column checkout-layout__column--checkout">

                                    <div className="checkout-section__before-title checkout-section__before-title--primary">
                                        <span className="checkout-section__before-count">2</span>Delivery
                                    </div>

                                    {this.state.displayLoginContainer && <LoginContainer active={checkout.visited.includes(STATE_EMAIL)}
                                                                                         onContinue={() => this.onLoginContinue()} />}

                                    <ShippingContainer active={checkout.visited.includes(STATE_DELIVERY)}
                                                       onContinue={() => this.onShippingContinue()} />

                                    {!isZeroOrder && <PaymentContainer active={checkout.visited.includes(STATE_PAYMENT)}
                                                      onContinue={() => this.onPaymentMethodContinue()} />}
                                </div>
                            </div>
                        </div>
                    </div>
                    <BodyClass checkoutStep={checkout.step} />
                    <DataLayer checkoutStep={checkout.step} />
                </div>
            </div>
        );
    }

    renderLoading() {
        return <div className="checkout-loader checkout-loader--large checkout-loader--overlay active">
            <div className="checkout-loader__spinner"><span></span></div>
        </div>;
    }

    renderNoItems() {
        return (
            <EmptyBasket customer={this.props.customer}
                         customerExists={this.props.customer.existingCustomer}
                         step={this.props.checkout.step} />
        );
    }
}

export default Checkout;