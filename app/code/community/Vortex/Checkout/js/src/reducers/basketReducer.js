import * as actionTypes from "../actions/actionTypes";
import initialState from "./initialState";
import {UPDATE_ADDRESS_SUCCESS} from "../actions/actionTypes";
import {ADDRESS_TYPE_BILLING, ADDRESS_TYPE_SHIPPING} from "../api/BagApi";


export default function basketReducer(state = initialState.basket, action) {
    switch(action.type) {
        case actionTypes.LOAD_BASKET_SUCCESS:
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

        default:
            return state;
    }
};