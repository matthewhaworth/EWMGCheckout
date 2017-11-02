import React, {Component} from 'react';
import ShippingAddress from "./ShippingAddress";
import ClickAndCollect from "./clickcollect/ClickAndCollect";
import ShippingMethod from "../shippingmethod/ShippingMethod";
import AmazonButton from "../payment/amazon/AmazonButton";
import PaypalButton from "../payment/paypal/PaypalButton";
import * as addressValidator from "../../validators/address";
import OrderSubmission from "../payment/OrderSubmission";

const DELIVERY_TYPE_DELIVERY = 'DELIVERY_TYPE_DELIVERY';
const DELIVERY_TYPE_CLICKCOLLECT = 'DELIVERY_TYPE_CLICKCOLLECT';

class Shipping extends Component {

    constructor(props, context) {
        super(props, context);

        const initialDeliveryType = props.basket.is_click_and_collect ? DELIVERY_TYPE_CLICKCOLLECT : DELIVERY_TYPE_DELIVERY;
        this.state = { deliveryType: initialDeliveryType };
    }

    toggleDelivery(event) {
        if (event.target.value === DELIVERY_TYPE_DELIVERY && this.props.basket.is_click_and_collect) {
            this.props.clearClickAndCollectStore().then(() => {
                this.setState({ deliveryType: DELIVERY_TYPE_DELIVERY });
            })
        } else {
            this.setState({ deliveryType: event.target.value });
        }
    }

    onOrderSubmit(history, newsletter, thirdParty) {
        return this.props.placeOrder(newsletter, thirdParty).then((resp) => {
            history.push(`/success/${resp.increment_id}`);
        });
    }

    render() {
        const {customer, basket, saveAddress, active, onContinue, saveCustomerAddress} = this.props;

        let child = null;
        if (this.state.deliveryType === DELIVERY_TYPE_DELIVERY) {
            child = <div>
                <ShippingAddress shippingAddress={basket.shipping_address}
                                 customer={customer}
                                 saveAddress={saveAddress}
                                 saveCustomerAddress={saveCustomerAddress} />

                <ShippingMethod basket={basket}
                                active={this.props.active}
                                setShippingMethod={this.props.setShippingMethod}
                                onContinue={onContinue} />
            </div>
        } else {
            child = <ClickAndCollect isClickAndCollectChosen={basket.is_click_and_collect}
                                     currentClickAndCollectStore={basket.click_and_collect_store}
                                     listClickAndCollectStores={this.props.listClickAndCollectStores}
                                     setClickAndCollectStore={this.props.setClickAndCollectStore}
                                     clearClickAndCollectStore={this.props.clearClickAndCollectStore}/>;
        }

        const isZeroOrder = parseFloat(basket.total) === parseFloat(0);

        const canContinue =
            (this.state.deliveryType === DELIVERY_TYPE_DELIVERY &&
                basket.shipping_method !== null &&
                basket.shipping_method !== '' &&
                basket.shipping_address !== null &&
                addressValidator.validate(basket.shipping_address)._all) ||

            (this.state.deliveryType === DELIVERY_TYPE_CLICKCOLLECT && basket.is_click_and_collect &&
                basket.click_and_collect !== null);

        return (
            <div className="checkout-section checkout-section--checkout">
                <div className="checkout-section__container">
                    <div className="form__control form__control--nomargin">
                        <div className="form__radio form__radio--inline">
                            <label className="form__label">
                                <input type="radio" name="delivery_type"
                                       onChange={(e) => this.toggleDelivery(e)}
                                       checked={this.state.deliveryType === DELIVERY_TYPE_DELIVERY}
                                       value={DELIVERY_TYPE_DELIVERY} />
                                <span className="form__label-text">Delivery</span>
                            </label>
                        </div>
                        <div className="form__radio form__radio--inline">
                            <label className="form__label">
                                <input type="radio" name="delivery_type"
                                       onChange={(e) => this.toggleDelivery(e)}
                                       checked={this.state.deliveryType === DELIVERY_TYPE_CLICKCOLLECT}
                                       value={DELIVERY_TYPE_CLICKCOLLECT} />
                                <span className="form__label-text">Click &amp; Collect</span>
                            </label>
                        </div>
                    </div>

                    {child}

                    <div className={'checkout-section__fader ' + (active ? '' : 'active')} />
                </div>

                {!isZeroOrder && <div className="form__control form__control--actions">
                    <button type='button'
                            onClick={(e) => onContinue(e)}
                            disabled={!canContinue}
                            className="button button--primary button--pay-by-card">
                        <div className="button button--arrow-right"><span>Pay by card</span></div>
                        <div className="payments">
                            <div className="payments__container">
                                <div className="button--payment-mastercard">
                                    <em className="path1" /><em className="path2"/><em className="path3"/><em className="path4"/>
                                </div>
                                <div className="button--payment-visa"></div>
                                <div className="button--payment-amex"></div>
                            </div>
                        </div>
                    </button>
                </div>}
                {!isZeroOrder && this.state.deliveryType === DELIVERY_TYPE_DELIVERY && <div className="checkout-basket__total-express">
                    <div className="checkout-basket__total-express-buttons">
                        <PaypalButton />
                        <AmazonButton />
                    </div>
                </div>}

                {isZeroOrder && <OrderSubmission basket={basket}
                                                 canPlaceOrder={canContinue}
                                                 addDiscountCode={code => this.props.addDiscountCode(code)}
                                                 removeDiscountCode={() => this.props.removeDiscountCode()}
                                                 onOrderSubmit={(history, newsletter, thirdParty) => this.onOrderSubmit(history, newsletter, thirdParty)} />
                }
            </div>
        );
    }
}

export default Shipping;