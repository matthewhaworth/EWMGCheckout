import React, {Component} from 'react';
import SingleInput from "../common/SingleInput";
import * as basketActions from "../../actions/basketActions";
import {bindActionCreators} from "redux";
import {connect} from "react-redux";

class GiftCardForm extends Component {

    constructor(props, context) {
        super(props, context);

        this.state = {
            giftCard: {
                number: '',
                pin: ''
            },
            expanded: false,
            saving: false
        };
    }

    onChange(e) {
        this.setState({
            giftCard: {
                ...this.state.giftCard,
                [e.target.name]: e.target.value
            }
        });
    }

    toggleExpanded(e) {
        e.preventDefault();
        this.setState({expanded: !this.state.expanded});
    }

    onSubmit() {
        this.setState({saving: true});
        this.props.basketActions.applyGiftCard(this.state.giftCard.number, this.state.giftCard.pin)
            .then(() => {
                this.setState({
                    saving: false,
                    expanded: false,
                    giftCard: {
                        number: '',
                        pin: ''
                    }
                })
            })
            .catch(() => {
                this.setState({
                    saving: false,
                    expanded: true
                })
            });
    }

    render() {
        const giftCard = this.state.giftCard;

        return (
            <div>
                <div className="checkout-basket__giftcard">
                    <div className={'checkout-giftcard' + (this.state.expanded ? ' expanded' : '')}>
                        <a href="#" className="checkout-giftcard__title" onClick={(e) => this.toggleExpanded(e)}>Add gift card</a>
                        <div className="checkout-giftcard__container">
                            <form className="form form--giftcard">
                                <fieldset>
                                    <SingleInput inputType={'text'}
                                                 title="Gift Card Number"
                                                 name={'number'}
                                                 onChange={(e) => this.onChange(e)}
                                                 value={giftCard.number || ''}
                                                 additionalClassNames="form__control--small full"
                                                 noValidate={true} />
                                    <label>Pin</label>
                                    <SingleInput inputType={'text'}
                                                 name={'pin'}
                                                 onChange={(e) => this.onChange(e)}
                                                 value={giftCard.pin || ''}
                                                 additionalClassNames="form__control--small col-3-4"
                                                 noValidate={true} />

                                    <div className="form__control form__control--actions col-1-4">
                                        <button type="button" className={'button button--secondary button--small' + (this.state.saving ? ' button--loading' : '')}
                                                onClick={(e) => this.onSubmit(e)}>
                                            <span>Add</span>
                                        </button>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
};

const mapStateToProps = (state) => {
    return {
        basket: state.basket
    }
};

const mapDispatchToProps = (dispatch) => {
    return {
        basketActions: bindActionCreators(basketActions, dispatch)
    }
};

export default connect(mapStateToProps, mapDispatchToProps)(GiftCardForm);