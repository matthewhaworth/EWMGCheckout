import React, {Component} from 'react';
import CmsApi from "../../api/CmsApi";
import RawHtml from "./RawHtml";

class DeliveryModal extends Component{

    constructor(props, context) {
        super(props, context);
        this.state = {
            content: '',
            active: false
        };
    }

    componentWillMount(){
        CmsApi.loadCmsContent('delivery').then((resp) => {
            this.setState({content: resp.html});
        }).catch(() => {
            this.setState({content: 'Delivery content on network error'});
        });

    }

    toggle(e){
        e.preventDefault();
        this.setState({active: !this.state.active})
    }


    render() {
        if (this.state.content === '') return null;

        return(
            <div>
                <a className="checkout-modal__trigger checkout-modal__trigger--info" href="#" onClick={(e) => this.toggle(e)}>More delivery information</a>

                { this.state.content !== '' && <div className={'checkout-modal ' + (this.state.active ? 'active' : '')}>
                    <div onClick={(e) => this.toggle(e)} className="checkout-loader checkout-loader--large checkout-loader--dark checkout-loader--overlay">
                        <div className="checkout-loader__spinner"><span /></div>
                    </div>
                    <div className="checkout-modal__container">
                        <a className="checkout-modal__close button--icon-close"
                           onClick={(e) => this.toggle(e)} />

                        <div className="checkout-delivery">
                            <div className="checkout-delivery__container">
                                <RawHtml className="cms" output={this.state.content}/>
                            </div>
                        </div>
                    </div>
                </div>}

            </div>
        );
    }

};

export default DeliveryModal;