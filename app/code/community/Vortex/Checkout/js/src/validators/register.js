import isEmpty from 'validator/lib/isEmpty';

export const validators = {
    password: [
        { message: 'Password is required', predicate: (value) => typeof value === 'string' && !isEmpty(value) },
        { message: 'Password must be at least six characters', predicate: (value) => typeof value === 'string' && value.length >= 6 },
        { message: 'Password must not contain spaces', predicate: (value) => typeof value === 'string' && value.split(' ').join('').length === value.length }
    ],
    password_confirmation: [
        { message: 'Password confirmation is required', predicate: (value, register) => typeof value === 'string' && !isEmpty(value) },
        { message: 'Password confirmation does not match password', predicate: (value, register) => value === register.password }
    ]
};

export function validate(register) {
    let errors = {};
    Object.keys(validators).forEach(fieldKey =>
        errors[fieldKey] = validators[fieldKey]
            .filter(rule => !rule.predicate(register[fieldKey], register))
            .map((rule) => rule.message)
    );

    errors._all = Object.keys(errors).every((fieldKey) => errors[fieldKey].length === 0);

    return errors;
}