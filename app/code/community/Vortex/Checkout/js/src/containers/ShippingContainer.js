import React, {Component} from 'react';
import {connect} from "react-redux";
import {bindActionCreators} from "redux";
import * as customerActions from "../actions/customerActions";
import * as basketActions from "../actions/basketActions";
import Shipping from "../components/shipping/Shipping";

class ShippingContainer extends Component {
    render() {
        return <Shipping active={this.props.active}
                         basket={this.props.basket}
                         customer={this.props.customer}
                         addDiscountCode={this.props.basketActions.addDiscountCode}
                         removeDiscountCode={this.props.basketActions.removeDiscountCode}
                         saveAddress={this.props.basketActions.saveAddress}
                         saveCustomerAddress={this.props.customerActions.saveCustomerAddress}
                         setShippingMethod={this.props.basketActions.setShippingMethod}
                         listClickAndCollectStores={this.props.basketActions.listClickAndCollectStores}
                         setClickAndCollectStore={this.props.basketActions.setClickAndCollectStore}
                         clearClickAndCollectStore={this.props.basketActions.clearClickAndCollectStore}
                         placeOrder={this.props.basketActions.placeOrder}
                         onContinue={this.props.onContinue}/>
    }
}

const mapStateToProps = (store) => {
    return {
        customer: store.customer,
        basket: store.basket
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
)(ShippingContainer);