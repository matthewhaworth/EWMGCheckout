
if(!APA || !APA.renderButtonWidget){
    APA = {};
}

APA.renderButtonWidget = function(tooltipContent){

    if(OffAmazonPayments == undefined){
        return false;
    }

    if (APA.urls.login != null) {
        $$(APA.layers.payButtons).each(function(button) {
            var buttonParams = {
                type: button.buttonType || APA.design.payButton.type || 'PwA',
                size: button.buttonSize || APA.design.payButton.size,
                color: button.buttonColor || APA.design.payButton.color,
                authorization: function() {
                    amazon.Login.authorize({
                        scope: 'profile payments:widget payments:shipping_address payments:billing_address',
                        popup: APA.popup
                    }, APA.urls.pay);
                },
                onError: APA.amazonErrorCallback
            };
            if (APA.language) {
                buttonParams.language = APA.language;
            }
            new OffAmazonPayments.Button(button.identify(), APA.sellerId, buttonParams);
        });
        $$(APA.layers.loginButtons).each(function(button) {
            var buttonParams = {
                type: button.buttonType || APA.design.loginButton.type || 'LwA',
                size: button.buttonSize || APA.design.loginButton.size,
                color: button.buttonColor || APA.design.loginButton.color,
                authorization: function() {
                    amazon.Login.authorize({
                        scope: 'profile payments:widget payments:shipping_address payments:billing_address',
                        popup: APA.popup
                    }, APA.urls.login);
                },
                onError: APA.amazonErrorCallback
            };
            if (APA.language) {
                buttonParams.language = APA.language;
            }
            new OffAmazonPayments.Button(button.identify(), APA.sellerId, buttonParams);
        });
    } else {
        $$(APA.layers.payButtons).each(function(button) {
            new OffAmazonPayments.Widgets.Button({
                sellerId: APA.sellerId,
                useAmazonAddressBook: !APA.virtual,
                onSignIn: APA.signInCallback,
                onError: APA.amazonErrorCallback
            }).bind(button.identify());
        });
    }

    // add tooltips
    if (tooltipContent && typeof Tooltip != 'undefined' && !APA.isMobileDevice()) {
        var tooltipButtons = $$(APA.layers.payButtons + ',' + APA.layers.loginButtons).findAll(function(button) {
            return button.hasClassName('with-tooltip');
        });
        if (tooltipButtons.length) {
            var tooltip = document.createElement('div');
            tooltip.setAttribute('id', 'pay-with-amazon-tooltip');
            tooltip.addClassName('pay-with-amazon-tooltip');
            tooltip.setStyle({display: 'none', zIndex: 10});
            tooltip.update(tooltipContent);
            document.body.appendChild(tooltip);
            tooltipButtons.each(function(button) {
                new Tooltip(button, tooltip);
            });
        }
    }
};

