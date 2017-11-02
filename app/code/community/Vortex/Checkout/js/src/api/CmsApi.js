import {config} from "../config";
import {addFormKey, handleServerResponse} from "./Api";
import URI from 'urijs';

export default class CmsApi
{
    static loadCmsContent(layoutHandle) {
        let url = new URI(config.apiEndpoint + '/cms');
        url = addFormKey(url);
        url.addQuery({layout_handle: layoutHandle});

        return fetch(url, {
            credentials: 'include',
            method: 'get',
        }).then(response => handleServerResponse(response));
    }
}