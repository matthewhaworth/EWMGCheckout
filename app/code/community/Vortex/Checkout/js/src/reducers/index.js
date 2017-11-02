import { combineReducers } from 'redux';

import basket from './basketReducer';
import checkout from './checkoutReducer';
import customer from './customerReducer';
import errors from './errorReducer';
import order from './orderReducer';

export default combineReducers({
    basket,
    checkout,
    customer,
    errors,
    order
});