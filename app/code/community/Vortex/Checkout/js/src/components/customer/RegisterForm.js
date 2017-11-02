import React from 'react';
import SingleInput from "../common/SingleInput";
import Checkbox from "../common/Checkbox";

const RegisterForm = ({customer, errors, loading, onChange, onContinue}) => {
    return (
        <form className="form form--primary">
            <fieldset>

                <p>Checkout quicker next time by creating an account today. Simply create a password!</p>

                <SingleInput inputType={'password'}
                             title={'Create password'}
                             name={'password'}
                             onChange={(e) => onChange(e)}
                             value=''
                             errors={errors.passwordregister}
                             additionalClassNames="full form__control--nomargin form__control--password"
                             showPassword={true}/>

                <SingleInput inputType={'password'}
                             title={'Confirm password'}
                             name={'confirm_password'}
                             onChange={(e) => onChange(e)}
                             value=''
                             errors={errors.passwordconfirmation}
                             additionalClassNames="full form__control--password"
                             showPassword={true}/>

                <p>Save these card details for next time?</p>

                <Checkbox label='American Express ending in 1234'
                          name=''
                          checked={true}
                          additionalClassNames="form__control--nomargin"
                          checkboxAdditionalClassNames="form__checkbox--smalltext"/>

                <div className="form__control form__control--actions">
                    <button type='button'
                            disabled={loading || errors.password.length > 0 }
                            onClick={(e) => onContinue(e)}
                            className={'button button--secondary ' + (loading ? 'button--loading' : 'button--arrow-right')}>
                        <span>Create my account</span>
                    </button>
                    <p class="note">By creating an account your information will be stored so next time you shop with us you can check in a matter of a few clicks. You can also log in and view order history.</p>
                </div>
            </fieldset>
        </form>
    );
};

export default RegisterForm;