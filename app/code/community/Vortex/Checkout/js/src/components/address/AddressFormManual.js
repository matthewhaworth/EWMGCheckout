import React from 'react';
import Dropdown from "../common/Dropdown";
import SingleInput from "../common/SingleInput";
import {config} from '../../config';

const AddressFormManual = ({errors, address, forceValidate, obtainRef, onSubmitAddress, onManualAddressChange}) => {
    return <div>
        <SingleInput inputType={'text'}
                     title={'Line 1'}
                     name={'line1'}
                     obtainRef={obtainRef}
                     onChange={(e) => onManualAddressChange(e)}
                     errors={errors.line1}
                     value={address.line1 || ''}
                     forceValidate={forceValidate}
                     additionalClassNames="full" />

        <SingleInput inputType={'text'}
                     name={'line2'}
                     obtainRef={obtainRef}
                     onChange={(e) => onManualAddressChange(e)}
                     errors={errors.line2}
                     value={address.line2 || ''}
                     placeholder="optional"
                     forceValidate={forceValidate}
                     additionalClassNames="form__control--nomargin full"/>

        <SingleInput inputType={'text'}
                     title={'City'}
                     name={'city'}
                     obtainRef={obtainRef}
                     onChange={(e) => onManualAddressChange(e)}
                     errors={errors.city}
                     value={address.city || ''}
                     forceValidate={forceValidate}
                     additionalClassNames="full" />

        {Object.keys(config.countryRegionList).includes(address.country) &&
            <Dropdown title={'State'}
                      name="region_id"
                      onChange={(e) => onManualAddressChange(e)}
                      errors={errors.region_id}
                      options={config.countryRegionList[address.country]}
                      placeholder="Select state"
                      value={address.region_id || ''}
                      additionalClassNames="full"/>}

        {!Object.keys(config.countryRegionList).includes(address.country) &&
            <SingleInput inputType={'text'}
                         title={'County'}
                         name={'county'}
                         obtainRef={obtainRef}
                         onChange={(e) => onManualAddressChange(e)}
                         errors={errors.county}
                         value={address.county || ''}
                         forceValidate={forceValidate}
                         additionalClassNames="full" />}

        <Dropdown title={'Country'}
                  name="country"
                  onChange={(e) => onManualAddressChange(e)}
                  errors={errors.country}
                  options={config.countryList}
                  placeholder="Select country"
                  value={address.country}
                  additionalClassNames="full"/>

        <SingleInput inputType={'text'}
                     title={address.country === 'US' ? 'Zip Code' : 'Post Code'}
                     name={'postcode'}
                     obtainRef={obtainRef}
                     onChange={(e) => onManualAddressChange(e)}
                     errors={errors.postcode}
                     value={address.postcode || ''}
                     forceValidate={forceValidate}
                     additionalClassNames="full" />

        <div className="form__control form__control--actions">
            <button type='button'
                    /*disabled={!errors._all}*/
                    onClick={() => onSubmitAddress(address, true)}
                    className="button button--primary button--arrow-right">
                <span>Continue</span>
            </button>
        </div>
    </div>;
};

export default AddressFormManual;