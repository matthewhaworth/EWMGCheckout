import {ORDER_SUCCESS} from "./actionTypes";
import OrderApi from "../api/OrderApi";

export function setOrderSuccess(order) {
    return {
        type: ORDER_SUCCESS,
        order
    }
}

export const loadOrder = (incrementId) => {
    return (dispatch) => {
        return OrderApi.loadOrder(incrementId).then((resp) => {
            dispatch(setOrderSuccess(resp));
            return resp;
        })
        .catch((err) => {
            throw err;
        });
    }
};