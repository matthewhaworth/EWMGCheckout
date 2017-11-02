import {config} from "../config";
import {addFormKey, handleServerResponse} from "./Api";
import URI from 'urijs';

export default class CustomerApi
{
    static checkCustomerExists(email) {
        let url = new URI(config.apiEndpoint + '/customer');
        url = addFormKey(url);

        if (typeof email !== 'undefined') {
            url.addQuery({email});
        }

        return fetch(url, {
            credentials: 'include',
            method: 'get',
        }).then(response => handleServerResponse(response));
    }

    static authenticate(customer) {
        let url = new URI(config.apiEndpoint + '/customer/session/');
        url = addFormKey(url);

        return fetch(url, {
            credentials: 'include',
            method: 'post',
            body: JSON.stringify({ 'email' : customer.email, 'password': customer.password })
        }).then(response => handleServerResponse(response));
    }

    static saveCustomerAddress(address) {
        let url = new URI(config.apiEndpoint + '/customer/address/');
        url = addFormKey(url);

        return fetch(url, {
            credentials: 'include',
            method: 'put',
            body: JSON.stringify(address)
        }).then(response => handleServerResponse(response));
    }
}