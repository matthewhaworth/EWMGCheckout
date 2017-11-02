import React, {Component} from 'react';
import {connect} from "react-redux";
import {bindActionCreators} from "redux";
import * as customerActions from "../actions/customerActions";
import * as basketActions from "../actions/basketActions";
import Payment from '../components/payment/Payment';

class PaymentContainer extends Component {
    render() {
        return <Payment active={this.props.active}
                        customer={this.props.customer}
                        basket={this.props.basket}
                        saveAddress={this.props.basketActions.saveAddress}
                        addDiscountCode={this.props.basketActions.addDiscountCode}
                        removeDiscountCode={this.props.basketActions.removeDiscountCode}
                        placeOrder={this.props.basketActions.placeOrder}
                        paymentErrors={this.props.errors.payment} />;
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