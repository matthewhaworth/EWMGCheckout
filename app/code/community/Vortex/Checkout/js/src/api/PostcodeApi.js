import {config} from '../config';
import URI from 'urijs';

export default class PostcodeApi
{
    static _handlePostcodeResponse(responseJson) {
        if (responseJson.Items.length === 1 && typeof(responseJson.Items[0].Error) !== "undefined") {
            return [ { Label: 'No results found.', Type: 'Address', Error: true } ];
        }

        return responseJson.Items;
    }

    static postcodeFind(postcode, key = false) {
        let url = new URI(config.postcodeAnywhere.findEndpoint);
        url.addQuery({
            Key: config.postcodeAnywhere.key,
            Text: postcode
        });

        if (key) {
            url.addQuery({Container: key});
        }

        return fetch(url,
            { method: 'get' })
            .then(response => response.json())
            .then(this._handlePostcodeResponse);
    }

    static postcodeRetrieve(id) {
        let url = new URI(config.postcodeAnywhere.retrieveEndpoint);
        url.addQuery({
            Key: config.postcodeAnywhere.key,
            Id: id
        });

        return fetch(url,
            { method: 'get' })
            .then(response => response.json())
            .then(this._handlePostcodeResponse);
    }

    static _handleGeocodeResponse(responseJson) {
        if (responseJson.hasOwnProperty('Error')) {
            return Promise.reject();
        }

        return responseJson;
    }

    static geocode(postcode) {
        let url = new URI(config.postcodeAnywhere.geocodeEndpoint);
        url.addQuery({
            Key: config.postcodeAnywhere.key,
            Country: config.postcodeAnywhere.geocodeCountry,
            Location: postcode
        });

        return fetch(url,
            { method: 'get' })
            .then(response => response.json())
            .then(this._handleGeocodeResponse);
    }
}
