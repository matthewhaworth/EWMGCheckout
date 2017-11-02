import {config} from "../config";
import {addFormKey, handleServerResponse} from "./Api";
import URI from 'urijs';

export default class OrderApi
{
    static loadOrder(incrementId) {
        let url = new URI(config.apiEndpoint + '/order');
        url.addQuery({
            increment_id: incrementId
        });
        url = addFormKey(url);

        return fetch(url, {
            credentials: 'include',
            method: 'GET',
        })
        .then(response => handleServerResponse(response));
    }
}