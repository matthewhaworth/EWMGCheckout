import React, {Component} from 'react';
import Address from "../address/Address";
import Checkbox from "../common/Checkbox";
import {ADDRESS_TYPE_BILLING} from "../../api/BagApi";
import {AddressDisplay} from "../address/AddressDisplay";
import AddressList from "../address/AddressList";
import * as addressValidator from "../../validators/address";

class BillingAddress extends Component {

    constructor(props, context) {
        super(props, context);

        this.state = {
            forceShowChooseBillingAddress: null,
            forceShowNewAddressEntry: null,
            loading: false,
            addressDisplayLoading: false
        }
    }

    shouldShowChooseBillingAddress() {
        // If the user has explicitly stated whether they want to be in an address change state
        if (this.state.forceShowChooseBillingAddress !== null) {
            return this.state.forceShowChooseBillingAddress;
        }

        const basket = this.props.basket;
        // if the user hasn't specified, determine the best action
        return !addressValidator.validate(basket.billing_address)._all || !basket.use_shipping_for_billing ||
            basket.is_click_and_collect;
    }

    shouldShowNewAddressEntry() {
        if (this.state.forceShowNewAddressEntry !== null) {
            return this.state.forceShowNewAddressEntry;
        }

        const {basket, customer} = this.props;
        const currentAddress = basket.billing_address;

        // If the current address is 'new' and the customer has chosen to not save to address book
        const currentAddressIsCustomerAddress = currentAddress.customer_address_id !== null;

        const customerIsLoggedIn = customer.hasOwnProperty('id');
        return !customerIsLoggedIn ||
            (customerIsLoggedIn && customer.addresses.length === 0) ||
            (addressValidator.validate(currentAddress)._all && !currentAddressIsCustomerAddress);
    }

    onToggleDifferentBillingAddress() {
        const forceShowChooseBillingAddress = !this.state.forceShowChooseBillingAddress;

        if (!forceShowChooseBillingAddress) {
            this.setState({addressDisplayLoading: true});
            this.handleAddressChange(this.props.basket.shipping_address, true).then(() => {
                this.setState({addressDisplayLoading: false});
            });
        }

        this.setState({forceShowChooseBillingAddress});
    }

    onPaymentAddressContinue(address) {
        return this.handleAddressChange(address, true);
    }

    handleAddressChange(address, submitToServer = false) {
        const {customer, saveAddress} = this.props;

        return saveAddress(customer, address, ADDRESS_TYPE_BILLING, submitToServer);
    }

    handleSavedAddressChoice(address) {
        this.setState({displayNewAddressEntry: false, loading: true});
        return this.handleAddressChange(address, true).then(() => {
            this.setState({loading: false});
        });
    }

    toggleDisplayNewAddressEntry(event) {
        const isChecked = event.target.checked;

        if (isChecked) {
            this.handleAddressChange({...this.props.shippingAddress, customer_address_id: null}).then(() => {
                this.setState({ displayNewAddressEntry: true});
            });
        } else {
            this.setState({ displayNewAddressEntry: false });
        }
    }

    render() {
        const {customer, basket, saveCustomerAddress} = this.props;

        const showAddressList = customer.hasOwnProperty('id') && customer.hasOwnProperty('addresses')
            && customer.addresses.length > 0;

        const showNewAddressEntry = this.shouldShowNewAddressEntry();
        const showChooseBillingAddress = this.shouldShowChooseBillingAddress();
        
        const addressList = <div>
            <AddressList
                addresses={customer.addresses}
                currentAddress={basket.billing_address}
                saveCustomerAddress={saveCustomerAddress}
                onChooseAddress={address => this.handleSavedAddressChoice(address) }
                loading={this.state.loading}
                listTitle="Select a stored billing address below"/>

            <Checkbox
                label="Use new address"
                name="new_customer_billing_address"
                checked={this.state.displayNewAddressEntry}
                onToggle={(e) => this.toggleDisplayNewAddressEntry(e)} />
        </div>;

        return (
            <div>
                {!showChooseBillingAddress &&
                <form className="form form--primary">
                    <fieldset>
                        <AddressDisplay addressLabel="Billing address"
                                        address={basket.billing_address}
                                        loading={this.state.addressDisplayLoading}
                                        onAddressClick={(e) => this.onToggleDifferentBillingAddress(e)}/>
                    </fieldset>
                </form>}

                {!basket.is_click_and_collect && <div className="form__control full">
                    <div className="form__checkbox">
                        <label className="form__label">
                            <input type="checkbox"
                                   name="address"
                                   checked={showChooseBillingAddress}
                                   onChange={(e) => this.onToggleDifferentBillingAddress(e)}
                                   value=""/>

                            <span className="form__label-text">Different billing address to above?</span>
                        </label>
                    </div>
                </div>}

                {showChooseBillingAddress && (
                    <div>
                        {showAddressList && addressList}

                        {showNewAddressEntry &&
                            <Address addressLabel="Billing address"
                                     address={basket.billing_address}
                                     allowAddressSave={customer.hasOwnProperty('id')}
                                     handleAddressChange={(e) => this.handleAddressChange(e)}
                                     handleAddressSubmit={(e) => this.onPaymentAddressContinue(e)} />}
                    </div>)}
            </div>
        );
    }
}

export default BillingAddress;