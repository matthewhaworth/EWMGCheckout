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
import * as checkoutSteps from '../../store/checkoutSteps';
import * as ReactDOM from 'react-dom';

class Checkout extends Component {

    constructor(props, context) {
        super(props, context);

        this.state = { loading: true };

        props.getLoggedInCustomer().then(() => {
            this.setState({ displayLoginContainer: false });
        }).catch(() => {
            // Customer will not return if it does not exist, as server returns 4xx
            this.setState({ displayLoginContainer: true });
        }).then(() => {
            props.fetchBasket().then(() => {
                this.setState({loading: false});
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

    scrollToViewBasket() {
        this.basketContainer.getWrappedInstance().scrollToView();
    }

    scrollToViewPayment() {
        this.paymentContainer.getWrappedInstance().scrollToView();
    }

    // EWMOC-47: Use if you to scroll to email section
    // scrollToViewEmail() {
    //
    // }

    scrollToView() {
        const node = ReactDOM.findDOMNode(this.refs.STATE_DELIVERY);

        if(node && !this.state.loading){
            node.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
                inline: 'start'
            });
        }
    }

    onBasketContinue() {
        if (this.props.customer.hasOwnProperty('id')) {
            const nextSection = this.props.basket.is_virtual ? STATE_PAYMENT : STATE_DELIVERY;
            this.props.changeCheckoutSection(nextSection);
            this.scrollToViewBasket();
        } else {
            this.props.changeCheckoutSection(STATE_EMAIL);
            //EWMOC-47: Use if you to scroll to email section
            //this.scrollToViewEmail();
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
            const nextSection = this.props.basket.is_virtual ? STATE_PAYMENT : STATE_DELIVERY;
            const changeCheckoutSection = this.props.changeCheckoutSection(nextSection);

            Promise.all([fetchBasket, changeCheckoutSection]).then(() => {
                this.setState({ displayLoginContainer: false });
                this.scrollToView();
            });
        } else {
            const nextSection = this.props.basket.is_virtual ? STATE_PAYMENT : STATE_DELIVERY;
            this.props.changeCheckoutSection(nextSection);
            this.scrollToView();
        }
    }

    onShippingContinue() {
        this.props.changeCheckoutSection(STATE_PAYMENT);
        this.scrollToViewPayment();
    }

    onPaymentMethodContinue() {
        //this.props.changeCheckoutSection(DONE);
        this.scrollToViewPayment();
    }

    renderCheckout() {
        const {checkout, basket} = this.props;

        const isZeroOrder = parseFloat(basket.total) === parseFloat(0);
        const deliveryIsRequired = !this.props.basket.is_virtual;
        return (
            <div>
                <Header checkoutStep={checkout.step}
                        shippingCost={basket.shipping}
                        shippingCostWithSymbol={basket.shipping_with_symbol}
                        basketTotalWithSymbol={basket.subtotal_incl_discount_currency}
                        grandTotalWithSymbol={basket.total_with_symbol} />

                <GlobalErrors />

                <div className="checkout-maincontent">
                    <div className="checkout-maincontent__wrapper">
                        <div className="checkout-maincontent__container">
                            <div className="checkout-layout checkout-layout--animate">
                                <BasketContainer onContinue={() => this.onBasketContinue()} ref={(basket) => {this.basketContainer = basket}} />

                                <div className="checkout-layout__column checkout-layout__column--checkout">

                                    {deliveryIsRequired && <div className="checkout-section__before-title checkout-section__before-title--primary" ref={checkoutSteps.STATE_DELIVERY}>
                                        <span className="checkout-section__before-count">2</span>Delivery
                                    </div>}

                                    {this.state.displayLoginContainer && <LoginContainer active={checkout.visited.includes(STATE_EMAIL)}
                                                                                         onContinue={() => this.onLoginContinue()} />}

                                    {deliveryIsRequired && <ShippingContainer active={checkout.visited.includes(STATE_DELIVERY)}
                                                       onContinue={() => this.onShippingContinue()}/>}

                                    {!isZeroOrder && <PaymentContainer active={checkout.visited.includes(STATE_PAYMENT)}
                                                      onContinue={() => this.onPaymentMethodContinue()}
                                                      ref={(payment) => {this.paymentContainer = payment}}/>}
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