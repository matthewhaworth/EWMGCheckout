import {config} from "../config";
import {addFormKey, handleServerResponse} from "./Api";
import * as cardMap from "../helpers/cardMap";
import URI from 'urijs';

export const ADDRESS_TYPE_BILLING = 'billing';
export const ADDRESS_TYPE_SHIPPING = 'shipping';

export default class BagApi
{
    static fetchBagItems() {
        let url = new URI(config.apiEndpoint + '/basket');
        url = addFormKey(url);

        return fetch(url, {
            credentials: 'include'
        })
        .then(response => handleServerResponse(response));
    }

    static changeQuantity(item, newQty) {
        let url = new URI(config.apiEndpoint + '/basket');
        url = addFormKey(url);

        return fetch(url, {
            credentials: 'include',
            method: 'put',
            body: JSON.stringify({ items: [ { 'item_id': item.item_id, 'qty': newQty } ]})
        })
        .then(response => handleServerResponse(response));
    }

    static saveAddress(customer, address, useShippingForBilling, addressType) {
        let url = new URI(config.apiEndpoint + '/basket/address');
        url = addFormKey(url);

        let addressRequest = {
            ...address,
            email: customer.email,
            use_shipping_for_billing: useShippingForBilling,
            type: addressType
        };

        return fetch(url, {
            credentials: 'include',
            method: 'put',
            body: JSON.stringify(addressRequest)
        }).then(response => handleServerResponse(response));
    }

    static addDiscountCode(discountCode) {
        let url = new URI(config.apiEndpoint + '/basket');
        url = addFormKey(url);

        return fetch(url, {
            credentials: 'include',
            method: 'put',
            body: JSON.stringify({'discount_code': discountCode})
        })
        .then(response => handleServerResponse(response));
    }

    static removeDiscountCode() {
        let url = new URI(config.apiEndpoint + '/basket');
        url = addFormKey(url);

        return fetch(url, {
            credentials: 'include',
            method: 'put',
            body: JSON.stringify({'remove_discount':true})
        })
        .then(response => handleServerResponse(response));
    }

    static setShippingMethod(shippingMethod) {
        let url = new URI(config.apiEndpoint + '/basket/shippingmethod');
        url = addFormKey(url);

        return fetch(url, {
            credentials: 'include',
            method: 'put',
            body: JSON.stringify({'shipping_method': shippingMethod})
        })
        .then(response => handleServerResponse(response));
    }

    static applyGiftCard(number, pin) {
        let url = new URI(config.apiEndpoint + '/basket/giftcard');
        url = addFormKey(url);

        return fetch(url, {
            credentials: 'include',
            method: 'POST',
            body: JSON.stringify({ giftcard_code: number, giftcard_pin: pin })
        }).then(response => handleServerResponse(response));
    }

    static removeGiftCard(number) {
        let url = new URI(config.apiEndpoint + '/basket/giftcard');
        url = addFormKey(url);

        return fetch(url, {
            credentials: 'include',
            method: 'DELETE',
            body: JSON.stringify({ giftcard_code: number })
        }).then(response => handleServerResponse(response));
    }

    static placeOrder(newsletter = false, thirdParty = false, encryptedCardData = null, cardType = null, cardTokenId = null, saveCardDetails = false) {
        let requestBody = { newsletter, thirdParty };

        // Recurring payment type
        if (encryptedCardData === null && cardTokenId !== null) {
            requestBody = {
                ...requestBody,
                method: 'worldpay_recur',
                recurringOrderId: cardTokenId
            };
        }

        // Pay with card
        if (encryptedCardData !== null && cardTokenId === null) {
            requestBody = {
                ...requestBody,
                method: 'worldpay_cc',
                type_worldpay_cc: cardMap.cardMap[cardType],
                encryptedData: encryptedCardData,
                saveCardDetails: saveCardDetails
            };
        }

        // No payment required
        if (encryptedCardData === null && cardTokenId === null) {
            requestBody = {
                method: 'free'
            }
        }

        let url = new URI(config.apiEndpoint + '/order');
        url = addFormKey(url);

        return fetch(url, {
            credentials: 'include',
            method: 'POST',
            body: JSON.stringify(requestBody)
        })
        .then(response => handleServerResponse(response));
    }
}