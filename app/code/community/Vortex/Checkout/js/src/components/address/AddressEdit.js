import React, {Component} from 'react';
import SingleInput from "../common/SingleInput";
import Dropdown from "../common/Dropdown";
import * as addressValidator from "../../validators/address";
import {config} from '../../config';

export default class AddressEdit extends Component {
    constructor(props, context) {
        super(props, context);

        this.state = {
            address: props.address,
            errors: addressValidator.validate(props.address)
        };
    }

    componentWillReceiveProps(nextProps) {
        this.updateAddress(nextProps.address);
    }

    updateAddress(address) {
        this.setState({
            address,
            errors: addressValidator.validate(address)
        });
    }

    onAddressChange(event) {
        const newAddress = {
            ...this.state.address,
            [event.target.name]: event.target.value
        };

        this.updateAddress(newAddress);
    }

    render() {
        const {address, errors} = this.state;
        const active = this.props.active;

        return (
            <div className={'checkout-modal ' + (active ? 'active' : '')}>
                <div onClick={() => this.props.closeEditAddress()} className="checkout-loader checkout-loader--large checkout-loader--dark checkout-loader--overlay">
                    <div className="checkout-loader__spinner"><span /></div>
                </div>
                <div className="checkout-modal__container">
                    <a className="checkout-modal__close button--icon-close"
                       onClick={() => this.props.closeEditAddress()} />

                    <div className="checkout-modal__title">Edit Address</div>

                    <div className="form form--modal">
                        <SingleInput inputType={'text'}
                                     title={'First name'}
                                     name={'first_name'}
                                     onChange={(e) => this.onAddressChange(e)}
                                     errors={errors.first_name}
                                     value={address.first_name || ''}
                                     additionalClassNames="col-2-4" />

                        <SingleInput inputType={'text'}
                                     title={'Last name'}
                                     name={'last_name'}
                                     onChange={(e) => this.onAddressChange(e)}
                                     errors={errors.last_name}
                                     value={address.last_name || ''}
                                     additionalClassNames="col-2-4" />

                        <SingleInput inputType={'text'}
                                     title={'Phone'}
                                     name={'phone'}
                                     onChange={(e) => this.onAddressChange(e)}
                                     errors={errors.phone}
                                     value={address.phone || ''}
                                     additionalClassNames="full" />

                        <SingleInput inputType={'text'}
                                     title={'Line 1'}
                                     name={'line1'}
                                     onChange={(e) => this.onAddressChange(e)}
                                     errors={errors.line1}
                                     value={address.line1 || ''}
                                     additionalClassNames="full" />

                        <SingleInput inputType={'text'}
                                     name={'line2'}
                                     onChange={(e) => this.onAddressChange(e)}
                                     errors={errors.line2}
                                     value={address.line2 || ''}
                                     placeholder="optional"
                                     additionalClassNames="form__control--nomargin full"/>

                        <SingleInput inputType={'text'}
                                     title={'City'}
                                     name={'city'}
                                     onChange={(e) => this.onAddressChange(e)}
                                     errors={errors.city}
                                     value={address.city || ''}
                                     additionalClassNames="full" />

                        <SingleInput inputType={'text'}
                                     title={'County'}
                                     name={'county'}
                                     onChange={(e) => this.onAddressChange(e)}
                                     errors={errors.county}
                                     value={address.county || ''}
                                     additionalClassNames="full" />

                        <Dropdown title={'Country'}
                                  name="country"
                                  onChange={(e) => this.onAddressChange(e)}
                                  errors={errors.country}
                                  options={config.countryList}
                                  placeholder="Select country"
                                  value={address.country}
                                  additionalClassNames="full"/>

                        <SingleInput inputType={'text'}
                                     title={'Post Code'}
                                     name={'postcode'}
                                     onChange={(e) => this.onAddressChange(e)}
                                     errors={errors.postcode}
                                     value={address.postcode || ''}
                                     additionalClassNames="full" />

                        <div className="form__control form__control--actions">
                            <button className="button button--small button--fifth" onClick={() => this.props.closeEditAddress()}><span>Cancel</span></button>
                            <button className={'button button--secondary ' + (this.props.loading ? 'button--loading' : '')}
                                    disabled={!this.state.errors._all}
                                    onClick={() => this.props.saveCustomerAddress(this.state.address)}>
                                <span>Save this address</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
};