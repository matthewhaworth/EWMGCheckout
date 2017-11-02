import React from 'react';
import SingleInput from "../../common/SingleInput";

const ClickCollectForm = ({postcode, loading, onUpdatePostcode, onSubmitPostcode}) => {

        return <form className="form form--primary">
            <fieldset>
                <SingleInput inputType={'text'}
                             title={'Post Code'}
                             name={'postcode'}
                             onChange={(e) => onUpdatePostcode(e)}
                             value={postcode}
                             autocomplete={false}
                             noValidate
                             additionalClassNames="full" />

                <div className="form__control form__control--actions">
                    <button type='button'
                            onClick={(e) => onSubmitPostcode(e)}
                            className={'button button--primary ' + (loading ? 'button--loading' : 'button--arrow-right')}>
                        <span>Find nearest stores</span>
                    </button>
                </div>

            </fieldset>
        </form>
};

export default ClickCollectForm;