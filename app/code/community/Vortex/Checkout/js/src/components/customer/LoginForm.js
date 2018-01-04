import React from 'react';
import SingleInput from "../common/SingleInput";

const EmailForm = ({customer, errors, loading, customerExists, onChange, onContinueAsLoggedIn, onContinueAsGuest}) => {
    return (
        <form className="form form--primary">
            <fieldset>

                <p>Please enter your email address and we will get you through the checkout as fast as we can</p>

                <SingleInput inputType={'email'}
                             title={'Email address'}
                             name={'email'}
                             autocomplete="email"
                             onChange={(e) => onChange(e)}
                             disableBlur
                             value={customer.email || ''}
                             errors={errors.email}
                             additionalClassNames="full form__control--nomargin" />

                {customerExists && <SingleInput inputType={'password'}
                             title={'Password'}
                             name={'password'}
                             autocomplete="password"
                             onChange={(e) => onChange(e)}
                             errors={errors.password}
                             value={customer.password || ''}
                             additionalClassNames="full" />}

                {!customerExists && <div className="form__control form__control--actions">
                    <button type='button'
                            disabled={loading || errors.email.length > 0 }
                            onClick={(e) => onContinueAsGuest(e)}
                            className={'button button--primary ' + (loading ? 'button--loading' : 'button--arrow-right')}>
                        <span>Continue</span>
                    </button>
                </div>}

                {customerExists && (<div>
                    <div className="form__control form__control--actions">
                        <button type='button'
                                disabled={loading || !errors._all}
                                onClick={(e) => onContinueAsLoggedIn(e)}
                                className={'button button--primary ' + (loading ? 'button--loading' : 'button--arrow-right')}>
                            <span>Continue</span>
                        </button>
                    </div>
                    <div className="form__control form__control--actions">
                        <button type='button'
                                onClick={(e) => onContinueAsGuest(e)}
                                className="button button--secondary button-blue button--arrow-right">
                            <span>Continue as guest</span>
                        </button>
                    </div>
                </div>)}
            </fieldset>
        </form>
    );
};

export default EmailForm;