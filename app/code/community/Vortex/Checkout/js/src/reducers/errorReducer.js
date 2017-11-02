import * as actionTypes from "../actions/actionTypes";
import initialState from "./initialState";

export default function errorReducer(state = initialState.errors, action) {
    switch(action.type) {
        case actionTypes.ADD_GLOBAL_ERROR:
            return {
                ...state,
                global: [
                    ...state.global,
                    action.error
                ]
            };
        case actionTypes.ADD_PAYMENT_ERROR:
            return {
                ...state,
                payment: [
                    ...state.payment,
                    action.error
                ]
            };
        case actionTypes.ADD_BASKET_ERROR:
            return {
                ...state,
                basket: [
                    ...state.basket,
                    action.error
                ]
            };
        case actionTypes.CLEAR_ALL_ERRORS:
            return {
                global: [],
                basket: [],
                payment: []
            };
        default:
            return state;
    }
};