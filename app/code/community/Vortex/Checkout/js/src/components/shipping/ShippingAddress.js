import React, {Component} from 'react';
import Address from "../address/Address";
import {ADDRESS_TYPE_SHIPPING} from "../../api/BagApi";
import AddressList from "../address/AddressList";
import Checkbox from "../common/Checkbox";
import * as addressValidator from "../../validators/address";

class ShippingAddress extends Component {
    constructor(props, context) {
        super(props, context);

        this.state = {
            forceShowNewAddressEntry: null
        };
    }

    onShippingAddressContinue(address) {
        return this.handleAddressChange(address, true);
    }

    handleAddressChange(address, submitToServer = false) {
        const {customer, saveAddress} = this.props;
        return saveAddress(customer, address, ADDRESS_TYPE_SHIPPING, submitToServer);
    }

    handleSavedAddressChoice(address) {
        this.setState({displayNewAddressEntry: false, loading: true});
        return this.handleAddressChange(address, true)
            .then(() => {
                this.setState({loading: false});
            })
            .catch((err) => {
                this.setState({loading: false});
            });
    }

    showNewAddressEntry() {
        if (this.state.forceShowNewAddressEntry) {
            return this.state.forceShowNewAddressEntry;
        }

        const customer = this.props.customer;
        const currentAddress = this.props.shippingAddress;

        // If the current address is 'new' and the customer has chosen to not save to address book
        const currentAddressIsCustomerAddress = currentAddress.customer_address_id !== null;

        const customerIsLoggedIn = customer.hasOwnProperty('id');
        return !customerIsLoggedIn ||
            (customerIsLoggedIn && customer.addresses.length === 0) ||
            (addressValidator.validate(currentAddress)._all && !currentAddressIsCustomerAddress);

    }

    toggleDisplayNewAddressEntry(event) {
        if (event.target.checked) {
            this.handleAddressChange({...this.props.shippingAddress, customer_address_id: null});
        }

        this.setState({ displayNewAddressEntry: event.target.checked});
    }

    render() {
        const addressList = <div>
            <AddressList
                addresses={this.props.customer.addresses}
                currentAddress={this.props.shippingAddress}
                saveCustomerAddress={this.props.saveCustomerAddress}
                onChooseAddress={(address) => this.handleSavedAddressChoice(address) }
                loading={this.state.loading}
                listTitle="Select a stored delivery address below"/>
            <Checkbox
                label="Delivery to new address"
                name="new_customer_address"
                checked={this.state.displayNewAddressEntry}
                onToggle={(e) => this.toggleDisplayNewAddressEntry(e)} />
        </div>;

        const showNewAddressEntry = this.showNewAddressEntry();

        const showAddressList = this.props.customer.hasOwnProperty('id')
            && this.props.customer.hasOwnProperty('addresses')
            && this.props.customer.addresses.length > 0;

        return (
            <div>
                {showAddressList && addressList}

                {showNewAddressEntry && <Address addressLabel="Delivery address" address={this.props.shippingAddress}
                         allowAddressSave={this.state.displayNewAddressEntry && this.props.customer.hasOwnProperty('id')}
                         handleAddressChange={(e) => this.handleAddressChange(e)}
                         handleAddressSubmit={(e) => this.onShippingAddressContinue(e)} />}
            </div>
        );
    }
}

export default ShippingAddress;