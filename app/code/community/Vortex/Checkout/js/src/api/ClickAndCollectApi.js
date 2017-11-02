import {config} from "../config";
import {addFormKey, handleServerResponse} from "./Api";
import URI from 'urijs';

export default class ClickAndCollectApi
{
    static listStores(lat, lng) {
        let url = new URI(config.apiEndpoint + '/clickandcollect/stores');
        url = addFormKey(url);

        let urlParams = {lng, lat};
        url.addQuery(urlParams);

        return fetch(url, {
            credentials: 'include',
            method: 'get',
        }).then(response => handleServerResponse(response));
    }

    static setStore(storeId) {
        let url = new URI(config.apiEndpoint + '/clickandcollect/stores');
        url = addFormKey(url);

        return fetch(url, {
            credentials: 'include',
            method: 'put',
            body: JSON.stringify({'store_id': storeId})
        }).then(response => handleServerResponse(response));
    }

    static clearStore() {
        let url = new URI(config.apiEndpoint + '/clickandcollect/clear');
        url = addFormKey(url);

        return fetch(url, {
            credentials: 'include',
            method: 'put'
        }).then(response => handleServerResponse(response));
    }
}