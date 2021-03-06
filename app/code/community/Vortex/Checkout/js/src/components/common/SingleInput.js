import React from 'react';
import classNames from 'classnames';
import PropTypes from 'prop-types';
import InputMask from 'react-input-mask';

class SingleInput extends React.Component {

    constructor(props, context) {
        super(props, context);

        const isValid = typeof props.errors === 'undefined' || props.errors.length === 0;
        this.state = { wasBlurred: false, wasValid: false, isValid, hasChanged: false };
    }

    onChange(event) {
        this.setState({hasChanged: true});
        this.props.onChange(event);
    }

    onBlur(event) {
        if (this.props.hasOwnProperty('disableBlur')) {
            return;
        }

        this.props.onChange(event);
        this.setState({wasBlurred: true, hasChanged: true})
    }

    componentWillReceiveProps(nextProps) {
        const isValid = typeof nextProps.errors === 'undefined' || nextProps.errors.length === 0;
        const wasValid = this.state.wasValid || isValid;

        this.setState({isValid, wasValid});
    }

    renderInput() {
        const {name, inputType, placeholder, value, isDisabled, autocomplete, pattern} = this.props;

        const inputProps = {
            name,
            type: inputType,
            onChange: (e) => this.onChange(e),
            placeholder,
            value,
            autoComplete: autocomplete ? 'on' : 'off',
            ref: this.props.obtainRef || (() => {}),
            disabled: isDisabled,
            pattern: pattern,
            onBlur: (e) => this.onBlur(e)
        };

        if (this.props.inputMask) {
            return <InputMask {...inputProps} mask={this.props.inputMask} maskChar=" " />
        } else {
            return <input {...inputProps} />
        }
    }

    render() {
        const isValid = this.state.isValid;
        const {title, errors, isDisabled, forceValidate, isCard, isCvc, showPassword, note} = this.props;
        const disableBlur = typeof this.props.disableBlur !== 'undefined' && this.props.disableBlur;
        const shouldValidate = (!disableBlur && this.state.wasBlurred) || forceValidate || this.state.wasValid && this.state.hasChanged;

        const errorsList = (!isValid) ? <li key={errors[0]}>{errors[0]}</li> : '';
        const isLoading = this.props.hasOwnProperty('loading') && this.props.loading;

        let inputClassNames = 'form__control ';

        inputClassNames += classNames({
            'form__control--loading': isLoading,
            'form__control--disabled': isDisabled
        });

        if (!this.props.noValidate) {
            inputClassNames += classNames({
                'form__control--validated': shouldValidate && isValid,
                'form__control--error': shouldValidate && !isValid
            });
        }

        if (this.props.additionalClassNames) {
            inputClassNames += ' ' + this.props.additionalClassNames;
        }

        return (
            <div className={inputClassNames}>
                {title && <label className="form__label">
                    {title}
                    {showPassword && <span className="form__showpassword">Show</span>}
                    {note && <span className="form__note">{note}</span>}
                </label>}
                <div className="form__input">
                    {this.renderInput()}
                    {isLoading && <span></span>}

                    {isCard && <div className="cardtypes">
                        <div className="cardtype cardtype--visa"></div>
                        <div className="cardtype cardtype--mastercard">
                            <span>
                                <em className="path1"></em>
                                <em className="path2"></em>
                                <em className="path3"></em>
                                <em className="path4"></em>
                            </span>
                        </div>
                        <div className="cardtype cardtype--amex"></div>
                    </div>}
                    {isCvc && <div className="cvc">
                        <span className="cvc__trigger"></span>
                        <div className="cvc__container">
                            <div className="cvc__card cvc__card--mastervisa">
                                <em className="path1"/>
                                <em className="path2"/>
                                <em className="path3"/>
                                <em className="path4"/>
                                <em className="path5"/>
                            </div>
                            <div className="cvc__card cvc__card--amex">
                                <em className="path1"/>
                                <em className="path2"/>
                                <em className="path3"/>
                                <em className="path4"/>
                                <em className="path5"/>
                            </div>
                        </div>
                    </div>}
                </div>
                {!isValid && <ul className="form__error">{errorsList}</ul>}
            </div>
        );
    }
}

SingleInput.propTypes = {
    inputType: PropTypes.oneOf(['text', 'number', 'email', 'password']).isRequired,
    title: PropTypes.string,
    name: PropTypes.string.isRequired,
    onChange: PropTypes.func.isRequired,
    placeholder: PropTypes.string,
    errors: PropTypes.array,
    isPristine: PropTypes.bool,
    isDisabled: PropTypes.bool,
    noValidate: PropTypes.bool,
    forceValidate: PropTypes.bool,
    additionalClassNames: PropTypes.string,
    loading: PropTypes.bool
};

export default SingleInput;