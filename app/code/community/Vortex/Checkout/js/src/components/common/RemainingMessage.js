import React, {Component} from 'react';
import classNames from 'classnames';
import {config} from "../../config";

class RemainingMessage extends Component{

    render(){
        const {subtotal, additionalClassNames} = this.props;
        const remaining = config.remainingTotal;
        const showAt = config.remainingShowAt;

        if(!remaining || (Number.parseFloat(subtotal) < Number.parseFloat(showAt)))
            return null;

        let qualified = Number.parseFloat(subtotal) >= Number.parseFloat(remaining);
        let diff = Math.abs(Number.parseFloat(subtotal) - Number.parseFloat(remaining));
        let blockClassNames = 'checkout-basket__remaining ';

        blockClassNames += classNames({
            'checkout-basket__remaining--qualified': qualified
        });

        if (additionalClassNames) {
            blockClassNames += ' ' + additionalClassNames;
        }

        return (
            <div className={blockClassNames}>
                {qualified ? (
                    <div className="checkout-basket__remaining-text">
                        Your order qualifies for free UK standard delivery!
                    </div>
                ) : (
                    <div className="checkout-basket__remaining-text">
                        Spend a further &pound;{diff.toFixed(2)} to receive free UK standard delivery!
                    </div>
                )}
            </div>
        );
    }


}

export default RemainingMessage