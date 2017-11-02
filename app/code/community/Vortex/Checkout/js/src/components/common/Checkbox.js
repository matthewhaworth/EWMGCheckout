import React from 'react';

const Checkbox = ({label, name, checked, onToggle, additionalClassNames, checkboxAdditionalClassNames}) => {

    let checkboxClassNames = 'form__checkbox';
    let classNames = 'form__control';

    if(additionalClassNames){
        classNames += ' ' + additionalClassNames;
    }

    if (checkboxAdditionalClassNames) {
        checkboxClassNames += ' ' + checkboxAdditionalClassNames;
    }

    return <div className={classNames}>
        <div className={checkboxClassNames}>
            <label className="form__label">
                <input type="checkbox"
                       name={name}
                       checked={checked}
                       onChange={onToggle}
                       className=""
                       value="" />
                <span className="form__label-text">{label}</span>
            </label>
        </div>
    </div>;
};

export default Checkbox;