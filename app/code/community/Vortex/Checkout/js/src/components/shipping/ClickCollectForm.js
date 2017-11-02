import React from 'react';
import SingleInput from "../common/SingleInput";

const ClickCollectForm = () => {

        return <div className="checkout-section checkout-section--checkout">
            <div className="checkout-section__container">
                <div className="checkout-login">
                    <div className="checkout-login__container">
                        <SingleInput inputType={'text'}
                                     title={'First Name'}
                                     name={'first_name'}
                                     controlFunc={() => {}}
                                     content={''} />

                        <SingleInput inputType={'text'}
                                     title={'Last Name'}
                                     name={'last_name'}
                                     controlFunc={() => {}}
                                     content={''} />

                        <SingleInput inputType={'text'}
                                     title={'phone'}
                                     name={'phone'}
                                     controlFunc={() => {}}
                                     content={''} />

                        <SingleInput inputType={'text'}
                                     title={'Post Code'}
                                     name={'postcode'}
                                     controlFunc={() => {}}
                                     content={''} />
                    </div>
                </div>
            </div>
        </div>
};

export default ClickCollectForm;