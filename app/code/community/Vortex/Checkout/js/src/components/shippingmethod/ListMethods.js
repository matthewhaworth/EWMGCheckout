import React from 'react';
import RawHtml from "../common/RawHtml";

const ListMethods = ({shippingMethods, selectedShippingMethod, setShippingMethod, isLoading, deliveryContent, deliveryActive, toggleDelivery}) => {

    const shippingMethodList = shippingMethods.map((method) => {
        return (
            <div className="form__radio form__radio--list" key={method.code}>
                <label className="form__label">
                    <input type="radio"
                           name="delivery"
                           value={method.code}
                           onChange={() => setShippingMethod(method.code)}
                           checked={method.code === selectedShippingMethod} />
                    <span className="form__label-text"><span dangerouslySetInnerHTML={{__html: method.method_title}}/><br/><span className="strong">{method.price_with_symbol}</span></span>
                </label>
            </div>
        );
    });

    return (
        <form className="form form--primary">
            <div className="form__control form__control--nomargin full">
                {shippingMethodList}
                {isLoading && <div className="checkout-loader active"><div className="checkout-loader__spinner"><span/></div></div>}
            </div>
            <a className="checkout-modal__trigger checkout-modal__trigger--info" href="#" onClick={(e) => toggleDelivery(e)}>More delivery information</a>

            { deliveryContent !== '' && <div className={'checkout-modal ' + (deliveryActive ? 'active' : '')}>
                <div onClick={(e) => toggleDelivery(e)} className="checkout-loader checkout-loader--large checkout-loader--dark checkout-loader--overlay">
                    <div className="checkout-loader__spinner"><span /></div>
                </div>
                <div className="checkout-modal__container">
                    <a className="checkout-modal__close button--icon-close"
                       onClick={(e) => toggleDelivery(e)} />

                    <div className="checkout-delivery">
                        <div className="checkout-delivery__container">
                            <RawHtml className="cms" output={deliveryContent}/>
                        </div>
                    </div>

                </div>
            </div>}

        </form>
    );
};

export default ListMethods;