import React from 'react'

const Error = ({message}) => {
    return (
        <div className="checkout-basket__message checkout-basket__message--error">
            <div className="checkout-basket__message-container">
                <p>{message}</p>
            </div>
        </div>
    );
};

export default Error;