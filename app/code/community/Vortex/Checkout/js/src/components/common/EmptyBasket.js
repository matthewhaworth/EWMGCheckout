import React from 'react'
import {url} from "../../config";
import Header from "../header/Header";
import BodyClass from "../common/BodyClass";
import GlobalErrors from '../common/GlobalErrors';

const EmptyBasket = ({customer, customerExists, step}) => {
    return(
        <div>
            <Header checkoutStep={step}/>
            <GlobalErrors/>
            <div className="checkout-maincontent">
                <div className="checkout-maincontent__wrapper">
                    <div className="checkout-maincontent__container">
                        <div className="checkout-layout checkout-layout--animate">
                            <div className="checkout-layout__column checkout-layout__column--basket">
                                <div className="checkout-section checkout-section--basket">
                                    <div className="checkout-section__title">
                                        <div className="checkout-section__title-text">
                                            <span className="checkout-section__title-count">0</span> items in your bag
                                        </div>
                                    </div>
                                    <div className="checkout-section__container">
                                        <div className="checkout-basket__empty">

                                            {customerExists && <div className="checkout-basket__empty-title">Welcome {customer.name}!</div>}
                                            <div className="checkout-basket__empty-bag">
                                                <div className="checkout-basket__empty-subtitle">Your bag is empty</div>
                                            </div>
                                            <div className="checkout-basket__empty-content">
                                                {customerExists && <p>View your account or continue shopping</p>}
                                                {!customerExists && <p>Sign in to your account or continue shopping</p>}
                                            </div>
                                            <div className="checkout-basket__empty-actions">
                                                <a href={url("")} className="button button--primary button--arrow-right"><span>Continue shopping</span></a>
                                                {!customerExists && <a href={url("customer/account/login")} className="button button--secondary button--arrow-right"><span>Sign in</span></a>}
                                                {customerExists && <a href={url("customer/account/index")} className="button button--secondary button--arrow-right"><span>View my Account</span></a>}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <BodyClass checkoutStep={step} />
            </div>
        </div>
    );
};

export default EmptyBasket