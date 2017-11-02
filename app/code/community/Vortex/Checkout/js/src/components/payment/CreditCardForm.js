import React from 'react';
import SingleInput from "../common/SingleInput";
import Checkbox from "../common/Checkbox";

const CreditCardForm = ({creditCard, errors, canSaveCard, saveCard, toggleSaveCard, onCreditCardChange}) => {
    return (
        <form className="form form--primary form--newcard" method="post">
            <fieldset>
                <div className="form__control-wrapper form__control-wrapper--primary">

                    <SingleInput inputType={'text'}
                                     title="Cardholder Name"
                                     name={'cardHolderName'}
                                     errors={errors.cardHolderName}
                                     onChange={onCreditCardChange}
                                     value={creditCard.cardHolderName || ''}
                                     additionalClassNames="full" />

                    <SingleInput inputType={'text'}
                                 inputMask={/^3[47]/.test(creditCard.cardNumber) ? '9999 999999 99999' : '9999 9999 9999 9999'}
                                 title="Card Number"
                                 name={'cardNumber'}
                                 errors={errors.cardNumber}
                                 onChange={onCreditCardChange}
                                 value={creditCard.cardNumber || ''}
                                 additionalClassNames={'form__control--card full ' + (creditCard.type || '')}
                                 isCard={true}
                                 cardType={creditCard.type}/>

                    <SingleInput inputType={'text'}
                                 inputMask='99/99'
                                 errors={errors.expiry}
                                 title="Expiry"
                                 name={'expiry'}
                                 onChange={onCreditCardChange}
                                 value={creditCard.expiry || ''}
                                 additionalClassNames="col-2-4" />

                    <SingleInput inputType={'text'}
                                 inputMask={/^3[47]/.test(creditCard.cardNumber)? '9999' : '999'}
                                 title="Security Code"
                                 errors={errors.cvc}
                                 name={'cvc'}
                                 onChange={onCreditCardChange}
                                 value={creditCard.cvc || ''}
                                 additionalClassNames="col-2-4 form__control--cvc"
                                 isCvc={true}/>

                    {canSaveCard && <Checkbox
                        label="Save Card"
                        name="save_card"
                        checked={saveCard}
                        onToggle={(e) => toggleSaveCard(e)} />}
                </div>
            </fieldset>
        </form>
    );
};

export default CreditCardForm;