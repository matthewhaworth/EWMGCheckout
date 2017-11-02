import React from 'react';
import {AddressDisplay} from "../address/AddressDisplay";
import AddressLookup from "./AddressLookup";
import AddressFormManual from "./AddressFormManual";
import AddressFormBasic from "./AddressFormBasic";
import Checkbox from "../common/Checkbox";

const AddressFormFull = ({
    addressLabel,
    errors,
    forceValidate,
    allowAddressSave,
    address,
    addressChosen,
    unsetAddressChosen,
    manualEntry,
    obtainRef,
    onToggleManualEntry,
    onToggleSaveInAddressBook,
    onSubmitAddress,
    onManualAddressChange,
    loading
}) => {

    return (
        <form className="form form--primary">
            <fieldset>
                <AddressFormBasic address={address}
                                  errors={errors}
                                  forceValidate={forceValidate}
                                  obtainRef={obtainRef}
                                  onManualAddressChange={onManualAddressChange}/>

                {!addressChosen && <AddressLookup address={address}
                                                  onSubmitAddress={onSubmitAddress}
                                                  disabled={manualEntry} />}

                {addressChosen && <AddressDisplay addressLabel={addressLabel}
                                                  address={address}
                                                  onAddressClick={() => unsetAddressChosen()}
                                                  loading={loading} />}

                <Checkbox label="Can't find your address?"
                          name='address_manual_entry'
                          checked={manualEntry}
                          onToggle={onToggleManualEntry}
                          additionalClassNames="full"/>

                {manualEntry && <AddressFormManual address={address}
                                                   errors={errors}
                                                   obtainRef={obtainRef}
                                                   forceValidate={forceValidate}
                                                   onSubmitAddress={onSubmitAddress}
                                                   onManualAddressChange={onManualAddressChange} />}

                {allowAddressSave && errors._all && <Checkbox label="Save in address book"
                          name='save_in_address_book'
                          checked={address.save_in_address_book}
                          onToggle={onToggleSaveInAddressBook}
                          additionalClassNames="full"/>}

            </fieldset>
        </form>
    );
};

export default AddressFormFull;