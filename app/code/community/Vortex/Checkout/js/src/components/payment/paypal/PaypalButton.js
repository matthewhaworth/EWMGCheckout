import React, {PureComponent} from 'react';
import {config} from "../../../config";
import {url} from "../../../config";

class PaypalButton extends PureComponent {
    constructor(props, context) {
        super(props, context);
    }

    redirectToPayPal() {
        window.location = url('/paypal/express/start/button/1/');
    }

    render() {
        if ( ! config.isPaypalEnabled) {
            return false;
        }

        return (
            <button className="button button--payment button--payment-paypal" onClick={() => this.redirectToPayPal()}>
                <span><em className="path1"></em><em className="path2"></em><em className="path3"></em><em className="path4"></em><em className="path5"></em><em className="path6"></em><em className="path7"></em><em className="path8"></em><em className="path9"></em><em className="path10"></em></span>
            </button>
        );
    }
}

export default PaypalButton;