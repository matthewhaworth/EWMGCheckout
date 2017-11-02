/**
 * This maps braintree card detected card types (https://github.com/braintree/credit-card-type#code) to
 * SessionDigital_Worldpay payment methods.
 */
export const cardMap = {
    'visa': 'VISA-SSL',
    'master-card': 'ECMC-SSL',
    'american-express': 'AMEX-SSL',
    'diners-club': 'DINERS-SSL',
    'discover': 'DISCOVER-SSL',
    'jcb': 'JCB-SSL',
    'maestro': 'MAESTRO-SSL'
};