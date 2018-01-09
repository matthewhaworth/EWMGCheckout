import React from 'react';
import SingleInput from "../common/SingleInput";
import AmazonButton from "../payment/amazon/AmazonButton";
import PaypalButton from "../payment/paypal/PaypalButton";
import GiftCardForm from "../giftcard/GiftCardForm";
import GiftCardList from "../giftcard/GiftCardList";

const ListTotals = ({basket, displayType, isDiscountApplied, discountCode, onDiscountRemove, onDiscountChange, onDiscountApply, onContinue, includeGiftCard, hideDelivery, hideRemoveDiscount, disableRemoveCards}) => {
    const {shipping_with_symbol, subtotal_incl_tax_currency, total_with_symbol, discounts} = basket;


    const discountCodeLabel = (discount) => <span>Discount ({discount.discount_code})</span>;
    const discountList = discounts.map((discount) =>
        <div key={discount.discount_label}>
            <div className="checkout-basket__total checkout-basket__total--extra" id={'discount_code-' + discount.discount_code}>
                <div className="checkout-basket__total-label">{discount.discount_code ? discountCodeLabel(discount) : discount.discount_label}</div>
                <div className="checkout-basket__total-price">-{discount.discount_amount_with_symbol}</div>
            </div>
            {discount.discount_code && !hideRemoveDiscount && <a href="" onClick={(e) => onDiscountRemove(e)}>Remove</a>}
        </div>
    );

    return (
        <div className={'checkout-basket__totals checkout-basket__totals--' + displayType}>
            <div className="checkout-basket__total">
                <div className="checkout-basket__total-label">Subtotal</div>
                <div className="checkout-basket__total-price">{subtotal_incl_tax_currency}</div>
            </div>
            {!hideDelivery && <div className="checkout-basket__total">
                <div className="checkout-basket__total-label">Delivery</div>
                <div className="checkout-basket__total-price">{shipping_with_symbol}</div>
            </div>}

            <div className={'checkout-basket__discount' + (isDiscountApplied ? ' applied' : '')}>
                {discounts && discountList}

                {(!isDiscountApplied) && (<form className="form form--discount">
                    <fieldset>
                        <label>Add discount code?</label>
                        <SingleInput inputType={'text'}
                                     name={'discount_code'}
                                     onChange={(e) => onDiscountChange(e)}
                                     value={discountCode.code || ''}
                                     additionalClassNames="form__control--small col-3-4"
                                     errors={discountCode.errors}
                                     forceValidate={discountCode.forceValidate}
                                     noValidate={!discountCode.forceValidate} />

                        <div className="form__control form__control--actions col-1-4">
                            <button className={'button button--secondary button--small' + (discountCode.saving ? ' button--loading' : '')}
                                    onClick={(e) => onDiscountApply(e)}><span>Add</span>
                            </button>
                        </div>
                    </fieldset>
                </form>)}

                {discountCode.removing && <div className="checkout-loader active"><div className="checkout-loader__spinner"><span /></div></div>}
            </div>

            {includeGiftCard && <div>
                <GiftCardForm />
                {basket.gift_cards && basket.gift_cards.length > 0 && <GiftCardList disableRemoveCards={disableRemoveCards} />}
            </div>}

            <div className="checkout-basket__total checkout-basket__total--grand">
                <div className="checkout-basket__total-label">Total</div>
                <div className="checkout-basket__total-price">{total_with_symbol}</div>
            </div>

            {onContinue && <div className="checkout-basket__total-action">
                <button type='button'
                        onClick={() => onContinue()}
                        className="button button--primary button--arrow-right">
                    <span>Continue to checkout</span>
                </button>
                <div className="checkout-basket__total-action-payments">
                    <button className="button--payment button--payment-paypal">
                        <span>
                            <em className="path1"/>
                            <em className="path2"/>
                            <em className="path3"/>
                            <em className="path4"/>
                            <em className="path5"/>
                            <em className="path6"/>
                            <em className="path7"/>
                            <em className="path8"/>
                            <em className="path9"/>
                            <em className="path10"/>
                        </span>
                    </button>
                    <button className="button--payment button--payment-amazon">
                        <span>
                            <em className="path1"/>
                            <em className="path2"/>
                        </span>
                    </button>
                    <button className="button--payment button--payment-mastercard">
                        <span>
                            <em className="path1"/>
                            <em className="path2"/>
                            <em className="path3"/>
                            <em className="path4"/>
                        </span>
                    </button>
                    <button className="button--payment button--payment-visa"></button>
                    <button className="button--payment button--payment-amex"></button>
                </div>
                {basket.total != 0 &&  <div className="checkout-basket__total-express">
                    <div className="checkout-basket__total-express-title">Express checkout <small>(delivery only)</small></div>
                    <div className="checkout-basket__total-express-buttons">
                        <PaypalButton/>
                        <AmazonButton/>
                    </div>
                </div>}
            </div>}
        </div>
    );
};

export default ListTotals