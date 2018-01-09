import React, {Component} from 'react';
import ListMethods from "./ListMethods";
import CmsApi from "../../api/CmsApi";

class ShippingMethod extends Component {

    constructor(props, context) {
        super(props, context);
        this.state = {
            loading: false,
            deliveryContent: '',
            deliveryActive: false
        };
    }

    componentWillMount(){
        CmsApi.loadCmsContent('delivery').then((resp) => {
            this.setState({deliveryContent: resp.html});
        }).catch(() => {
            this.setState({deliveryContent: 'Delivery content on network error'});
        });

    }

    toggleDelivery(e){
        e.preventDefault();
        this.setState({deliveryActive: !this.state.deliveryActive})
    }


    onSetShippingMethod(method){
        this.setState({ loading: true }, () => {
            this.props.setShippingMethod(method)
                .then(() => {
                    this.setState({ loading: false })
                })
                .catch((err) => {
                    this.setState({ loading: false })
                })
        });
    }

    render() {
        const basket = this.props.basket;
        const hasShippingMethods = basket.available_shipping_methods.length > 0;
        const isLoading = this.state.loading;

        const NoMethods = () => <p>Please enter your delivery address to see available delivery methods.</p>;

        const ShippingMethods = hasShippingMethods ? ListMethods : NoMethods;

        return (
            <div>
                <div className="form__title form__title--secondary">Delivery Options</div>
                <ShippingMethods shippingMethods={basket.available_shipping_methods}
                                 selectedShippingMethod={basket.shipping_method}
                                 setShippingMethod={(method) => this.onSetShippingMethod(method)}
                                 isLoading={isLoading}
                                 deliveryContent={this.state.deliveryContent}
                                 deliveryActive={this.state.deliveryActive}
                                 toggleDelivery={(e) => this.toggleDelivery(e)}
                                 basketSubtotal={basket.subtotal_incl_discount}/>
            </div>
        );
    }
}

export default ShippingMethod;