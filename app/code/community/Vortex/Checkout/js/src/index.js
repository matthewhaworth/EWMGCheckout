import React from 'react';
import ReactDOM from 'react-dom';
import './index.css';
import './checkout.css';
import CheckoutContainer from './containers/CheckoutContainer';
import { Provider } from 'react-redux'
import configureStore from './store/configureStore';
import {
    HashRouter as Router,
    Route
} from 'react-router-dom'
import SuccessContainer from "./containers/SuccessContainer";
import ScrollToTop from "./components/common/ScrollToTop";

const store = configureStore();

ReactDOM.render(
    <Provider store={store}>
        <Router onUpdate={() => window.scrollTo(0, 0)} >
            <ScrollToTop>
                <div>
                    <Route exact path="/" component={CheckoutContainer}/>
                    <Route path="/success/:orderId" component={SuccessContainer} />
                </div>
            </ScrollToTop>
        </Router>
    </Provider>,
    document.getElementById('root')
);