import React, {Component} from 'react';
import CreditCardForm from "./CreditCardForm";
import Address from "../address/Address";
import Checkbox from "../common/Checkbox";
import {ADDRESS_TYPE_BILLING} from "../../api/BagApi";
import {AddressDisplay} from "../address/AddressDisplay";
import {config} from "../../config";
import * as creditCardValidator from "../../validators/creditCard";
import creditCardType from 'credit-card-type';
import CreditCardList from "./CreditCardList";
import AddressList from "../address/AddressList";
import * as addressValidator from "../../validators/address";
import OrderSubmission from "./OrderSubmission";

class Payment extends Component {

    constructor(props, context) {
        super(props, context);

        const currentAddress = props.basket.billing_address;

        // If the current address is 'new' and the customer has chosen to not save to address book
        const currentAddressIsCustomerAddress = currentAddress.customer_address_id !== null;

        const customerIsLoggedIn = props.customer.hasOwnProperty('id');
        const displayNewAddressEntry = !customerIsLoggedIn ||
            (customerIsLoggedIn && props.customer.addresses.length === 0) ||
            (addressValidator.validate(currentAddress)._all && !currentAddressIsCustomerAddress);

        this.state = {
            differentBillingToShippingAddress: !addressValidator.validate(props.basket.shipping_address)._all ||
                !props.basket.use_shipping_for_billing,
            creditCard: {},
            selectedCardId: null,
            saveCard: false,
            errors: {},
            loading: false,
            processingSubmit: false,
            displayNewAddressEntry,
            creditCardFormVisible: !props.customer.savedCards || props.customer.savedCards.length === 0
        }
    }

    componentWillReceiveProps(nextProps) {
        this.setState({
            differentBillingToShippingAddress: !nextProps.basket.use_shipping_for_billing,
        });
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

    onToggleDifferentBillingAddress() {
        this.setState({differentBillingToShippingAddress: !this.state.differentBillingToShippingAddress});
    }

    onPaymentAddressContinue(address) {
        return this.handleAddressChange(address, true);
    }

    handleAddressChange(address, submitToServer = false) {
        const {customer, saveAddress} = this.props;

        return saveAddress(customer, address, ADDRESS_TYPE_BILLING, submitToServer);
    }

    handleSavedAddressChoice(address) {
        this.setState({displayNewAddressEntry: false, loading: true});
        return this.handleAddressChange(address, true).then(() => {
            this.setState({loading: false});
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

    toggleDisplayNewAddressEntry(event) {
        const isChecked = event.target.checked;

        if (isChecked) {
            this.handleAddressChange({...this.props.shippingAddress, customer_address_id: null}).then(() => {
                this.setState({ displayNewAddressEntry: isChecked});
            });
        } else {
            this.setState({ displayNewAddressEntry: isChecked });
        }
    }

    onSelectCard(e) {
        //const customer = this.props.customer;

        //const selectedCard = customer.savedCards.filter(card => card.id === e.target.value);
        //const savedCardAddress = customer.addresses.filter(address => address.id = 1);

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
        const {customer, basket} = this.props;

        const canPlaceOrder = (this.state.selectedCardId || creditCardValidator.validate(this.state.creditCard)._all) &&
            addressValidator.validate(basket.billing_address)._all;

        const {differentBillingToShippingAddress} = this.state;

        const showAddressList = customer.hasOwnProperty('id') && customer.hasOwnProperty('addresses')
            && customer.addresses.length > 0;

        const addressList = <div>
            <AddressList
                addresses={this.props.customer.addresses}
                currentAddress={this.props.basket.billing_address}
                saveCustomerAddress={this.props.saveCustomerAddress}
                onChooseAddress={(address) => this.handleSavedAddressChoice(address) }
                loading={this.state.loading}
                listTitle="Select a stored billing address below"/>

            <Checkbox
                label="Use new address"
                name="new_customer_billing_address"
                checked={this.state.displayNewAddressEntry}
                onToggle={(e) => this.toggleDisplayNewAddressEntry(e)} />
        </div>;

        return (
            <div className="checkout-section checkout-section--checkout">
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

                    {!differentBillingToShippingAddress &&
                        <form className="form form--primary">
                            <fieldset>
                                <AddressDisplay addressLabel="Billing address"
                                                address={basket.billing_address}
                                                onAddressClick={(e) => this.onToggleDifferentBillingAddress(e)}/>
                            </fieldset>
                        </form>}

                    <div className="form__control full">
                        <div className="form__checkbox">
                            <label className="form__label">
                                <input type="checkbox"
                                       name="address"
                                       checked={differentBillingToShippingAddress}
                                       onChange={(e) => this.onToggleDifferentBillingAddress(e)}
                                       className=""
                                       value=""/>
                                <span className="form__label-text">Different billing address to above?</span>
                            </label>
                        </div>
                    </div>

                    {differentBillingToShippingAddress && (
                        <div>
                            {showAddressList && addressList}

                            {this.state.displayNewAddressEntry && <Address addressLabel="Billing address"  address={basket.billing_address}
                                 allowAddressSave={customer.hasOwnProperty('id')}
                                 handleAddressChange={(e) => this.handleAddressChange(e)}
                                 handleAddressSubmit={(e) => this.onPaymentAddressContinue(e)} />}
                        </div>
                    )}

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

export default Payment;