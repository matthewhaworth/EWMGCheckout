import React, {Component} from 'react';
import {connect} from "react-redux";
import {bindActionCreators} from "redux";
import * as basketActions from "../actions/basketActions";
import * as errorActions from "../actions/errorActions";
import * as customerActions from "../actions/customerActions";

import Payment from '../components/payment/Payment';

class PaymentContainer extends Component {

    scrollToView() {
        this.payment.getWrappedInstance().scrollToView();
    }

    render() {
        return <Payment active={this.props.active}
                        customer={this.props.customer}
                        basket={this.props.basket}
                        addGlobalError={this.props.errorActions.addGlobalError}
                        saveAddress={this.props.basketActions.saveAddress}
                        saveCustomerAddress={this.props.customerActions.saveCustomerAddress}
                        addDiscountCode={this.props.basketActions.addDiscountCode}
                        removeDiscountCode={this.props.basketActions.removeDiscountCode}
                        placeOrder={this.props.basketActions.placeOrder}
                        scrollToView={this.props.scrollToView}
                        ref={(payment) => {this.payment = payment;}}/>;
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
        basketActions: bindActionCreators(basketActions, dispatch),
        errorActions: bindActionCreators(errorActions, dispatch)

    }
};

export default connect(mapStateToProps, mapDispatchToProps, null, { withRef: true })(PaymentContainer);