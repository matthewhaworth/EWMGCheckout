import React from 'react'
import * as checkoutSteps from '../../store/checkoutSteps';

export default class DataLayer extends React.Component {
    pageTypeMap = {};

    constructor(props, context) {
        super(props, context);

        this.pageTypeMap[checkoutSteps.steps.indexOf(checkoutSteps.STATE_BASKET)] = 'checkout/cart/index';
        this.pageTypeMap[checkoutSteps.steps.indexOf(checkoutSteps.STATE_EMAIL)] = 'checkout/onepage/index/email';
        this.pageTypeMap[checkoutSteps.steps.indexOf(checkoutSteps.STATE_DELIVERY)] = 'checkout/onepage/index/delivery';
        this.pageTypeMap[checkoutSteps.steps.indexOf(checkoutSteps.STATE_PAYMENT)] = 'checkout/onepage/index/payment';
        this.pageTypeMap[checkoutSteps.steps.indexOf(checkoutSteps.STATE_SUCCESS)] = 'checkout/onepage/success';

        this.updateDataLayer(props.checkoutStep);
    }

    updateDataLayer(checkoutStep) {
        if (typeof window.dataLayer !== 'undefined') {
            window.dataLayer.push({ pageType: this.pageTypeMap[checkoutStep] })
        }
    }

    componentWillReceiveProps(nextProps) {
        if (nextProps.checkoutStep === this.props.checkoutStep) return;
        this.updateDataLayer(nextProps.checkoutStep);
    }

    render() {
        return null;
    }
}
