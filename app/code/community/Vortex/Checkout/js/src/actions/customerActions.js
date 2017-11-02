import * as actionTypes from "./actionTypes";
import CustomerApi from "../api/CustomerApi";
import {fetchBasket} from "./basketActions";
import {UPDATE_CUSTOMER} from "./actionTypes";
import {addGlobalError} from "./errorActions";

export function updateCustomer(customer) {
    return {
        type: UPDATE_CUSTOMER,
        customer
    }
}

export function customerFetchSuccess(customer, guestEmail) {
    let customerAction = {
        type: actionTypes.CUSTOMER_LOGIN_SUCCESS,
        customer: {...customer, existingCustomer: customer.hasOwnProperty('email')}
    };

    if (guestEmail) {
        customerAction.customer.email = guestEmail;
    }

    return customerAction;
}

export function customerDoesExist() {
    return {
        type: actionTypes.CUSTOMER_LOGIN_SUCCESS,
        customer: {existingCustomer: true}
    }
}

export function customerNotFound() {
    return {
        type: actionTypes.CUSTOMER_LOGIN_SUCCESS, // this doesn't make sense... but it uses teh same function to update it..
        customer: {existingCustomer: false}
    };
}

export const authenticate = (email, password) => {
    return (dispatch) => {
        return CustomerApi.authenticate({ 'email': email, 'password': password }).then(
            customer => {
                // Basket may have merged with logged in user's existing basket
                fetchBasket();

                dispatch(customerFetchSuccess(customer, email));
                return customer;
            }
        ).catch((err) => {
            dispatch(addGlobalError(err));
            throw err;
        });
    };
};

export const checkCustomerExists = (email) => {
    return (dispatch) => {
        return CustomerApi.checkCustomerExists(email).then(
            customer => {
                dispatch(customerDoesExist()); // We know it exists as we got a 2xx code back
                return customer;
            }
        ).catch((err) => {
            dispatch(customerNotFound());
            throw err;
        });
    };
};

export const getLoggedInCustomer = () => {
    return (dispatch) => {
        return CustomerApi.checkCustomerExists().then(
            customer => {
                dispatch(customerFetchSuccess(customer));
                return customer;
            }
        )
    };
};

export const saveCustomerAddress = (address) => {
    return (dispatch) => {
        return CustomerApi.saveCustomerAddress(address).then(customer => {
            dispatch(updateCustomer(customer));
        }).catch((err) => {
            dispatch(addGlobalError(err));
            throw err;
        });
    };
};