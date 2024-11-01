define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader',
        'Midtrans_Snap/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'Magento_Ui/js/model/messageList'
    ],
    function (Component, $, quote, urlBuilder, storage, errorProcessor, customer, fullScreenLoader, setPaymentMethodAction, additionalValidators, url, messageList) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Midtrans_Snap/payment/offline'
            },
            redirectAfterPlaceOrder: false,
            /** Open VT-Snap */
            afterPlaceOrder: function () {

                var production = window.checkoutConfig.payment.offline.production;
                var client_key = window.checkoutConfig.payment.offline.clientkey;
                var merchant_id = window.checkoutConfig.payment.offline.merchantid;
                var mixpanel_key = window.checkoutConfig.payment.offline.mixpanelkey;
                var magento_version = window.checkoutConfig.payment.offline.magentoversion;
                var plugin_version = window.checkoutConfig.payment.offline.pluginversion;
                var enable_redirect = window.checkoutConfig.payment.offline.enableredirect;


                if (production) {
                    var js = "https://app.midtrans.com/snap/snap.js";
                } else {
                    var js = "https://app.sandbox.midtrans.com/snap/snap.js";
                }

                !function(e,p){if(!p.__SV){var r,l,t=window;try{var n,o,a,i=t.location,c=i.hash;n=function(e,t){return(o=e.match(RegExp(t+"=([^&]*)")))?o[1]:null},c&&n(c,"state")&&("mpeditor"===(a=JSON.parse(decodeURIComponent(n(c,"state")))).action&&(t.sessionStorage.setItem("_mpcehash",c),history.replaceState(a.desiredHash||"",e.title,i.pathname+i.search)))}catch(e){}(window.mixpanel=p)._i=[],p.init=function(e,t,n){function o(e,t){var n=t.split(".");2==n.length&&(e=e[n[0]],t=n[1]),e[t]=function(){e.push([t].concat(Array.prototype.slice.call(arguments,0)))}}var a=p;for(void 0!==n?a=p[n]=[]:n="mixpanel",a.people=a.people||[],a.toString=function(e){var t="mixpanel";return"mixpanel"!==n&&(t+="."+n),e||(t+=" (stub)"),t},a.people.toString=function(){return a.toString(1)+".people (stub)"},r="disable time_event track track_pageview track_links track_forms track_with_groups add_group set_group remove_group register register_once alias unregister identify name_tag set_config reset opt_in_tracking opt_out_tracking has_opted_in_tracking has_opted_out_tracking clear_opt_in_out_tracking people.set people.set_once people.unset people.increment people.append people.union people.track_charge people.clear_charges people.delete_user people.remove".split(" "),l=0;l<r.length;l++)o(a,r[l]);var i="set set_once union unset remove delete".split(" ");a.get_group=function(){function e(e){t[e]=function(){call2_args=arguments,call2=[e].concat(Array.prototype.slice.call(call2_args,0)),a.push([n,call2])}}for(var t={},n=["get_group"].concat(Array.prototype.slice.call(arguments,0)),o=0;o<i.length;o++)e(i[o]);return t},p._i.push([e,t,n])},p.__SV=1.2,(t=e.createElement("script")).type="text/javascript",t.async=!0,t.src="undefined"!=typeof MIXPANEL_CUSTOM_LIB_URL?MIXPANEL_CUSTOM_LIB_URL:"file:"===e.location.protocol&&"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//)?"https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js":"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js",(n=e.getElementsByTagName("script")[0]).parentNode.insertBefore(t,n)}}(document,window.mixpanel||[]),mixpanel.init(mixpanel_key);

                var scriptTag = document.createElement('script');
                scriptTag.src = js;
                scriptTag.setAttribute('data-client-key', client_key);
                document.body.appendChild(scriptTag);
                $.ajax({
                    type: 'post',
                    url: url.build('snap/payment/redirect'),
                    cache: false,
                    success: function (data) {
                        function trackResult(data, merchant_id, plugin_name, status, result) {
                            var eventNames = {
                                pay: 'pg-pay',
                                success: 'pg-success',
                                pending: 'pg-pending',
                                error: 'pg-error',
                                close: 'pg-close'
                            };
                            mixpanel.track(
                                eventNames[status], {
                                    merchant_id: merchant_id,
                                    cms_name: 'Magento',
                                    cms_version: magento_version,
                                    plugin_name: plugin_name,
                                    plugin_version: plugin_version,
                                    snap_token: data,
                                    payment_type: result ? result.payment_type : null,
                                    order_id: result ? result.order_id : null,
                                    status_code: result ? result.status_code : null,
                                    gross_amount: result && result.gross_amount ? Number(result.gross_amount) : null,
                                }
                            );
                        }

                        if (!enable_redirect) {
                            console.log('Pay with Snap : ' + data);
                            trackResult(data, merchant_id, 'Fullpayment', 'pay', null);

                            var retryCount = 0;
                            var snapExecuted = false;
                            var intervalFunction = 0;
                            console.log(data);

                            function execSnapCont() {
                                intervalFunction = setInterval(function () {
                                    try {
                                        snap.pay(data,
                                            {
                                                skipOrderSummary: true,
                                                showOrderId: true,
                                                onSuccess: function (result) {
                                                    trackResult(data, merchant_id, 'Fullpayment Snap', 'success', result);
                                                    messageList.addSuccessMessage({
                                                        message: result.status_message
                                                    });
                                                    window.location.replace(url.build('snap/index/finish'));
                                                    console.log(result.status_message);
                                                },
                                                onPending: function (result) {
                                                    trackResult(data, merchant_id, 'Fullpayment Snap', 'pending', result);
                                                    messageList.addSuccessMessage({
                                                        message: result.status_message
                                                    });
                                                    window.location.replace(url.build('snap/index/finish'));
                                                    console.log(url.build('snap/index/finish'))
                                                },
                                                onError: function (result) {
                                                    trackResult(data, merchant_id, 'Fullpayment Snap', 'error', result);
                                                    messageList.addErrorMessage({
                                                        message: result.status_message
                                                    });
                                                    window.location.replace(url.build('snap/index/close'));
                                                    console.log(result.status_message);
                                                },
                                                onClose: function () {
                                                    console.log("Payment page close")
                                                    trackResult(data, merchant_id, 'Fullpayment Snap', 'close');
                                                    window.location.replace(url.build('snap/index/close'));
                                                }
                                            });
                                        var snapExecuted = true;
                                    } catch (e) {
                                        retryCount++;
                                        if (retryCount >= 10) {
                                            messageList.addErrorMessage({
                                                message: 'Trying to load snap, this might take longer'
                                            });
                                        }
                                        console.log(e);
                                        console.log("Snap not ready yet... Retrying in 1000ms!");
                                    } finally {
                                        if (snapExecuted) {
                                            clearInterval(intervalFunction);
                                            // record 'pay' event to Mixpanel
                                            trackResult(data, merchant_id, 'Fullpayment Snap PopUp', 'pay', null);
                                        }
                                    }
                                }, 1000);
                            }; //end of execsnapcont
                            execSnapCont();
                        } else {
                            window.location = data;
                            console.log('Payment using snap redirect, with URL: ' + data);
                        }
                    }//end of ajax success
                });
            }
        });
    }
);
