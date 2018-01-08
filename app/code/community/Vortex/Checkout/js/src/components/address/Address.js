import React, {Component} from 'react';
import * as addressValidator from "../../validators/address";
import AddressFormFull from "./AddressFormFull";

export default class Address extends Component {

    constructor(props, context) {
        super(props, context);

        const errors = addressValidator.validate(props.address);

        this.state = {
            manualEntry: false,
            forceValidate: false,
            errors,
            addressChosen: errors._all
        };

        this.inputs = {};

        this.handleAddressSubmit = this.handleAddressSubmit.bind(this);
    }

    /**
     * Handle the address being update from elsewhere in the application.
     *
     * The only thing we need to do is ensure that the address is valid as per the validation rules specified
     * in here. If this wasn't executed the validation state would remain the same as if the address was not
     * updated.
     *
     * @param nextProps
     */
    componentWillReceiveProps(nextProps) {
        //  this.setState({ errors: addressValidator.validate(nextProps.address) });
    }

    /**
     * Proxies the address update so that we can update local validation state.
     * Anything other than validation is out of the responsibility scope of this component.
     *
     * @param event
     */
    onAddressFieldUpdate(event) {
        event.preventDefault();

        if (!this.state.errors.hasOwnProperty(event.target.name)) {
            return;
        }

        let addressDelta = {};
        addressDelta[event.target.name] = event.target.value;

        const newAddress = {...this.props.address, ...addressDelta};

        this.setState({errors: addressValidator.validate(newAddress)});

        this.props.handleAddressChange(newAddress);
    }

    /**
     * Proxies the address submit action so that we can update local UI state of 'address chosen'.
     *
     * It also does a validation sanity check
     *
     * @param address
     * @param force
     */
    handleAddressSubmit(address, force) {
        const newAddress = {...address, customer_address_id: null};
        const newErrors = addressValidator.validate(newAddress);

        this.setState({errors: newErrors});

        this.props.handleAddressChange(newAddress);

        // If there is an error..
        if (!newErrors._all) {
            // .. then set the state to manual entry and navigate the user to the first problem.
            this.setState({manualEntry: true, forceValidate: true}, () => {
                const firstError = Object.keys(this.state.errors).filter((key) => this.state.errors[key].length > 0)[0];
                this.inputs[firstError].focus();
            });

            return;
        }

        this.setState({addressChosen: true, manualEntry: false, loading: true});

        this.props.handleAddressSubmit(newAddress, force).then(() => {
            this.setState({loading: false});
        });
    }

    obtainRef(element) {
        if (element === null) return;

        this.inputs[element.name] = element;
    }

    toggleSaveInAddressBook(event) {
        event.preventDefault();

        const newAddress = {
            ...this.props.address,
            save_in_address_book: event.target.checked
        };


        this.handleAddressSubmit(newAddress, true);
    }

    toggleManualEntry(event) {
        const isChecked = event.target.checked;

        /**
         * On selecting manual entry, remove the 'address' fields
         */
        if (isChecked) {
            const address = this.props.address;
            this.props.handleAddressChange({
                ...addressValidator.emptyAddress,
                first_name: address.first_name,
                last_name: address.last_name,
                email: address.email,
                phone: address.phone,
            }, false, true);
        }

        this.setState({
            manualEntry: isChecked,
            addressChosen: false
        });
    }

    unsetAddressChosen() {
        const address = this.props.address;
        this.props.handleAddressChange({
            ...addressValidator.emptyAddress,
            first_name: address.first_name,
            last_name: address.last_name,
            email: address.email,
            phone: address.phone,
        }, false, true);
        this.setState({addressChosen:false});
    }

    render() {
        return <AddressFormFull addressLabel={this.props.addressLabel}
                                forceValidate={this.state.forceValidate}
                                obtainRef={(elm) => this.obtainRef(elm)}
                                errors={this.state.errors}
                                allowAddressSave={this.props.allowAddressSave}
                                address={this.props.address}
                                addressChosen={this.state.addressChosen}
                                unsetAddressChosen={() => this.unsetAddressChosen()}
                                manualEntry={this.state.manualEntry}
                                onToggleManualEntry={(event) => this.toggleManualEntry(event)}
                                onToggleSaveInAddressBook={(event) => this.toggleSaveInAddressBook(event)}
                                onSubmitAddress={this.handleAddressSubmit}
                                onManualAddressChange={(event) => this.onAddressFieldUpdate(event)}
                                loading={this.state.loading} />;
    }
}