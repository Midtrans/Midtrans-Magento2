define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'snap',
                component: 'Midtrans_Snap/js/view/payment/method-renderer/snap-method'
            },
            {
                type: 'specific',
                component: 'Midtrans_Snap/js/view/payment/method-renderer/specific-method'
            },
            {
                type: 'installment',
                component: 'Midtrans_Snap/js/view/payment/method-renderer/installment-method'
            },
            {
                type: 'offline',
                component: 'Midtrans_Snap/js/view/payment/method-renderer/offline-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
