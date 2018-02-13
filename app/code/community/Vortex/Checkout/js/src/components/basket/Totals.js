import React, {Component} from 'react';
import isEmpty from 'validator/lib/isEmpty';
import {config} from "../../config";
import ListTotals from "./ListTotals";

class Totals extends Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            discount: {
                code: this.props.basket.discount_code,
                saving: false,
                removing: false,
                errors: [],
                forceValidate: false
            },
        };
    }

    onDiscountChange(event) {
        event.preventDefault();
        this.setState({
            discount: {
                ...this.state.discount,
                code: event.target.value,
                errors:[],
                forceValidate: false
            }
        });
    }

    onDiscountApply(event) {
        event.preventDefault();

        if(!this.state.discount.code || isEmpty(this.state.discount.code)){
            this.setState({
                discount: {
                    errors: ['Discount code couldn\'t be empty'],
                    forceValidate: true
                }
            });
            return false;
        }

        this.setState({
            discount: {
                ...this.state.discount,
                saving: true
            }
        });

        this.props.addDiscountCode(this.state.discount.code)
            .then(response => {
                this.setState({
                    discount: {
                        ...this.state.discount,
                        saving: false
                    }
                });
            })
            .catch(() => {
                this.setState({ discount: {
                    ...this.state.discount,
                    saving: false
                }})
            });
    }

    onDiscountRemove(event) {
        event.preventDefault();

        this.setState({
            discount:{
                ...this.state.discount,
                removing: true,
                forceValidate: false,
                errors:[]
            }
        });

        this.props.removeDiscountCode()
            .then(response => {
                this.setState({
                    discount: {
                        ...this.state.discount,
                        removing: false,
                    }
                });
            })
            .catch(() => {
                this.setState({
                    discount: {
                        ...this.state.discount,
                        removing: false
                    }
                })
            });
    }

    render() {
        const isDiscountApplied = this.props.basket.discount_code && this.props.basket.discount_code !== '';

        return <ListTotals basket={this.props.basket}
                           displayType={this.props.displayType}
                           disableRemoveCards={this.props.disableRemoveCards}
                           isDiscountApplied={isDiscountApplied}
                           discountCode={this.state.discount}
                           includeGiftCard={this.props.includeGiftCard}
                           onDiscountRemove={(e) => this.onDiscountRemove(e)}
                           onDiscountChange={(e) => this.onDiscountChange(e)}
                           onDiscountApply={(e) => this.onDiscountApply(e)}
                           onContinue={() => this.props.onContinue()}
                           hideRemoveDiscount={this.props.hideRemoveDiscount}
                           hideDelivery={this.props.hideDelivery}
                           isAmazonPayEnabled={config.isAmazonPayEnabled}
                           isPaypalEnabled={config.isPaypalEnabled}/>;
    }
}

export default (Totals);