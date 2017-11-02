import React from 'react';
import AddressEdit from "./AddressEdit";
import {Component} from "react/lib/ReactBaseClasses";

class AddressList extends Component {

    constructor(props, context) {
        super (props,context);

        this.state = {
            addressUnderEdit: {},
            isEditMode: false,
            loading: false
        };
    }

    editAddress(address) {
        this.setState({ addressUnderEdit: address, isEditMode: true });
    }

    saveAddress(address) {
        this.setState({ loading: true }, () => {
            this.props.saveCustomerAddress(address).then(() => {
                this.setState({addressUnderEdit: {}, isEditMode: false, loading: false});
            });
        });
    }

    closeEditAddress() {
        this.setState({ isEditMode: false });
    }

    render() {
        const {addresses, currentAddress, onChooseAddress, listTitle} = this.props;
        const isLoading = (this.props.hasOwnProperty('loading') && this.props.loading) || this.state.loading;

        const addressList = addresses.map((address) => {
            const checked = currentAddress.hasOwnProperty('customer_address_id') &&
                address.customer_address_id === currentAddress.customer_address_id;

            return <div className="form__radio form__radio--list" key={address.customer_address_id}>
                <label className="form__label">
                    <a className="form__label-link" href="#" onClick={() => this.editAddress(address)}>Edit</a>
                    <input type="radio"
                           name="customer_address_list"
                           onChange={() => onChooseAddress(address)}
                           value={address.customer_address_id}
                           checked={checked}/>
                        <span className="form__label-text">
                            <span className="strong">{address.first_name} {address.last_name}</span><br/>
                            {address.line1},<br/>
                            {address.city}, {address.country}
                        </span>
                </label>
            </div>
        });

        return (
            <div>
                <form className="form form--primary">
                    <fieldset>
                        <div className="form__control full">
                            {listTitle && <p>{listTitle}</p>}
                            {addressList}
                            {isLoading && <div className="checkout-loader active"><div className="checkout-loader__spinner"><span /></div></div>}
                        </div>
                    </fieldset>
                </form>
                <AddressEdit address={this.state.addressUnderEdit}
                             active={this.state.isEditMode}
                             loading={this.state.loading}
                             saveCustomerAddress={(address) => this.saveAddress(address)}
                             closeEditAddress={() => this.closeEditAddress()} />
            </div>
        );
    }
}

export default AddressList;