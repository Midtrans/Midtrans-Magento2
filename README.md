[![Latest Stable Version](https://poser.pugx.org/midtrans/snap/v)](//packagist.org/packages/midtrans/snap) 
[![Monthly Downloads](https://poser.pugx.org/midtrans/snap/d/monthly)](//packagist.org/packages/midtrans/snap)
[![Total Downloads](https://poser.pugx.org/midtrans/snap/downloads)](//packagist.org/packages/midtrans/snap) 
[![License](https://poser.pugx.org/midtrans/snap/license)](//packagist.org/packages/midtrans/snap)

Midtrans ❤️ Magento! Midtrans strive to make payments simple for both the merchant and customers. With Midtrans Magento extension/plugin, your Magento Store can easily start accepting payments via Midtrans.
## Live Demo
Want to see Midtrans Magento payment plugins in action? We have some demo web-stores for Magento that you can use to try the payment journey directly, click the link below.
* [Midtrans CMS Demo Store](https://docs.midtrans.com/en/snap/with-plugins?id=midtrans-payment-plugin-live-demonstration)

## Requirements:
*   An online store with Magento infrastructure. This plugin tested with Magento v2.1.0, v2.2.0, v2.3.1, v2.4.1
*   PHP v5.6 or greater.
*   MySQL v5.7 or greater.
*   Midtrans plugin for Magento v2.x [ [Github](https://github.com/Midtrans/Midtrans-Magento2) | [Zip](https://github.com/Midtrans/Midtrans-Magento2/archive/master.zip) ]
*   This plugin supports Magento2 version 2.1.0 - 2.4.1 and higher.


# How to install the plugins
## Install Midtrans Snap plugins through Composer
Before you begin to install through the composer, you need Magento marketplace account and make sure that you have installed Composer. In your terminal, go to the Magento folder and run the following commands:
1. Install the plugins: `composer require midtrans/snap`
2. Enable the plugin:  `bin/magento module:enable Midtrans_Snap`
3. Execute upgrade script : `bin/magento setup:upgrade`
4. Clean cache storage :  `bin/magento cache:clean`
5. Check the module status:  `bin/magento module:status Midtrans_Snap`

>Note: If you do have a previous version installed and upgrade the plugins to the latest version. After upgrade our plugins, You need to run `bin/magento setup:upgrade --keep-generated`, `bin/magento setup:static-content:deploy` and clean cache `bin/magento cachce:clean`.


## Install Midtrans Snap plugins through Magento marketplace 
You can install Midtrans Snap plugins through Magento Marketplace. Please, visit Midtrans on [Magento Marketplace](https://marketplace.magento.com/midtrans-snap.html) and follow step-by-step installation instructions from the [Official Magento extension docs](https://devdocs.magento.com/extensions/install)

## Install Midtrans Snap plugins from GitHub project

With these steps, you can custom/modify our Magento plugins to handle the business model that you want

1. Download and extract the plugin you have previously downloaded from GitHub and rename the folder as Snap.
2. Make a directory structure like this: 
![](https://user-images.githubusercontent.com/21098575/78326383-723e6700-75a4-11ea-97ae-44885443008c.png "image_directory_structure")
3. Locate the root Magento directory of your shop via FTP connection.
4. Copy the app folders into the Magento root folder.
5. Run this command on terminal

    `bin/magento module:enable Midtrans_Snap`
    
    `bin/magento setup:upgrade`
    
    `bin/magento cache:clean`
    
    `bin/magento module:status Midtrans_Snap`


# Plugin Usage Instruction
## Basic Plugins Configuration

Before you begin, make sure that you have successfully installed and enabled Midtrans Snap plugins.
Configure the Midtrans plugin in your Magento admin panel: 

1. Log in to your Magento admin panel. 
2. In the left navigation bar, go to **Stores(1)** -> **Configuration(2)**. 
3. In the menu, go to **Sales(3)** -> **Payment Methods(4)**

![](https://user-images.githubusercontent.com/21098575/78235369-c133c000-7502-11ea-99af-d28144d5f2ca.png "image_mag_config")


4. In the Midtrans - Accept Online Payment section, click Basic Settings and fill out the following fields:

| Field                   | Description									                               |
|-------------------------| ---------------------------------------------------------------------------|
| Is Production           | Select whether you want to use a sandbox or production mode\.			|
| Merchant ID             | Unique id of your Midtrans account for which the payments will be processed\.|
| Sandbox \- ClientKey    | Used as an API key to be used for authorization sandbox environment on frontend API request/configuration\. So it is safe to put in your HTML / client code publicly\.    |
| Sandbox \- ServerKey    | Used as an API key to be used for authorization sandbox environment while calling Midtrans API from the backend\. So keep it stored confidentially\.                      |
| Production \- ClientKey | Used as an API key to be used for authorization production environment on frontend API request/configuration\. So it is safe to put in your HTML / client code publicly\. |
| Production \- ServerKey | Used as an API key to be used for authorization production environments while calling Midtrans API from the backend\. So keep it stored confidentially                    |
| Enable Snap redirect    | Change to Snap redirect mode, the default value is No\.			 |


>Note: Access Keys are unique for every merchant. Server Key is secret, please always keep Server Key confidential.

## Log options

The plugins will store log file in directory `/var/log/midtrans`. The default value is on for request, notification and error log. Except Throw Exception, is off by default.
![](https://user-images.githubusercontent.com/21098575/78235349-b9741b80-7502-11ea-9139-f5119193bc20.png "image_mag_config")


## Config Plugins Payment Integration

In the Midtrans Magento plugins we have 4 option to use Snap model payment method, with the following description:


1. **Snap payment integration**
    
    This is the default Snap for Midtrans Magento plugins, Snap payment will be auto-enabled when installing the Midtrans plugins. Midtrans will show the available payment method on the Snap payment screen.

2. **Specific Payment integration | Optional** 
    
    Enabling this will display additional payment options to customer, for specific payment that are specified in the "Allowed Payment Method" field, Midtrans Snap will show only the listed payment method on the Snap screen.

3. **Online Installment payment integration | Optional**

    Enabling this will display additional payment options to customer, for online installment payment where the Card Issuer and Acquiring Bank is the same entity (e.g: BNI Card and BNI Acquiring bank).

4. **Offline Installment payment integration | Optional**

    Enabling this will display additional payment options to customer, for offline Installment where the Card Issuer and Acquiring Bank don't have to be same entity (e.g: BNI Card and Mandiri Acquiring Bank)


>Note: You can use different Midtrans Account for every Snap model payment method, should configure the access-key in Optional section `“Use different Midtrans account”`. If the optional access-key is empty, the plugins will automatically use access key on Basic Settings.

>INFO: 
><li> The built-in BCA Klikpay landing page for now will only use server key from basic settings of Snap payment integration.</li>
><li> Multishipping only support on version Midtrans Magento Plugins v2.5.3 or greater and not support in offline installment payment</li>
In case you need to customize configuration these field are configurable, and described as follows:

| Field                  | Description            
|------------------------|---------------------------------------------------------------------------|
| Enable                 | Payment snap section enable                                                                        
| Title                  | The title for the payment method in the checkout page
| Custom Expiry          | This field will allow you to set a custom duration on how long the transaction is available to be paid\.                                                                                                        
| Allowed Payment Method | Customize allowed payment method, separate payment method code with a comma\. e\.g: bank\_transfer,credit\_card\. Leave it default if you are not sure\.                                                        
| Acquiring Bank         | You can specify which Acquiring Bank they prefer to use for a specific transaction\. The transaction fund will be routed to that specific acquiring bank\. Leave it blank if you are not sure\!                 
| BIN Number             | It is a feature that allows the merchant to accept only Credit Cards within a specific set of BIN numbers\. Separate BIN number with comma Example: 4,5,4811,bni,mandiri\. Leave it blank if you are not sure\! |
| Installment Terms      | An arrangement for payment by installments\.                             
| 3D Secure              | You must enable 3D Secure for secure card transactions\. Please contact us if you wish to disable this feature in the Production environment\.                                                                  
| Save Card              | This will allow your customer to save their card on the payment popup, for faster payment flow on the following purchase\.                                                                                      



### Midtrans&nbsp;  MAP Configuration
1. Login to your [Midtrans&nbsp;  Account](https://dashboard.midtrans.com), select your environment (sandbox/production), go to menu `settings -> configuration`
   * Payment Notification URL: 
    >`https://[your-site-url]/snap/payment/notification`
   * Finish Redirect URL: 
    >`https://[your-site-url]/snap/index/finish`
   * Unfinish Redirect URL: 
    >`https://[your-site-url]/snap/index/finish`
   * Error Redirect URL: 
    >`https://[your-site-url]/snap/index/finish`

2. Go to menu **settings > Snap Preference > System Settings**
  * Insert `https://[your-site-url]/snap/index/finish` link as Finish/Unfinish/Error Redirect URL.


## How to online refund transaction

<details><summary>Click to expand info</summary>
<br>

You can request refunds either from the [Midtrans Dashboard](https://dashboard.midtrans.com/transactions) or from the Magento admin. After a refund is issued, it cannot be cancelled or undone. Before you trigger this request, make sure that the refund amount and any other details are correct.

If you make refund from the Midtrans Dashboard, Refund notification is sent to Magento, set transaction state to CLOSED and for now is not created the credit memo.

### Request refund from Magento Admin:

1. Log in to your Magento admin panel. 
2. In the menu, go to **Sales** > **Orders**. This opens the order overview page. 
3. Click on the **order** you want to refund.
4. In the **Order list View** left-hand navigation sidebar, click **Invoices** tab.
5. In the **invoice list page**, selected the **order**. click the **view button** on invoice you need to request online refund.
6. Click **Credit Memo** on the top-right corner of the page.
7. In the **New Memo for Invoice** page, scroll down to the **Refund Totals** section.
8. In this section, you can request online **Refund** or **Refund Offline**.
    *   **Refund:** This option will request refund Online to Midtrans, Midtrans automatically send refund notification and changes order status to **Closed** from notification.
    *   **Refund Offline**: An offline refund does not trigger request refund to midtrans it’s only refund in Magento side. You need to take action and carry out the refund manually from Midtrans dashboard.After a refund operation, the order status changes to **Closed**. This order status change is controlled by the Magento system. 
    
    The status change may not mean that the refund has carried out successfully on Midtrans side. When the transaction status in Midtrans dashboard changes to REFUND, then the refund went through successfully
</details>

### Configuring Custom Payment Fee
In case you need it, payment fee can optionally be added by using additional (3rd party) extension: [Mageprince Magento2 PaymentFee](https://github.com/mageprince/magento2-paymentfee/) extension. It allows adding extra charges for specific payment methods and displays them on the cart page, checkout page, invoice, and credit memo.

All the fee calculation will be handled by that extension. Midtrans extension will then take the produced PaymentFee value (from Magento order object), and parse it into additional item object for Midtrans API param.

#### Example on how to use the PaymentFee Extension
<details><summary>Click to expand info</summary>
<br>
    
You can try the demo extension [here](https://github.com/mageprince/magento2-paymentfee/#demo)

#### Installation & Configuration Instructions
You can install from [Magento Marketplace](https://marketplace.magento.com/prince-magento2-paymentfee.html) or follow [the manual installation step](https://github.com/mageprince/magento2-paymentfee/#installation-instruction).

#### How to configure the extension
1. Go to menu `Stores -> Configuration -> MagePrince -> Payment Fee`
2. Set Enable field to `Yes`
3. Go to `Payment Fee Settings` section, choose `PriceType`, Fill in `Minimum` and `Maximum order amount`.
4. Click `Add Fee` button, then choose Midtrans Payment method on `Payment Method Fee` and Fill in `Fee Amount`
5. Click `Save Config`

This is just an example configuration that works at the time of this writing, the extension may change in the future. For further & most up to date details of Mageprince PaymentFee extension configuration, you can check  [the official extension documentation](https://github.com/mageprince/magento2-paymentfee/#configuration).
</details>

#### Get help

* [General Documentation Midtrans](http://docs.midtrans.com)
* Please follow [this step by step guide](https://docs.midtrans.com/en/snap/with-plugins?id=magento) for complete configuration. If you have any feedback or request, please [do let us know here](https://docs.midtrans.com/en/snap/with-plugins?id=feedback-and-request).
* Technical Support Team Midtrans [support@midtrans.com](mailto:support@midtrans.com)
* [Midtrans Magento Demo Store](https://docs.midtrans.com/en/snap/with-plugins?id=midtrans-payment-plugin-live-demonstration) 
* [SNAP Documentation Product Midtrans](https://snap-docs.midtrans.com/)
* [CoreAPI Documentation Product Midtrans](https://api-docs.midtrans.com/)
* [Mobile Documentation Product Midtrans](http://mobile-docs.midtrans.com/)
