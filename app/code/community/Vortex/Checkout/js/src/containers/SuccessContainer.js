import React, {Component} from 'react';
import ListItems from "../components/basket/ListItems";
import Totals from "../components/basket/Totals";
import {connect} from "react-redux";
import * as registerValidator from "../validators/register";
import {STATE_SUCCESS} from "../store/checkoutSteps";
import Header from "../components/header/Header";
import BodyClass from "../components/common/BodyClass";
import {url} from "../config";
import * as customerActions from "../actions/customerActions";
import * as orderActions from "../actions/orderActions";
import {bindActionCreators} from "redux";
import DataLayer from "../components/common/DataLayer";
import RegisterForm from "../components/customer/RegisterForm";

class SuccessContainer extends Component {
    constructor(props, context) {
        super(props, context);

        this.state = {
            itemsExpanded: true,
            accountExpanded: true,
            loading: false,
            registerErrors: {},
            registerForm: {
                password: '',
                password_confirmation: ''
            },
            showSignUpSuccess: false
        };
    }

    /**
     * Try and load the order if it is not already in state
     */
    componentWillMount() {
        window.scrollTo(0, 0);

        if (this.props.order && this.props.order.order_id) {
            return;
        }

        const incrementId = this.props.match.params.orderId;
        if (!incrementId) {
            window.location = this.redirectHome();
        }

        this.props.orderActions.loadOrder(incrementId).then(order => {
            this.props.customerActions.checkCustomerExists(order.customer_email);
        }).catch(err => {
            window.location = this.redirectHome();
        });
    }

    componentDidUpdate() {
        //window.scrollTo(0, 0);
    }

    redirectHome() {
        window.location = url('');
    }

    toggleItemsExpanded() {
        this.setState({itemsExpanded: !this.state.itemsExpanded});
    }

    toggleAccountExpanded() {
        this.setState({accountExpanded: !this.state.accountExpanded});
    }

    onRegisterChange(event){
        event.preventDefault();

        const newRegisterForm = {
            ...this.state.registerForm,
            [event.target.name]: event.target.value
        };

        const registerErrors = registerValidator.validate(newRegisterForm);

        this.setState({
            registerErrors,
            registerForm: newRegisterForm
        });
    }

    onRegisterContinue(event) {
        event.preventDefault();
        if (this.state.loading) return;

        this.setState({loading: true});
        this.props.customerActions.createCustomer(this.state.registerForm.password).then(() => {
            this.setState({
                showSignUpSuccess: true,
                loading: false
            });
        }).catch(() => {
            this.setState({loading: false});
        });
    }

    render() {
        const {basket, order, customer} = this.props;
        if (!order || !order.order_id) return null;

        return (
            <div>
                <Header checkoutStep={STATE_SUCCESS}
                        shippingCost={basket.shipping}
                        shippingCostWithSymbol={basket.shipping_with_symbol}
                        basketTotalWithSymbol={basket.subtotal_incl_discount_currency}
                        grandTotalWithSymbol={basket.total_with_symbol} />
                <div className="checkout-maincontent">
                    <div className="checkout-maincontent__wrapper">
                        <div className="checkout-maincontent__container">
                            <div className="checkout-layout checkout-layout--animate">

                                <div className="checkout-section checkout-section--success-top">
                                    <div className="checkout-section__title">
                                        <div className="checkout-section__title-text">
                                            Thank you!
                                        </div>
                                    </div>
                                    <div className="checkout-section__container">
                                        <div className="checkout-success">
                                            <div className="checkout-success__msg">
                                                {customer.hasOwnProperty('id') && <p>Your order has been successfully placed.<br/>Order number: <a href={url(`/sales/order/view/order_id/${order.increment_id}/`)}>{order.increment_id}</a></p>}
                                                {!customer.hasOwnProperty('id') && <p>Your order has been successfully placed.<br/>Order number: {order.increment_id}</p>}

                                                <p>An email has been sent to: {order.customer_email}</p>
                                                {customer.hasOwnProperty('id') && <p><a href={url("customer/account/index")} className="button button--secondary button--arrow-right"><span>View my Account</span></a></p>}
                                                <p><a href={url('')} className="button button--secondary button--arrow-right"><span>Continue Shopping</span></a></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="checkout-layout__column checkout-layout__column--basket">
                                    {this.state.showSignUpSuccess && <p>Sign up successful!</p>}

                                    {!customer.existingCustomer &&
                                        <div className={'checkout-section checkout-section--register ' + (this.state.accountExpanded ? 'expanded' : '')}>
                                            <div className="checkout-section__title">
                                                <div className="checkout-section__title-text">
                                                    <span className="checkout-section__title-count">Create Account</span>
                                                </div>
                                                <a className="checkout-section__title-link"
                                                   onClick={e => this.toggleAccountExpanded(e)}>Create Account</a>
                                            </div>
                                            <div className="checkout-section__container">
                                                <div className="checkout-register">
                                                    <div className="checkout-register__container">
                                                        <RegisterForm
                                                            register={this.state.registerForm}
                                                            errors={this.state.registerErrors}
                                                            loading={this.state.loading}
                                                            onChange={(e) => this.onRegisterChange(e)}
                                                            onContinue={(e) => this.onRegisterContinue(e)}/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>}

                                    <div className={'checkout-section checkout-section--basket ' + (this.state.itemsExpanded ? 'expanded' : '')}>
                                        <div className="checkout-section__title">
                                            <div className="checkout-section__title-text">
                                                <span className="checkout-section__title-count">Order Summary</span>
                                            </div>
                                            <a className="checkout-section__title-link" onClick={(e) => this.toggleItemsExpanded(e)}>Order Summary</a>
                                        </div>
                                        <div className="checkout-section__container">
                                            <ListItems items={order.items}
                                                       itemsSaving={[]}
                                                       showFinalPrice />

                                            <Totals basket={order}
                                                    includeGiftCard={false}
                                                    displayType="primary"
                                                    hideRemoveDiscount
                                                    onContinue={false}/>
                                        </div>
                                    </div>

                                </div>

                                <div className="checkout-section checkout-section--success-bottom">
                                    <div className="checkout-section__container">
                                        <div className="checkout-success__box checkout-success__box--secondary">
                                            <div className="checkout-success__details">
                                                <div className="checkout-success__details-title">Billing address</div>
                                                <p>{order.billing_address.first_name && <span>{order.billing_address.first_name}&nbsp;</span>}
                                                    {order.billing_address.last_name && <span>{order.billing_address.last_name}</span>}
                                                    {order.billing_address.company && <span><br/>{order.billing_address.company}</span>}
                                                    {order.billing_address.line1 && <span><br/>{order.billing_address.line1}</span>}
                                                    {order.billing_address.line2 && <span><br/>{order.billing_address.line2}</span>}
                                                    {order.billing_address.city && <span><br/>{order.billing_address.city}</span>}
                                                    {order.billing_address.postcode && <span>,&nbsp;{order.billing_address.postcode}</span>}
                                                    {order.billing_address.phone && <span><br/>{order.billing_address.phone}</span>}
                                                    </p>
                                            </div>
                                            <div className="checkout-success__details">
                                                <div className="checkout-success__details-title">Shipping address</div>
                                                <p>{order.shipping_address.first_name && <span>{order.shipping_address.first_name}&nbsp;</span>}
                                                    {order.shipping_address.last_name && <span>{order.shipping_address.last_name}</span>}
                                                    {order.shipping_address.company && <span><br/>{order.shipping_address.company}</span>}
                                                    {order.shipping_address.line1 && <span><br/>{order.shipping_address.line1}</span>}
                                                    {order.shipping_address.line2 && <span><br/>{order.shipping_address.line2}</span>}
                                                    {order.shipping_address.city && <span><br/>{order.shipping_address.city}</span>}
                                                    {order.shipping_address.postcode && <span>,&nbsp;{order.shipping_address.postcode}</span>}
                                                    {order.shipping_address.phone && <span><br/>{order.shipping_address.phone}</span>}
                                                    </p>
                                            </div>
                                            <div className="checkout-success__details">
                                                <div className="checkout-success__details-title">Payment method</div>
                                                {order.payment_method && <p>{order.payment_method}</p>}
                                            </div>
                                            <div className="checkout-success__details">
                                                <div className="checkout-success__details-title">Delivery information</div>
                                                {order.shipping_method && <p>{order.shipping_method}</p>}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <BodyClass checkoutStep={6} />
                <DataLayer checkoutStep={6} />
            </div>
        );
    }
}

const mapStateToProps = (store) => {
    return {
        customer: store.customer,
        basket: store.basket,
        order: store.order
    }
};

const mapDispatchToProps = (dispatch) => {
    return {
        customerActions: bindActionCreators(customerActions, dispatch),
        orderActions: bindActionCreators(orderActions, dispatch)
    }
};

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(SuccessContainer)