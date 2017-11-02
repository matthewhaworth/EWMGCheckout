import * as checkoutSteps from "../store/checkoutSteps";

export default {
    basket: {
        use_shipping_for_billing: true
    },
    customer: {},
    checkout: {
        step: checkoutSteps.steps.indexOf(checkoutSteps.STATE_BASKET),
        visited: [checkoutSteps.STATE_BASKET]
    },
    errors: {
        global: [],
        basket: [],
        payment: []
    },
    order: {}
};