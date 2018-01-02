import React, {Component} from 'react';
import * as basketActions from "../../actions/basketActions";
import {bindActionCreators} from "redux";
import {connect} from "react-redux";

class GiftCardList extends Component {
    onSubmit(e, cardNumber){
        e.preventDefault();

        this.props.basketActions.removeGiftCard(cardNumber)
            .then(() => {})
            .catch(() => {});
    }

    render() {
        const giftCards = this.props.basket.gift_cards;

        if (giftCards.length === 0) {
            return null;
        }

        const giftCardList = this.props.basket.gift_cards.map(giftCard =>
            <div key={giftCard.card_number} className="checkout-basket__giftcard applied">
                <div className="checkout-basket__giftcard-label">
                    Gift Card ({giftCard.card_number})
                    {!this.props.disableRemoveCards && <a href="#" onClick={(e) => this.onSubmit(e, giftCard.card_number)}>Remove</a>}
                </div>
                <div className="checkout-basket__giftcard-price">{giftCard.amount_with_symbol}</div>
            </div>
        );

        return <div>{giftCardList}</div>;
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

export default connect(mapStateToProps, mapDispatchToProps)(GiftCardList);