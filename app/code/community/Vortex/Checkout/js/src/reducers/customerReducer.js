import initialState from "./initialState";
import {CUSTOMER_LOGIN_SUCCESS, UPDATE_CUSTOMER} from "../actions/actionTypes";

export default function customerReducer(state = initialState.customer, action) {
    switch(action.type) {
        case CUSTOMER_LOGIN_SUCCESS:
        case UPDATE_CUSTOMER:
            return {
                ...state,
                ...action.customer
            };
        default:
            return state;
    }
};