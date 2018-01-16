import isEmpty from 'validator/lib/isEmpty';

export const validators = {
    number: [
        { message: 'Number is required ', predicate: (value) => typeof value === 'string' && !isEmpty(value) },
        { message: 'Number must be at least 10 characters', predicate: (value) => typeof value === 'string' && value.length >= 10 }
    ]
    // pin: [
    //     { message: 'Pin is required', predicate: (value) => typeof value === 'string' && !isEmpty(value) },
    //     { message: 'Pin must be at least 2 characters', predicate: (value) => typeof value === 'string' && value.length >= 2 }
    // ]
};

export function validate(giftcard) {
    let errors = {};
    Object.keys(validators).forEach((fieldKey) =>
        errors[fieldKey] = validators[fieldKey]
            .filter(rule => !rule.predicate(giftcard[fieldKey], giftcard))
            .map((rule) => rule.message)
    );

    errors._all = Object.keys(errors).every((fieldKey) => errors[fieldKey].length === 0);

    return errors;
}