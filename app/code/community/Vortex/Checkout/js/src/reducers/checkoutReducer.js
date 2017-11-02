import * as actionTypes from "../actions/actionTypes";
import * as checkoutSteps from "../store/checkoutSteps";
import initialState from "./initialState";

export default function checkoutReducer(state = initialState.checkout, action) {
    switch(action.type) {
        case actionTypes.CHANGE_CHECKOUT_SECTION:
            let nextSectionId = checkoutSteps.steps.indexOf(action.nextSection);
            if (nextSectionId === -1) {
                // Do nothing if it can't find the state
                return state;
            }

            let visited = [...state.visited, action.nextSection];
            return { step: nextSectionId, visited };

        default:
            return state;
    }
};