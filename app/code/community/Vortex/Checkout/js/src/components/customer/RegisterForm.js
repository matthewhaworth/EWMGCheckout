import React from 'react';
import SingleInput from "../common/SingleInput";

const RegisterForm = ({register, errors, loading, onChange, onContinue}) => {
    return (
        <form className="form form--primary">
            <fieldset>

                <p>Checkout quicker next time by creating an account today. Simply create a password!</p>

                <SingleInput inputType={'password'}
                             title={'Create password'}
                             name={'password'}
                             onChange={(e) => onChange(e)}
                             value={register.password}
                             errors={errors.password}
                             additionalClassNames="full form__control--nomargin form__control--password" />

                <SingleInput inputType={'password'}
                             title={'Confirm password'}
                             name={'password_confirmation'}
                             onChange={(e) => onChange(e)}
                             value={register.password_confirmation}
                             errors={errors.password_confirmation}
                             additionalClassNames="full form__control--password" />

                <div className="form__control form__control--actions">
                    <button type='button'
                            disabled={loading || !errors._all }
                            onClick={(e) => onContinue(e)}
                            className={'button button--secondary ' + (loading ? 'button--loading' : 'button--arrow-right')}>
                        <span>Create my account</span>
                    </button>
                    <p className="note">By creating an account your information will be stored so next time you shop with us you can check in a matter of a few clicks. You can also log in and view order history.</p>
                </div>
            </fieldset>
        </form>
    );
};

export default RegisterForm;