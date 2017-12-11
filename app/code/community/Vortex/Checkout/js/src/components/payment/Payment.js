import React, {Component} from 'react';
import CreditCardForm from "./CreditCardForm";
import {config} from "../../config";
import * as creditCardValidator from "../../validators/creditCard";
import creditCardType from 'credit-card-type';
import CreditCardList from "./CreditCardList";
import * as addressValidator from "../../validators/address";
import OrderSubmission from "./OrderSubmission";
import BillingAddress from "../billing/BillingAddress";
import * as checkoutSteps from '../../store/checkoutSteps';
import * as ReactDOM from 'react-dom';
import {connect} from "react-redux";

class Payment extends Component {

    scrollToView(){
        const node = ReactDOM.findDOMNode(this.refs.STATE_PAYMENT);

        if(node && !this.state.loading){
            node.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
                inline: 'start'
            });
        }

    }

    constructor(props, context) {
        super(props, context);
        
        this.state = {
            creditCard: {},
            selectedCardId: null,
            saveCard: false,
            errors: {},
            loading: false,
            processingSubmit: false,
            creditCardFormVisible: !props.customer.savedCards || props.customer.savedCards.length === 0
        }
    }

    onCreditCardChange(event) {
        const newCreditCard = {
            ...this.state.creditCard,
            [event.target.name]: event.target.value
        };

        if (event.target.name === 'cardNumber') {
            const cardType = creditCardType(event.target.value.split(' ').join(''));
            if (cardType.length === 1) {
                newCreditCard.type = cardType[0].type;
            } else {
                newCreditCard.type = '';
            }
        }

        this.setState({
            creditCard: newCreditCard,
            errors: creditCardValidator.validate(newCreditCard)
        });
    }

    mapCardDataToWorldpay(cardData) {
        const expiryDates = cardData.expiry.split('/');
        return {
            cvc: cardData.cvc,
            cardHolderName: cardData.cardHolderName,
            cardNumber: cardData.cardNumber.split(' ').join(''),
            expiryMonth: expiryDates[0],
            expiryYear: parseInt(expiryDates[1], 10) + 2000 // Converts '17' to '2017' @todo Change this in 80 years
        };
    }

    onOrderSubmit(history, newsletter, thirdParty) {
        this.setState({processingSubmit: true});

        let placeOrderPromise = null;
        if (this.state.selectedCardId === null) {
            try {
                const mappedCardData = this.mapCardDataToWorldpay(this.state.creditCard);
                window.Worldpay.setPublicKey(config.worldpay.publicKey);
                const encryptedCardData = window.Worldpay.encrypt(mappedCardData);

                placeOrderPromise = this.props.placeOrder(
                    newsletter,
                    thirdParty,
                    encryptedCardData,
                    this.state.creditCard.type,
                    null,
                    this.state.saveCard
                );
            } catch (e) {
                placeOrderPromise = Promise.reject();
            }
        } else {
            placeOrderPromise = this.props.placeOrder(newsletter, thirdParty, null, null, this.state.selectedCardId);
        }

        return placeOrderPromise.then((resp) => {
            this.setState({processingSubmit: false});
            history.push(`/success/${resp.increment_id}`);
        }).catch(err => {
            this.setState({processingSubmit: false});
            throw err;
        });
    }

    toggleCreditCardFormVisible(event) {
        event.preventDefault();

        const newState = { creditCardFormVisible: !this.state.creditCardFormVisible };

        if (this.state.creditCardFormVisible) {
            newState.creditCard = {};
        } else {
            newState.selectedCardId = null;
        }

        this.setState(newState);
    }

    onSelectCard(e) {
        this.setState({
            creditCard: {},
            creditCardFormVisible: false,
            selectedCardId: e.target.value
        });
    }

    toggleSaveCard(event) {
        this.setState({saveCard: event.target.checked});
    }

    render() {
        const {customer, basket, saveAddress, saveCustomerAddress} = this.props;

        const canPlaceOrder = (this.state.selectedCardId || creditCardValidator.validate(this.state.creditCard)._all) &&
            addressValidator.validate(basket.billing_address)._all;

        return (
            <div className="checkout-section checkout-section--checkout" ref={checkoutSteps.STATE_PAYMENT}>
                <div className="checkout-section__container">
                    <div className="form__title form__title--primary"><span className="form__title-count">3</span>Secure payment</div>

                    {customer.savedCards && customer.savedCards.length > 0 &&
                        <CreditCardList creditCards={customer.savedCards}
                                        selectedCardId={this.state.selectedCardId}
                                        onSelectCard={(e) => this.onSelectCard(e)}
                                        toggleCreditCardFormVisible={(e) => this.toggleCreditCardFormVisible(e)}/>}

                    {this.state.creditCardFormVisible && <CreditCardForm
                        creditCard={this.state.creditCard}
                        errors={this.state.errors}
                        canSaveCard={customer.hasOwnProperty('id')}
                        saveCard={this.state.saveCard}
                        toggleSaveCard={(e) => this.toggleSaveCard(e)}
                        onCreditCardChange={(e) => this.onCreditCardChange(e)} />}

                    <BillingAddress customer={customer} 
                                    basket={basket}
                                    saveAddress={saveAddress}
                                    saveCustomerAddress={saveCustomerAddress} />

                    <OrderSubmission basket={basket}
                                     canPlaceOrder={canPlaceOrder}
                                     addDiscountCode={code => this.props.addDiscountCode(code)}
                                     removeDiscountCode={() => this.props.removeDiscountCode()}
                                     onOrderSubmit={(history, newsletter, thirdParty) => this.onOrderSubmit(history, newsletter, thirdParty)} />

                    <div className={'checkout-section__fader ' + (this.props.active ? '' : 'active')}/>

                </div>
            </div>
        );
    }
}

export default connect(null, null , null, { withRef: true })(Payment);