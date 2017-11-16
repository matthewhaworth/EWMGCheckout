export const config = window.ewmgCheckoutConfig || {
    postcodeAnywhere: {
        key: 'ZM84-JH59-HD92-TG97',
        geocodeKey: 'MH39-HB51-RW69-KE13',
        geocodeCountry: 'GB',
        geocodeEndpoint: 'https://services.postcodeanywhere.co.uk/Geocoding/International/Geocode/v1.10/json3ex.ws',
        findEndpoint: 'https://services.postcodeanywhere.co.uk/Capture/Interactive/Find/v1.00/json3ex.ws',
        retrieveEndpoint: 'https://services.postcodeanywhere.co.uk/Capture/Interactive/Retrieve/v1.00/json3ex.ws'
    },
    apiEndpoint: 'http://peacocks.co.uk.dev/fast-checkout/v1/api',
    magentoUrl: 'http://peacocks.co.uk.dev',
    worldpay: {
        publicKey: '1#10001#c16ac84a07eabf71c26e58ea4995f04fa284ea637413826599335a0c01760fe85b2938df4eded18e94c9f95eeeba4a1073816a77ee3b9bdd575e23e7c84d30467f8e0d63c3d54f2782433842ef9e70d656937a001e9b9c6528dd2296df21b500b12323a409af15b2390e5df933b1984c953c667ade28d46cc045d695c07c88376fa194629a3752c0490916e721c32f906226532f3973a7b9c7ef2cfc24859d71571e4d416352413f5c2703ec980374e620e72600de80d07acb5b2e209373bf84651648e4d6ade4dd31fec245c1e92d1f40b14c8475b6bebfc8aac4b7bc2af8a37276261d833887fa9d1e0c743ba9eaaf6cdbb8065d0ce7b65fc44db88b983cc5'
    },
    checkoutBasePath: '/',
    amazon: {
        buttonWidgetUrl: 'https://payments-sandbox.amazon.co.uk/gp/widgets/button?sellerId=A2RTMOBL1OCWKV&size=large&color=tan',
        buttonWidgetHtmlIdPrefix: 'payButtonWidget',
        sellerId: 'A2RTMOBL1OCWKV',
        urls: '{"checkout":"http://peacocks.co.uk.dev/amazonpayments/advanced_checkout/"}',
        tooltip: 'http://peacocks.co.uk.dev/skin/frontend/base/default/creativestyle/images/logo_a-glyph_1x.png',
        isLive: false,
        isPopup: true,
        language: 'en-GB',
        designParams: {},
        buttonType: '',
        buttonSize: '',
        buttonColor: ''
    },
    formKeyRequired: false,
    formKey: null,
    countryList: [
        {"value":"","label":true},
        {"value":"AU","label":"Australia"},
        {"value":"AT","label":"Austria"},
        {"value":"BE","label":"Belgium"},
        {"value":"CA","label":"Canada"},
        {"value":"DK","label":"Denmark"},
        {"value":"FI","label":"Finland"},
        {"value":"FR","label":"France"},
        {"value":"DE","label":"Germany"},
        {"value":"GG","label":"Guernsey"},
        {"value":"HU","label":"Hungary"},
        {"value":"IE","label":"Ireland"},
        {"value":"IM","label":"Isle of Man"},
        {"value":"IT","label":"Italy"},
        {"value":"JE","label":"Jersey"},
        {"value":"NL","label":"Netherlands"},
        {"value":"NZ","label":"New Zealand"},
        {"value":"NO","label":"Norway"},
        {"value":"PL","label":"Poland"},
        {"value":"PT","label":"Portugal"},
        {"value":"ES","label":"Spain"},
        {"value":"SE","label":"Sweden"},
        {"value":"CH","label":"Switzerland"},
        {"value":"GB","label":"United Kingdom"},
        {"value":"US","label":"United States"}
    ],
    countryRegionList: {}
};

export const url = (url) => {
    return config.magentoUrl + url;
};
