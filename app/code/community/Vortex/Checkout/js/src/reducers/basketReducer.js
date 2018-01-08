import * as actionTypes from "../actions/actionTypes";
import initialState from "./initialState";
import {LOAD_BASKET_SUCCESS, UPDATE_ADDRESS_SUCCESS, REMOVE_DELIVERY_METHODS} from "../actions/actionTypes";
import {ADDRESS_TYPE_BILLING, ADDRESS_TYPE_SHIPPING} from "../api/BagApi";


export default function basketReducer(state = initialState.basket, action) {
    switch(action.type) {
        case LOAD_BASKET_SUCCESS:
            return {...action.basket, use_shipping_for_billing: state.use_shipping_for_billing };
        case UPDATE_ADDRESS_SUCCESS:
            if (action.addressType === ADDRESS_TYPE_SHIPPING) {
                return {
                    ...state,
                    shipping_address: {
                        ...state.shipping_address,
                        ...action.address
                    },
                    use_shipping_for_billing: state.use_shipping_for_billing
                };
            } else if (action.addressType === ADDRESS_TYPE_BILLING) {
                return {
                    ...state,
                    billing_address: {
                        ...state.billing_address,
                        ...action.address
                    },
                    use_shipping_for_billing: false
                };
            }

            break;

        case REMOVE_DELIVERY_METHODS:
            return {
                ...state,
                available_shipping_methods: []
            };
            break;

        default:
            return state;
    }
};