import BagApi from "../api/BagApi";
import * as actionTypes from "./actionTypes";
import ClickAndCollectApi from "../api/ClickAndCollectApi";
import {addBasketError, addGlobalError, addPaymentError} from "./errorActions";
import {setOrderSuccess} from "./orderActions";

export function loadBasketSuccess(basket) {
    return { type: actionTypes.LOAD_BASKET_SUCCESS, basket };
}

function handleBasketResponse(basket, dispatch) {
    dispatch(loadBasketSuccess(basket));
    return basket;
}

export const fetchBasket = () => {
    return (dispatch) => BagApi.fetchBagItems()
        .then((basket) => handleBasketResponse(basket, dispatch))
        .catch((err) => {dispatch(addGlobalError('Could not update basket. Have you lost connection?'))});
};

export const changeQuantity = (item, qty) => {
    return (dispatch) => BagApi.changeQuantity(item, qty)
        .then((basket) => handleBasketResponse(basket, dispatch))
        .catch((err) => dispatch(addBasketError(err)));
};

export const addDiscountCode = (discountCode) => {
    return (dispatch) => {
        return BagApi.addDiscountCode(discountCode)
            .then((basket) => {
                if (!basket.discount_code || basket.discount_code === '') {
                    throw 'Could not apply discount code.';
                }
                return handleBasketResponse(basket, dispatch);
            })
            .catch((err) => {
                dispatch(addBasketError(err));
                throw err;
            })
    }
};

export const removeDiscountCode = () => {
    return (dispatch) => {
        return BagApi.removeDiscountCode()
            .then((basket) => {
                if (basket.discount_code && basket.discount_code !== '') {
                    throw 'Could not remove discount code.';
                }
                return handleBasketResponse(basket, dispatch);
            })
            .catch((err) => {
                dispatch(addBasketError(err));
                throw err;
            })
    }
};

export const setShippingMethod = (shippingMethod) => {
    return (dispatch) => BagApi.setShippingMethod(shippingMethod)
        .then((basket) => handleBasketResponse(basket, dispatch))
        .catch((err) => {
            dispatch(addBasketError(err));
            throw err;
        });
};

export const clearDeliveryMethods = () => {
    return { type: actionTypes.REMOVE_DELIVERY_METHODS };
};

const updateAddress = (address, addressType) => {
    return { type: actionTypes.UPDATE_ADDRESS_SUCCESS, address, addressType };
};

export const saveAddress = (customer, address, addressType, force = false, removeDeliveryMethods = false) => {
    return (dispatch, getState) => {
        const useShippingForBilling = getState().basket.use_shipping_for_billing;
        if (force) {
            return BagApi.saveAddress(customer, address, useShippingForBilling, addressType)
                .then(basket => dispatch(loadBasketSuccess(basket)))
                .catch((err) => {
                    dispatch(addBasketError(err));
                    throw err;
                });
        } else {
            dispatch(updateAddress(address, addressType));
            if (removeDeliveryMethods) {
                dispatch(clearDeliveryMethods());
            }
            return Promise.resolve();
        }
    }
};

export const applyGiftCard = (code, pin) => {
    return (dispatch) => {
        return BagApi.applyGiftCard(code, pin)
            .then(resp => dispatch(loadBasketSuccess(resp)))
            .catch((err) => {
                dispatch(addBasketError(err));
                throw err;
            });
    }
};

export const removeGiftCard = (code) => {
    return (dispatch) => {
        return BagApi.removeGiftCard(code)
            .then(resp => dispatch(loadBasketSuccess(resp)))
            .catch((err) => {
                dispatch(addBasketError(err));
                throw err;
            });
    }
};

export const placeOrder = (newsletter = false, thirdParty = false, encryptedCardData = null, cardType = null, cardTokenId = null, saveCardDetails = false) => {
    return (dispatch) => {
        return BagApi.placeOrder(newsletter, thirdParty, encryptedCardData, cardType, cardTokenId, saveCardDetails).then((resp) => {
            dispatch(setOrderSuccess(resp.order));
            return resp;
        }).catch((err) => {
            dispatch(addPaymentError(err));
            throw err;
        });
    }
};


export const listClickAndCollectStores = (lat, lon) => {
    return (dispatch) => ClickAndCollectApi.listStores(lat, lon)
        .catch(err => {
            dispatch(addGlobalError(err));
            throw err;
        })
};

export const setClickAndCollectStore = (storeId) => {
    return (dispatch) => ClickAndCollectApi.setStore(storeId)
        .then(basket => handleBasketResponse(basket, dispatch))
        .catch(err => {
            dispatch(addGlobalError(err));
            throw err;
        });
};

export const clearClickAndCollectStore = () => {
    return (dispatch) => ClickAndCollectApi.clearStore()
        .then(basket => handleBasketResponse(basket, dispatch))
        .catch(err => {
            dispatch(addGlobalError(err));
            throw err;
        });
};