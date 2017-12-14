import React, {Component} from 'react';
import classNames from 'classnames';
import PropTypes from 'prop-types';

class Dropdown extends Component {

    constructor(props, context) {
        super(props, context);

        this.state = {isPristine: true};
        this.onChange = this.onChange.bind(this);
    }

    onChange(event) {
        this.setState({ isPristine: false });
        this.props.onChange(event);
    }

    render() {
        const {name, title, placeholder, options, errors, isDisabled} = this.props;
        const shouldValidate = !this.state.isPristine || this.props.forceValidate;
        const isValid = typeof errors === 'undefined' || errors.length === 0;
        const errorsList = (!isValid) ? errors.map((message, index) => <li key={index}>{message}</li>) : '';
        const optionsList = (typeof options !== 'undefined')
            ? options.map((option, index) => {
                if(option.value && option.value !== ""){
                    return <option key={index} value={option.value}>{option.label}</option>
                }

                return null;
            })
            : null;

        let inputClassNames = 'form__control ';

        inputClassNames += classNames({
            'form__control--validated': shouldValidate && isValid,
            'form__control--error': shouldValidate && !isValid,
            'form__control--disabled': isDisabled,
        });

        if (this.props.additionalClassNames) {
            inputClassNames += ' ' + this.props.additionalClassNames;
        }

        return (
            <div className={inputClassNames}>
                {title && <label className="form__label">{title}</label>}
                <div className="form__select">
                    <select
                        name={name}
                        value={this.props.value}
                        onChange={(e) => this.onChange(e)}
                        disabled={isDisabled}>
                        <option value="">{placeholder}</option>
                        {optionsList}
                    </select>
                </div>
                {!isValid && <ul className="form__error">{errorsList}</ul>}
            </div>
        );
    }

}

Dropdown.propTypes = {
    title: PropTypes.string,
    name: PropTypes.string.isRequired,
    onChange: PropTypes.func.isRequired,
    placeholder: PropTypes.string,
    errors: PropTypes.array,
    isDisabled: PropTypes.bool,
    additionalClassNames: PropTypes.string,
    loading: PropTypes.bool
};

export default Dropdown;