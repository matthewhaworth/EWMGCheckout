import isEmpty from 'validator/lib/isEmpty';
import isEmail from 'validator/lib/isEmail';

export const validators = {
    email: [
        { message: 'Email is required', predicate: (value) => typeof value === 'string' && !isEmpty(value) },
        { message: 'Please enter a valid email address. For example johndoe@domain.com.', predicate: (value) => typeof value === 'string' && isEmail(value) }
    ],
    password: [
        { message: 'Password is required, or continue as guest', predicate: (value) => typeof value === 'string' && !isEmpty(value) }
    ],

    // passwordregister:[
    //     { message: 'Password is required', predicate: (value) => typeof value === 'string' && !isEmpty(value)}
    // ],
    // passwordconfirmation: [
    //     { message: 'Password confirmation is required', predicate: (value) => typeof value === 'string' && !isEmpty(value)}
    // ]
};

export function validate(address) {
    let errors = {};
    Object.keys(validators).forEach((fieldKey) =>
        errors[fieldKey] = validators[fieldKey]
            .filter(rule => !rule.predicate(address[fieldKey]))
            .map((rule) => rule.message)
    );

    errors._all = Object.keys(errors).every((fieldKey) => errors[fieldKey].length === 0);

    return errors;
}