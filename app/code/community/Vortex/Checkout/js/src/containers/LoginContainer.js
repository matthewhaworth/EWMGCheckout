import React, {Component} from 'react';
import {connect} from "react-redux";
import {bindActionCreators} from "redux";
import * as customerActions from "../actions/customerActions";
import * as customerValidator from "../validators/customer";
import LoginForm from "../components/customer/LoginForm";

class LoginContainer extends Component {
    constructor(props, context) {
        super(props, context);

        const errors = customerValidator.validate(this.props.customer);
        this.state = { errors, loading: false };
    }

    onChange(event) {
        event.preventDefault();

        let newCustomer = Object.assign({}, this.props.customer);
        newCustomer[event.target.name] = event.target.value;

        const errors = customerValidator.validate(newCustomer);
        this.setState({errors});

        this.props.customerActions.updateCustomer(newCustomer);

        if (event.target.name === 'email' && errors.email.length === 0) {
            this.setState({ loading: true });
            this.props.customerActions.checkCustomerExists(event.target.value).then(() => {
                this.setState({ loading: false });
            }).catch(() => {
                this.setState({ loading: false });
            });
        }
    }

    customerLoginUnsuccessful() {
        const newCustomer = {...this.props.customer, password: ''};
        this.props.customerActions.updateCustomer(newCustomer);
        this.setState({ loading: false, errors: { password: ['Your password was not correct. ']} });
    }

    onContinueAsLoggedIn(event) {
        event.preventDefault();
        if (this.state.loading) return;

        this.setState({loading: true});

        if (this.props.customer.existingCustomer) {
            const customer = this.props.customer;

            this.props.customerActions.authenticate(customer.email, customer.password).then((customer) => {
                if (customer.hasOwnProperty('id')) {
                    this.props.onContinue();
                } else {
                    this.customerLoginUnsuccessful();
                }
            }).catch(() => {
                this.customerLoginUnsuccessful();
            });
        } else {
            this.props.customerActions.checkCustomerExists(this.props.customer.email).then(() => {
                this.setState({ loading: false });
            }).catch(() => {
                this.setState({ loading: false });
            });
        }
    }

    render() {
        return (
            <div className="checkout-section checkout-section--checkout">
                <div className="checkout-section__container">
                    <div className="checkout-login">
                        <div className="checkout-login__container">
                            <LoginForm
                                customer={this.props.customer}
                                errors={this.state.errors}
                                loading={this.state.loading}
                                customerExists={this.props.customer.existingCustomer}
                                onChange={(e) => this.onChange(e)}
                                onContinueAsLoggedIn={(e) => this.onContinueAsLoggedIn(e)}
                                onContinueAsGuest={() => this.props.onContinue()} />
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

const mapStateToProps = (state) => {
    return {
        customer: state.customer
    }
};

const mapDispatchToProps = (dispatch) => {
    return {
        customerActions: bindActionCreators(customerActions, dispatch)
    }
};

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(LoginContainer);