import React from 'react';

const CreditCardList = ({creditCards, selectedCardId, onSelectCard, toggleCreditCardFormVisible}) => {
    const cardList = creditCards.map((card) =>
       <div className="form__radio form__radio--card" key={card.id}>
            <label className="form__label">
                <input type="radio"
                       name="customer_card_list"
                       onChange={(e) => onSelectCard(e)}
                       value={card.id}
                       checked={card.id === selectedCardId}/>
                    <span className="form__label-text">
                        <span className="form__label-card">
                            {(card.type.toLowerCase().replace(/\s/g, '') === "visa") && <span className="cardtype cardtype--visa"></span>}
                            {(card.type.toLowerCase().replace(/\s/g, '') === "mastercard") && <span className="cardtype cardtype--mastercard">
                                <em className="path1"/>
                                <em className="path2"/>
                                <em className="path3"/>
                                <em className="path4"/>
                            </span>}
                            {(card.type.toLowerCase().replace(/\s/g, '') === "americanexpress") &&<span className="cardtype cardtype--amex"></span>}
                            <span className="strong">{card.type}</span> ending in {card.last_four}
                        </span>
                    </span>
            </label>
        </div>
    );

    return (
        <form className="form form--cardslist">
            <fieldset>
                <div className="form__control form__control--nomargin full">
                    <p>Select a stored card below</p>
                    {cardList}
                </div>
                <div className="form__control form__control--actions">
                    <button onClick={(e) => toggleCreditCardFormVisible(e)} className="button button--small button--fifth">
                        <span>Add new card</span>
                    </button>
                </div>
            </fieldset>
        </form>
    );
};

export default CreditCardList;