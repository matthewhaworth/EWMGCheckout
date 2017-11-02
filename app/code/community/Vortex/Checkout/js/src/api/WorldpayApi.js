import {config} from '../config';

export default class WorldpayApi
{
    static createCardToken(name, expiryMonth, expiryYear, startMonth, startYear, cardNumber, type, cvc, issueNumber, reusable = false) {
        const jsonRequest = {
            "reusable": true/false,
            "paymentMethod": {
                "name": name,
                "expiryMonth": 2,
                "expiryYear": 2015,
                "issueNumber": 1,
                "startMonth": 2,
                "startYear": 2013,
                "cardNumber": "4444 3333 2222 1111",
                "type": "Card",
                "cvc": "123"
            },
        };

        let pcaUrl = config.postcodeAnywhere.retrieveEndpoint;
        return fetch(pcaUrl + '?' + query, { method: 'get' })
            .then(response => response.json())
            .then(this._handleItemsResponse);
    }
}
