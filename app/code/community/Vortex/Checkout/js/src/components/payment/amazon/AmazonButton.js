import React, {PureComponent} from 'react';
import {config} from "../../../config";
import {guid} from '../../../helpers/utility';

class AmazonButton extends PureComponent {

    widgetHtmlId = null;

    constructor(props, context) {
        super(props, context);

        this.widgetHtmlId = config.amazon.buttonWidgetHtmlIdPrefix + guid();
    }

    componentDidMount() {
        let amazonButtonTooltip = `<img src="${config.amazon.tooltip}" alt="Pay with Amazon"/><strong>Amazon Payments</strong> helps you shop quickly, safely and securely.You can pay on our website without re-entering your payment and address details.`;

        if(window.OffAmazonPayments){

            window.APA.setup(config.amazon.sellerId, {
                live: config.amazon.isLive,
                popup: config.amazon.isPopup,
                virtual: false,
                language: config.amazon.language,
                layers: {
                    payButtons: '#' + this.widgetHtmlId,
                },
                urls: config.amazon.urls,
                design: config.amazon.designParams
            }).renderButtonWidget(amazonButtonTooltip);

        }
    }

    onClick(event){
        event.preventDefault();

        if(!this.button.childElements() || !this.button.childElements().length){
            return false;
        }

        this.button.childElements().map((element, index) => {
            if(element.tagName.toLowerCase() === "img"){
                element.click();
            }
        });

    }

    render() {
        return (
            <button type="button" ref={(button) => {this.button = button}} onClick={(e) => this.onClick(e)} className={ 'button button--payment button--payment-amazon ' + config.amazon.buttonWidgetHtmlIdPrefix + ' with-tooltip'} id={this.widgetHtmlId} >
                <span>
                    <em className="path1"/>
                    <em className="path2"/>
                </span>
            </button>
        );
    }
};

export default AmazonButton;