import {config} from "../config";

export const addFormKey = (url) => {
    if (config.formKeyRequired && config.formKey) {
        url.addQuery('form_key', config.formKey);
    }

    return url;
};

/**
 * 1. The server should ideally respond with an 'ok' status (>= 200 and < 300).
 *    If it does, this method forwards on the successful json and the flow can continue.
 *
 * 2. If the server encounters an error it is able to handle, it will return a 4xx or 5xx with json in the format:
 *
 *    { errorCode: ..., errorMessage: ... }
 *
 *    If this is the case, the promise will be rejected with the errorMessage passed down and a catch further down
 *    will deal with it.
 *
 * 3. If the server encounters an error and still gives a 4xx or 5xx, but still a json response that does not
 *    conform to the above format, then we pass:
 *
 *    "An unexpected error occurred."
 *
 *    Down the chain as we would have passed 'errorMessage' above.
 *
 * 4. If the server encounters an error and as a result does not pass json and does passes 2xx (PHP Fatal error can
 *    do this in certain circumstances). Then we also pass
 *
 *    "An unexpected error occurred."
 *
 *    further down the chain.
 *
 * This
 *
 * @param response
 */
export const handleServerResponse = (response) => {
    return response.json().then(json => {
        if (!response.ok) {
            return Promise.reject(json.errorMessage ? json.errorMessage : 'An unexpected error occurred.');
        } else {
            return json;
        }
    }).catch(err => {
        // If it was a real server error (not json) response.json() will have failed, so through a new error..
        if (err instanceof Error) {
            throw 'An unexpected error occurred.';
        }

        // .. or continue to reject the promise.
        return Promise.reject(err);
    })
}