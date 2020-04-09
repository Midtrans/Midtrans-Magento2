Midtrans ❤️ Magento! Midtrans is highly concerned with customer experience (UX). We strive to make payments simple for both the merchant and customers. With this plugin,  you can make your Magento store using Midtrans payment.

## Requirements:
*   An online store with Magento infrastructure. This plugin is tested with Magento v2.3.2
*   PHP v5.4 or greater.
*   MySQL v5.0 or greater.
*   Midtrans plugin for Magento v2.x [ [Github](https://github.com/Midtrans/Midtrans-Magento2) | [Zip](https://github.com/Midtrans/Midtrans-Magento2/archive/master.zip) ]


# How to install the plugins
## Install Midtrans Snap plugins through Composer
Before you begin to install through the composer, you need Magento marketplace account and make sure that you have installed Composer. In your terminal, go to the Magento folder and run the following commands:
1. Install the plugins: `composer require midtrans/snap`
2. Enable the plugin:  `bin/magento module:enable Midtrans_Snap`
3. Execute upgrade script : `bin/magento setup:upgrade`
4. Flush cache storage :  `bin/magento cache:flush`


## Install Midtrans Snap plugins through Magento marketplace 


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
    
    `bin/magento cache:flush`


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
    
    This is the default Snap for Midtrans Magento plugins, Snap payment will auto-enabled when install the Midtrans plugins. Midtrans will show the available payment method on the Snap payment screen.

2. **Specific Payment integration | Optional** 
    
    Enabling this will display additional payment options to customer, for specific payment that are specified in the "Allowed Payment Method" field, Midtrans Snap will show only the listed payment method on the Snap screen.

3. **Online Installment payment integration | Optional**

    Enabling this will display additional payment options to customer, for online installment payment where the Card Issuer and Acquiring Bank is the same entity (e.g: BNI Card and BNI Acquiring bank).

4. **Offline Installment payment integration | Optional**

    Enabling this will display additional payment options to customer, for offline Installment where the Card Issuer and Acquiring Bank don't have to be same entity (e.g: BNI Card and Mandiri Acquiring Bank)


>Note: You can use different Midtrans Account for every Snap model payment method, should configure the access-key in Optional section `“Use another Midtrans account”`. If the optional access-key is empty, the plugins will automatically use access key on Basic Settings.

>INFO: The built-in BCA Klikpay landing page for now will only use server key from basic settings of Snap payment integration

If you need custom configuration you can feel free to change the default configuration with the following fields 

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
    >`http://[your-site-url]/snap/payment/notification`
   * Finish Redirect URL: 
    >`http://[your-site-url]/snap/index/finish`
   * Unfinish Redirect URL: 
    >`http://[your-site-url]/snap/index/finish`
   * Error Redirect URL: 
    >`http://[your-site-url]/snap/index/finish`



## How to online refund transaction

You can request refunds either in the [Midtrans Dashboard](https://dashboard.midtrans.com/transactions) or in the Magento admin. After a refund is issued, it cannot be cancelled or undone. Before you trigger this request, make sure that the refund amount and any other details are correct. The online refund function is available only for payment method gopay and credit card.

If you make a refund in the Midtrans Dashboard, a Refund notification is sent to Magento, set transaction state to CLOSE and for now is not created the credit memo.


### Request refund from Magento Admin:

1. Log in to your Magento admin panel. 
2. In the menu, go to **Sales** > **Orders**. This opens the order overview page. 
3. Click on the order you want to refund.
4. In the **Order list View** left-hand navigation sidebar, click **Invoices**tab.
5. In the invoice list page, selected the order. click the view button on invoice you need to request online refund.
6. Click **Credit Memo** on the top-right corner of the page.
7. In the **New Memo for Invoice** page, scroll down to the **Refund Totals** section.
8. In this section, you can request online **Refund** or **Refund Offline**.
    *   **Refund:** This option will request refund Online to Midtrans, Midtrans automatically send refund notification and changes order status to **Closed** from notification.
    *   **Refund Offline**: An offline refund does not trigger request refund to midtrans it’s only refund in Magento side. You need to take action and carry out the refund manually from Midtrans dashboard.After a refund operation, the order status changes to **Closed**. This order status change is controlled by the Magento system. 
    
    The order status change does not mean that the refund was carried out successfully on Midtrans side. If the transaction status in Midtrans dashboard changes to **REFUND**, then the refund went through successfully
    
>Info: When refund process and the Magento dashboard show message `Midtrans Error (412): Transaction status cannot be updated.` The message occurs when do a refund before the transaction_status is settlement 

#### Get help

* [General Documentation Midtrans](http://docs.midtrans.com)
* Technical Support Team Midtrans [support@midtrans.com](mailto:support@midtrans.com)
* [SNAP Documentation Product Midtrans](https://snap-docs.midtrans.com/)
* [CoreAPI Documentation Product Midtrans](https://api-docs.midtrans.com/)
* [Mobile Documentation Product Midtrans](http://mobile-docs.midtrans.com/)
