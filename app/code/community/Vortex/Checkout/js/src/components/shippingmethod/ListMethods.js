import React from 'react';
import DeliveryModal from '../../components/common/DeliveryModal';
import RemainingMessage from "../common/RemainingMessage";

const ListMethods = ({shippingMethods, selectedShippingMethod, setShippingMethod, isLoading, basketSubtotal}) => {

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
            <RemainingMessage subtotal={basketSubtotal}
                              additionalClassNames={"checkout-basket__remaining--primary"}/>
            <div className="form__control form__control--nomargin full">
                {shippingMethodList}
                {isLoading && <div className="checkout-loader active"><div className="checkout-loader__spinner"><span/></div></div>}
            </div>
            <DeliveryModal/>
        </form>
    );
};

export default ListMethods;