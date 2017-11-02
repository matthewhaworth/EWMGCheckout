import {ADD_BASKET_ERROR, ADD_GLOBAL_ERROR, ADD_PAYMENT_ERROR, CLEAR_ALL_ERRORS} from "./actionTypes";

export function addGlobalError(error) {
    return {
        type: ADD_GLOBAL_ERROR,
        error
    }
}

export function addBasketError(error) {
    return {
        type: ADD_BASKET_ERROR,
        error
    }
}

export function addPaymentError(error) {
    return {
        type: ADD_PAYMENT_ERROR,
        error
    }
}

export function clearAllErrors() {
    return {
        type: CLEAR_ALL_ERRORS,
    }
}