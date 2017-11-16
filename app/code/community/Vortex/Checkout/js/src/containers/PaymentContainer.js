import React, {Component} from 'react';
import {connect} from "react-redux";
import {bindActionCreators} from "redux";
import * as basketActions from "../actions/basketActions";
import * as customerActions from "../actions/customerActions";

import Payment from '../components/payment/Payment';

class PaymentContainer extends Component {
    render() {
        return <Payment active={this.props.active}
                        customer={this.props.customer}
                        basket={this.props.basket}
                        saveAddress={this.props.basketActions.saveAddress}
                        saveCustomerAddress={this.props.customerActions.saveCustomerAddress}
                        addDiscountCode={this.props.basketActions.addDiscountCode}
                        removeDiscountCode={this.props.basketActions.removeDiscountCode}
                        placeOrder={this.props.basketActions.placeOrder} />;
    }
}

const mapStateToProps = (state) => {
    return {
        customer: state.customer,
        basket: state.basket,
        errors: state.errors
    }
};

const mapDispatchToProps = (dispatch) => {
    return {
        customerActions: bindActionCreators(customerActions, dispatch),
        basketActions: bindActionCreators(basketActions, dispatch)
    }
};

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(PaymentContainer);