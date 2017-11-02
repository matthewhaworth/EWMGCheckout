import React, {Component} from 'react';
import {connect} from "react-redux";
import * as basketActions from "../actions/basketActions";
import * as checkoutActions from "../actions/checkoutActions";
import * as customerActions from "../actions/customerActions";
import {bindActionCreators} from "redux";
import Checkout from "../components/checkout/Checkout";

class CheckoutContainer extends Component {
    render() {
        return <Checkout basket={this.props.basket}
                         checkout={this.props.checkout}
                         customer={this.props.customer}
                         fetchBasket={this.props.basketActions.fetchBasket}
                         getLoggedInCustomer={this.props.customerActions.getLoggedInCustomer}
                         changeCheckoutSection={this.props.checkoutActions.changeCheckoutSection}/>
    }
}

const mapStateToProps = (state) => {
    return {
        basket: state.basket,
        customer: state.customer,
        checkout: state.checkout
    }
};

const mapDispatchToProps = (dispatch) => {
    return {
        checkoutActions: bindActionCreators(checkoutActions, dispatch),
        customerActions: bindActionCreators(customerActions, dispatch),
        basketActions: bindActionCreators(basketActions, dispatch)
    }
};

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(CheckoutContainer);