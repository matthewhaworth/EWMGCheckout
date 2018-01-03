import React from 'react'
import * as checkoutSteps from '../../store/checkoutSteps';

export default class DataLayer extends React.Component {
    pageTypeMap = {};

    constructor(props, context) {
        super(props, context);

        this.pageTypeMap[checkoutSteps.steps.indexOf(checkoutSteps.STATE_BASKET)] = 'checkoutCartIndex';
        this.pageTypeMap[checkoutSteps.steps.indexOf(checkoutSteps.STATE_EMAIL)] = 'checkoutOnepageIndexEmail';
        this.pageTypeMap[checkoutSteps.steps.indexOf(checkoutSteps.STATE_DELIVERY)] = 'checkoutOnepageIndexDelivery';
        this.pageTypeMap[checkoutSteps.steps.indexOf(checkoutSteps.STATE_PAYMENT)] = 'checkoutOnepageIndexPayment';
        this.pageTypeMap[checkoutSteps.steps.indexOf(checkoutSteps.STATE_SUCCESS)] = 'checkoutOnepageSuccess';

        this.updateDataLayer(props.checkoutStep);
    }

    getOrderDataLayer() {
        if (this.props.hasOwnProperty('order') && this.props.order.hasOwnProperty('data_layer')) {
            return this.props.order.data_layer;
        }

        return {};
    }

    updateDataLayer(checkoutStep) {
        if (typeof window.dataLayer !== 'undefined') {
            const orderDataLayer = this.getOrderDataLayer();

            window.dataLayer.push({
                event: this.pageTypeMap[checkoutStep],
                ...orderDataLayer
            });
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
