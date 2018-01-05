import React, {Component} from 'react';
import Transition from 'react-transition-group/Transition';
import TransitionGroup from 'react-transition-group/TransitionGroup';
import '../../checkout.css';
import {url} from "../../config";

export default class ListItems extends Component {

    renderConfiguredOptions(item) {

        return item.configured_options.map((configuredOption, i) =>
            <li key={configuredOption.label} className="checkout-basket__item-attribute">
                <span className="checkout-basket__item-attribute-label">{configuredOption.label}: {configuredOption.value}</span>
                {this.props.onQuantityChange && <a href={url("/checkout/cart/configure/id/" + item.item_id)} className="checkout-basket__item-link">
                    Change
                </a>}
            </li>
        );
    }

    hasSpecialPrice(item){
        return parseFloat(item.price) < parseFloat(item.original_price);
    }

    render() {
        const {items, itemsSaving, showFinalPrice, onQuantityChange, itemQuantities, onQuantityKeyPress, onQuantityKeyChange} = this.props;

        if(!items || !items.length) return null;

        const itemsList = items.map((item, i) =>
            <Transition timeout={500} in={true} key={item.item_id}>
                {(status) => (
                    <div className={'checkout-basket__item checkout-basket__item--' + (status)} >
                        <div className="checkout-basket__img">
                            <img src={item.image} alt={item.name} />
                        </div>
                        <div className="checkout-basket__item-info">
                            {item.qty_status.error &&
                                <span className="checkout-basket__item-stock checkout-basket__item-stock--outofstock">
                                    {item.qty_status.error}
                                </span>
                            }
                            <div className="checkout-basket__item-title">
                                <a href={item.product_url}>{item.name}</a>
                            </div>
                            <div className="checkout-basket__item-price">
                                <div className="checkout-basket__price-box">
                                    {!this.hasSpecialPrice(item) && <span className="regular-price">
                                        <span className="label">Price</span>
                                        <span className="price">{item.price_currency}</span>
                                    </span>}
                                    {this.hasSpecialPrice(item) && <span className="old-price">
                                        <span className="label">Old price</span>
                                        <span className="price">{item.original_price_currency}</span>
                                    </span>}
                                    {this.hasSpecialPrice(item) && <span className="special-price">
                                        <span className="label">Special price</span>
                                        <span className="price">{item.price_currency}</span>
                                    </span>}
                                    {showFinalPrice && <span className="final-price">
                                        <span className="label">Subtotal price</span>
                                        <span className="price">(x{item.qty} = {item.subtotal_price})</span>
                                    </span>}
                                </div>
                            </div>
                            <ul className="checkout-basket__item-attributes">
                                {item.configured_options && item.configured_options.length > 0 && this.renderConfiguredOptions(item)}
                                {item.sku && <li className="checkout-basket__item-attribute">
                                    <span className="checkout-basket__item-attribute-label">Ref: {item.sku}</span>
                                </li>}
                            </ul>
                            {this.props.onQuantityChange && <div className="checkout-basket__qty">
                                <form className="form form--qty">
                                    <div className="checkout-basket__qty-button">
                                        <button type="button"
                                                className="button button--icon button--icon-minus"
                                                onClick={(event) => onQuantityChange(item, itemQuantities[item.item_id] - 1, event)}
                                                disabled={item.qty === 1} />
                                    </div>
                                    <div className="form__control">
                                        <div className="form__input">
                                            <input type="text" min="1"
                                                   value={itemQuantities[item.item_id]}
                                                   onChange={(e) => onQuantityKeyChange(item, e)}
                                                   onKeyPress={(e) => onQuantityKeyPress(item, e)} />
                                            {/*onBlur={(e) => onQuantityChange(item, itemQuantities[item.item_id], e)} />*/}
                                        </div>
                                    </div>

                                    <div className="checkout-basket__qty-button">
                                        <button type="button"
                                                className="button button--icon button--icon-plus"
                                                onClick={(event) => onQuantityChange(item, itemQuantities[item.item_id] + 1, event)} />
                                    </div>
                                </form>
                            </div>}
                        </div>
                        {this.props.onQuantityChange && <button onClick={(event) => onQuantityChange(item, 0, event)}
                                className="checkout-basket__item-remove button--icon button--icon-close" />}

                        <div className={'checkout-loader ' + (itemsSaving.includes(item.item_id) ? 'active' : '')}>
                            <div className="checkout-loader__spinner"><span /></div>
                        </div>
                    </div>
                )}
            </Transition>
        );

        return (
            <TransitionGroup component="div" className="checkout-basket__items">
                {itemsList}
            </TransitionGroup>
        );

    }
}