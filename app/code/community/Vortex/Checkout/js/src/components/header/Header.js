import React, {Component} from 'react';
import * as checkoutSteps from "../../store/checkoutSteps";
import classNames from 'classnames';


class Header extends Component {

    shoppingBagSteps = [
        checkoutSteps.steps.indexOf(checkoutSteps.STATE_BASKET),
        checkoutSteps.steps.indexOf(checkoutSteps.STATE_EMAIL),
        checkoutSteps.steps.indexOf(checkoutSteps.STATE_PASSWORD)
    ];

    deliverySteps = [
        checkoutSteps.steps.indexOf(checkoutSteps.STATE_DELIVERY)
    ];

    paymentSteps = [
        checkoutSteps.steps.indexOf(checkoutSteps.STATE_PAYMENT),
        checkoutSteps.steps.indexOf(checkoutSteps.STATE_BILLING_ADDRESS)
    ];

    getSectionClass(sectionSteps) {
        let isActive = this.isActive(sectionSteps);
        let isComplete = this.isComplete(sectionSteps);

        return classNames({
            'checkout-progress__step': true,
            'active': isActive,
            'complete': isComplete,
            'inactive': !isActive && !isComplete
        });
    }

    getIsClickable(sectionSteps){
        let isActive = this.isActive(sectionSteps);
        let isComplete = this.isComplete(sectionSteps);

        return isActive || isComplete;
    }

    isActive(steps) {
        return steps.includes(this.props.checkoutStep);
    }

    isComplete(steps) {
        return this.props.checkoutStep > Math.max(...steps) || this.props.checkoutStep === checkoutSteps.STATE_SUCCESS;
    }

    getShoppingBagClasses() {
        return this.getSectionClass(this.shoppingBagSteps);
    }

    getDeliveryClasses() {
        return this.getSectionClass(this.deliverySteps);
    }

    getPaymentClasses() {
        return this.getSectionClass(this.paymentSteps);
    }

    render() {
        return (
            <div className="checkout-header">
                <div className="checkout-header__wrapper">
                    <div className="checkout-header__container">
                        <div className="checkout-header__top">
                            <div className="checkout-progress">
                                <ol className="checkout-progress__steps-list">
                                    <li onClick={(step, clickable) => this.props.onClick(checkoutSteps.STATE_BASKET, this.getIsClickable(this.shoppingBagSteps))} className={this.getShoppingBagClasses()}>
                                        <span className="checkout-progress__title"><em>Shopping </em>Bag</span>
                                        <span className="checkout-progress__subtitle">{this.props.basketTotalWithSymbol}</span>
                                    </li>
                                    <li onClick={(step, clickable) => this.props.onClick(checkoutSteps.STATE_DELIVERY, this.getIsClickable(this.deliverySteps))} className={this.getDeliveryClasses()}>
                                        <span className="checkout-progress__title">Delivery</span>
                                        <span className="checkout-progress__subtitle">{this.props.shippingCost ? this.props.shippingCostWithSymbol : 'FREE'}</span>
                                    </li>
                                    <li onClick={(step, clickable) => this.props.onClick(checkoutSteps.STATE_PAYMENT, this.getIsClickable(this.paymentSteps))} className={this.getPaymentClasses()}>
                                        <span className="checkout-progress__title">Payment</span>
                                        <span className="checkout-progress__subtitle">{this.props.grandTotalWithSymbol}</span>
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

export default Header;