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

        const currentAddress = props.basket.billing_address;

        // If the current address is 'new' and the customer has chosen to not save to address book
        const currentAddressIsCustomerAddress = currentAddress.customer_address_id !== null;

        const customerIsLoggedIn = props.customer.hasOwnProperty('id');
        const displayNewAddressEntry = !customerIsLoggedIn ||
            (customerIsLoggedIn && props.customer.addresses.length === 0) ||
            (addressValidator.validate(currentAddress)._all && !currentAddressIsCustomerAddress);

        this.state = {
            forceShowChooseBillingAddress: null,
            loading: false,
            displayNewAddressEntry
        }
    }

    shouldShowChooseBillingAddress() {
        const basket = this.props.basket;
        const forceShowChooseBillingAddress = this.state.forceShowChooseBillingAddress;

        // If the user has explicitly stated whether they want to be in an address change state
        if (forceShowChooseBillingAddress !== null) {
            return forceShowChooseBillingAddress;
        }

        // if the user hasn't specified, determine the best action
        return !addressValidator.validate(basket.billing_address)._all || !basket.use_shipping_for_billing ||
            basket.is_click_and_collect;
    }

    onToggleDifferentBillingAddress() {
        this.setState({forceShowChooseBillingAddress: !this.state.forceShowChooseBillingAddress});
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

        const chooseBillingAddress = this.shouldShowChooseBillingAddress();
        
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
                {!chooseBillingAddress &&
                <form className="form form--primary">
                    <fieldset>
                        <AddressDisplay addressLabel="Billing address"
                                        address={basket.billing_address}
                                        onAddressClick={(e) => this.onToggleDifferentBillingAddress(e)}/>
                    </fieldset>
                </form>}

                {!basket.is_click_and_collect && <div className="form__control full">
                    <div className="form__checkbox">
                        <label className="form__label">
                            <input type="checkbox"
                                   name="address"
                                   checked={chooseBillingAddress}
                                   onChange={(e) => this.onToggleDifferentBillingAddress(e)}
                                   value=""/>

                            <span className="form__label-text">Different billing address to above?</span>
                        </label>
                    </div>
                </div>}

                {chooseBillingAddress && (
                    <div>
                        {showAddressList && addressList}

                        {this.state.displayNewAddressEntry &&
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