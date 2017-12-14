import React from 'react'

export const AddressDisplay = ({addressLabel, address, onAddressClick, loading}) => {

    return(
        <div className="form__control form__control--autocomplete full complete" onClick={() => onAddressClick()}>
            {addressLabel && <label>{addressLabel}</label>}
            <div className="form__autocomplete-result">
                <a className="form__autocomplete-edit">Change</a>
                {address.company && <span>{address.company}<br/></span>}
                {address.line1 && <span>{address.line1}<br/></span>}
                {address.line2 && <span>{address.line2}<br/></span>}
                {address.city && <span>{address.city}<br/></span>}
                {address.postcode && <span>{address.postcode}</span>}
                {address.country && <span>, {address.country}<br/></span>}
            </div>
            {loading && <div className="checkout-loader active"><div className="checkout-loader__spinner"><span /></div></div>}
        </div>
    );
};