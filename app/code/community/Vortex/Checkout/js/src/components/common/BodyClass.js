import React from 'react'
import * as checkoutSteps from '../../store/checkoutSteps';

export default class BodyClass extends React.Component {
    stepClassMap = {};

    constructor(props, context) {
        super(props, context);

        this.stepClassMap[checkoutSteps.steps.indexOf(checkoutSteps.STATE_BASKET)] = 'checkout--step-basket';
        this.stepClassMap[checkoutSteps.steps.indexOf(checkoutSteps.STATE_EMAIL)] = 'checkout--step-delivery';
        this.stepClassMap[checkoutSteps.steps.indexOf(checkoutSteps.STATE_PASSWORD)] = 'checkout--step-delivery';
        this.stepClassMap[checkoutSteps.steps.indexOf(checkoutSteps.STATE_DELIVERY)] = 'checkout--step-delivery';
        this.stepClassMap[checkoutSteps.steps.indexOf(checkoutSteps.STATE_PAYMENT)] = 'checkout--step-delivery';
        this.stepClassMap[checkoutSteps.steps.indexOf(checkoutSteps.STATE_SUCCESS)] = 'checkout--success';

        this.resetBodyClasses();
        this.setBodyClassBasedOnCheckoutStep(props.checkoutStep)
    }

    resetBodyClasses() {
        let classList = document.body.classList;

        // Remove all other state keys
        Object.keys(this.stepClassMap).map((key) => {
            if (this.stepClassMap[key] === '') return;
            classList.remove(this.stepClassMap[key]);
        });
    }

    setBodyClassBasedOnCheckoutStep(nextCheckoutStep) {
        if (this.stepClassMap[nextCheckoutStep] === '') return;
        document.body.classList.add(this.stepClassMap[nextCheckoutStep])
    }

    componentWillReceiveProps(nextProps) {
        this.resetBodyClasses();
        this.setBodyClassBasedOnCheckoutStep(nextProps.checkoutStep)
    }

    componentDidMount() {
        this.resetBodyClasses();
        this.setBodyClassBasedOnCheckoutStep(this.props.checkoutStep);
    }

    render() {
        return null;
    }
}
