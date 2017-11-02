import initialState from "./initialState";
import {ORDER_SUCCESS} from "../actions/actionTypes";

export default function orderReducer(state = initialState.order, action) {
    switch(action.type) {
        case ORDER_SUCCESS:
            return action.order;
        default:
            return state;
    }
};