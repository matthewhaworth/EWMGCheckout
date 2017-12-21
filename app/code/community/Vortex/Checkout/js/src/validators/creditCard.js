import isEmpty from 'validator/lib/isEmpty';
import * as cardValidator from 'card-validator';
import creditCardType from 'credit-card-type';

const isMMYY = (value) => {
    if (!(typeof value === 'string')) return false;
    const splitValue = value.split('/');
    if (splitValue.length !== 2) return false;
    return /^[0-9]{2}$/.test(splitValue[0]) && /^[0-9]{2}$/.test(splitValue[1]);
};

const isCardExpired = (month, year) => {
    const twoDigitCurrentMonth = new Date().getMonth();
    const twoDigitCurrentYear = new Date().getFullYear().toString().substr(-2);

    if (year < twoDigitCurrentYear) return true;
    return year === twoDigitCurrentYear && month <= twoDigitCurrentMonth;

};

export const validators = {
    cardHolderName: [
        { message: 'Cardholder name is required', predicate: value => typeof value === 'string' && !isEmpty(value) }
    ],
    cardNumber: [
        { message: 'Card number is required', predicate: value => typeof value === 'string' && !isEmpty(value) },
        { message: 'Invalid card number', predicate: value => typeof value === 'string' && cardValidator.number(value).isValid }
    ],
    expiry: [
        { message: 'Expiry is required', predicate: value => typeof value === 'string' && !isEmpty(value) },
        { message: 'Expiry must be MM/YY', predicate: value => isMMYY(value)},
        { message: 'Month is invalid', predicate: value => isMMYY(value) && value.split('/')[0] >= 1 && value.split('/')[0] <= 12 },
        { message: 'Card is expired', predicate: value => isMMYY(value) && !isCardExpired(value.split('/')[0], value.split('/')[1])  }
    ],
    cvc: [
        { message: 'CVC is required', predicate: value => typeof value === 'string' && !isEmpty(value)},
        {
            message: 'CVC must be appropriate length for card type',
            predicate: (value, creditCard) => {
                if (!creditCard.cardNumber) {
                    return false;
                }

                const cardValidationData = cardValidator.number(creditCard.cardNumber.split(' ').join(''));
                if (!cardValidationData || !cardValidationData.card) {
                    return false;
                }

                const requiredSize = cardValidationData.card.code.size;
                return typeof value === 'string' && value.split(' ').join('').length === requiredSize;
            }
        }
    ]
};

export function validate(creditCard) {
    let errors = {};
    Object.keys(validators).forEach((fieldKey) =>
        errors[fieldKey] = validators[fieldKey]
            .filter(rule => !rule.predicate(creditCard[fieldKey], creditCard))
            .map((rule) => rule.message)
    );

    errors._all = Object.keys(errors).every((fieldKey) => errors[fieldKey].length === 0);

    return errors;
}