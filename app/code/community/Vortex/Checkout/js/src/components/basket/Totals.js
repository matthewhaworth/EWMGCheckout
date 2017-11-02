import React, {Component} from 'react';
import ListTotals from "./ListTotals";

class Totals extends Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            discount: {
                code: this.props.basket.discount_code,
                saving: false,
                removing: false,
                applied: this.props.basket.discount_code && this.props.basket.discount_code !== ''
            },
        };
    }

    onDiscountChange(event) {
        event.preventDefault();
        this.setState({
            discount: {
                ...this.state.discount,
                code: event.target.value
            }
        });
    }

    onDiscountApply(event) {
        event.preventDefault();
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
                        saving: false,
                        applied: response && response.discount_code && response.discount_code !== ''
                    }
                });
            })
            .catch(() => {
                this.setState({ discount: {
                    saving: false
                }})
            });
    }

    onDiscountRemove(event) {
        event.preventDefault();

        this.setState({
            discount:{
                ...this.state.discount,
                removing: true
            }
        });

        this.props.removeDiscountCode()
            .then(response => {
                this.setState({
                    discount: {
                        ...this.state.discount,
                        removing: false,
                        applied: false
                    }
                });
            })
            .catch(() => {
                this.setState({
                    discount: {
                        removing: false
                    }
                })
            });
    }

    render() {
        return <ListTotals basket={this.props.basket}
                           displayType={this.props.displayType}
                           discountCode={this.state.discount}
                           includeGiftCard={this.props.includeGiftCard}
                           onDiscountRemove={(e) => this.onDiscountRemove(e)}
                           onDiscountChange={(e) => this.onDiscountChange(e)}
                           onDiscountApply={(e) => this.onDiscountApply(e)}
                           onContinue={() => this.props.onContinue()}
                           hideDelivery={this.props.hideDelivery}/>;
    }
}

export default (Totals);