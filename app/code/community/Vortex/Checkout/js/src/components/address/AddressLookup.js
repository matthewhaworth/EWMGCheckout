import React, {Component} from 'react';
import PostcodeApi from "../../api/PostcodeApi";
import {debounce} from "lodash";
import {config} from "../../config";

export default class AddressLookup extends Component {

    constructor(props, context) {
        super(props, context);

        this.state = {
            searchString: '',
            suggestedAddresses: [],
            loading: false
        };

        this.debounceAddressSearch = debounce(this.debounceAddressSearch, 300);
    }

    debounceAddressSearch(searchString) {
        PostcodeApi.postcodeFind(searchString).then(addresses =>
            this.setState({suggestedAddresses: addresses, loading: false})
        );
    }

    onAddressSearch(event) {
        event.persist();
        this.setState({searchString: event.target.value});

        if (event.target.value === '') return;

        this.setState({loading: true});
        this.debounceAddressSearch(event.target.value);
    }

    onAddressSelect(key) {
        const {suggestedAddresses} = this.state;

        if (suggestedAddresses.length === 0) {
            return;
        }

        let selectedAddress = suggestedAddresses.filter((address) => address.Id === key)[0];

        if (selectedAddress.hasOwnProperty('Error') && selectedAddress.Error) {
            return;
        }

        this.setState({loading: true});

        if (selectedAddress.Next === 'Retrieve') {
            PostcodeApi.postcodeRetrieve(key).then((addresses) => {
                selectedAddress = addresses[0];

                let addressDelta = {};
                addressDelta['line1'] = (selectedAddress.Company || '') + selectedAddress.Line1;
                addressDelta['line2'] = selectedAddress.Line2;
                addressDelta['city'] = selectedAddress.City;

                if (Object.keys(config.countryRegionList).includes(selectedAddress.CountryIso2)) {
                    addressDelta['region_id'] = selectedAddress.Province;
                } else {
                    addressDelta['county'] = selectedAddress.ProvinceName === '' ? selectedAddress.City : selectedAddress.ProvinceName;
                }

                addressDelta['postcode'] = selectedAddress.PostalCode;
                addressDelta['country'] = selectedAddress.CountryIso2;

                this.setState({suggestedAddresses: [], loading: false});

                const newAddress = {...this.props.address, ...addressDelta, customer_address_id: null};
                this.props.onSubmitAddress(newAddress, true);
            });
        } else {
            PostcodeApi.postcodeFind(this.state.searchString, key).then((addresses) => {
                this.setState({suggestedAddresses: addresses, loading: false});
            });
        }
    }

    render() {
        const suggestedAddressesList = this.state.suggestedAddresses.map((address) => {
            return <li className="form__autocomplete-item" key={address.Id} onClick={() => this.onAddressSelect(address.Id)}>
                {address.Type === 'Postcode' ? address.Description : (address.Text || address.Label)}
            </li>
        });

        return <div>
            <div className={'form__control form__control--autocomplete full ' + (this.state.loading ? 'form__control--loading' : '')}>
                <label className="form__label">Address Lookup</label>
                <div className="form__input">
                    <input type="text"
                           onChange={(e) => this.onAddressSearch(e)}
                           value={this.state.searchString || ''}
                           autoComplete="false"
                           disabled={this.props.disabled} />
                    {this.state.loading && <span />}
                </div>

                <div className={'form__autocomplete ' + (this.state.suggestedAddresses.length === 0 ? '' : 'active')}>
                    <ul className="form__autocomplete-list">
                        {suggestedAddressesList}
                    </ul>
                </div>
            </div>
        </div>;
    }
}