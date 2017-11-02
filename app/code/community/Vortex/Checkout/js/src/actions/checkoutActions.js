import * as actionTypes from './actionTypes';

export const changeCheckoutSection = (nextSection) => {
    return { type: actionTypes.CHANGE_CHECKOUT_SECTION, nextSection };
};