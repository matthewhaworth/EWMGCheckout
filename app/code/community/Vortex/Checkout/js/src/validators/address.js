import isEmpty from 'validator/lib/isEmpty';
import isPostalCode from 'validator/lib/isPostalCode';
import { isValidNumber } from 'libphonenumber-js'


export const emptyAddress = {
    first_name: '',
    last_name: '',
    phone: '',
    line1: '',
    line2: '',
    city: '',
    county: '',
    region_id: '',
    postcode: '',
    country: ''
};

export const validators = {
    first_name: [
        { message: 'First name is required', predicate: (value) => typeof value === 'string' && !isEmpty(value) }
    ],
    last_name: [
        { message: 'Last name is required', predicate: (value) => typeof value === 'string' && !isEmpty(value) }
    ],
    phone: [
        { message: 'Phone is required', predicate: (value) =>  typeof value === 'string' && !isEmpty(value) },
        { message: 'Phone number is invalid', predicate: (value) => typeof value === 'string' && value.length > 3 }
    ],
    line1: [
        { message: 'Line 1 is required', predicate: (value) => typeof value === 'string' && !isEmpty(value) }
    ],
    line2: [],
    city: [
        { message: 'City is required', predicate: (value) => typeof value === 'string' && !isEmpty(value) }
    ],
    county: [
        {
            message: 'County is required',
            predicate: (value, address) => {
                if (address.region_id !== null && address.region_id !== '') return true;
                return typeof value === 'string' && !isEmpty(value)
            }
        }
    ],
    region_id: [
        {
            message: 'State is required',
            predicate: (value, address) => {
                if (address.county !== null && address.county !== '') return true;
                return typeof value === 'string' && !isEmpty(value)
            }
        }
    ],
    postcode: [
        { message: 'Postcode is required', predicate: (value) => typeof value === 'string' && !isEmpty(value) },
        {
            message: 'Postcode wrong format',
            predicate: (value, address) => {
                const isString = typeof value === 'string';
                if (!isString) return false;
                try {
                    return isPostalCode(value, (address.country !== null && address.country !== '') ? address.country : 'any');
                } catch (e) {
                    return isPostalCode(value, 'any');
                }
            }
        },
    ],
    country: [
        { message: 'Country is required', predicate: (value) => typeof value === 'string' && !isEmpty(value) }
    ]
};

export function validate(address) {
    let errors = {};
    Object.keys(validators).forEach((fieldKey) =>
        errors[fieldKey] = validators[fieldKey]
            .filter(rule => !rule.predicate(address[fieldKey], address))
            .map((rule) => rule.message)
    );

    errors._all = Object.keys(errors).every((fieldKey) => errors[fieldKey].length === 0);

    return errors;
}