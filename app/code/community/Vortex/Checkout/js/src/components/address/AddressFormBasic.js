import React from 'react';
import SingleInput from "../common/SingleInput";

const AddressFormBasic = ({address, errors, forceValidate, obtainRef, onManualAddressChange}) => {
    return <div>
        <SingleInput inputType={'text'}
                     title={'First name'}
                     name={'first_name'}
                     forceValidate={forceValidate}
                     onChange={(e) => onManualAddressChange(e)}
                     errors={errors.first_name}
                     value={address.first_name || ''}
                     obtainRef={obtainRef}
                     additionalClassNames="col-2-4" />

        <SingleInput inputType={'text'}
                     title={'Last name'}
                     name={'last_name'}
                     forceValidate={forceValidate}
                     onChange={(e) => onManualAddressChange(e)}
                     errors={errors.last_name}
                     value={address.last_name || ''}
                     obtainRef={obtainRef}
                     additionalClassNames="col-2-4" />

        <SingleInput inputType={'text'}
                     title={'Phone'}
                     name={'phone'}
                     forceValidate={forceValidate}
                     onChange={(e) => onManualAddressChange(e)}
                     errors={errors.phone}
                     value={address.phone || ''}
                     obtainRef={obtainRef}
                     additionalClassNames="full"
                     note={'(Mobile number for delivery text)'}/>
    </div>;
};

export default AddressFormBasic;