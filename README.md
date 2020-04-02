# Midtrans Magento
## Document’s History

<table>
  <tr>
   <td><strong>Version</strong>
   </td>
   <td><strong>Date</strong>
   </td>
   <td><strong>Author</strong>
   </td>
   <td><strong>Note</strong>
   </td>
  </tr>
  <tr>
   <td>1.0
   </td>
   <td>Fri, Maret 27th 2020
   </td>
   <td>Zaki Ibrahim
   </td>
   <td>First Draft
   </td>
  </tr>
</table>

# 


# Overview
Midtrans ❤️ Magento! Midtrans is highly concerned with customer experience (UX). We strive to make payments simple for both the merchant and customers. With this plugin,  you can make your Magento store using Midtrans payment.

## Requirements:
*   An online store with Magento infrastructure. This plugin is tested with Magento v2.3.2
*   PHP v5.4 or greater.
*   MySQL v5.0 or greater.
*   Midtrans plugin for Magento v2.x [ [Github](https://github.com/Midtrans/Midtrans-Magento2) | [Zip](https://github.com/Midtrans/Midtrans-Magento2/archive/master.zip) ]


# How to install the plugins

## Install Midtrans Snap plugins through Composer
Before you begin to install through the composer, you need Magento marketplace account and make sure that you have installed Composer. In your terminal, go to the Magento folder and run the following commands:
1. Install the plugins: composer require midtrans/snap
2. Enable the plugin:  bin/magento module:enable Midtrans_Snap
3. Execute upgrade script : bin/magento setup:upgrade
4. Flush cache storage :  bin/magento cache:flush


## Install Midtrans Snap plugins through Magento marketplace 

1. TODO ELABORATE

## Install Midtrans Snap plugins from GitHub project

With these steps, you can custom/modify our Magento plugins to handle the business model that you want

1. Download and extract the plugin you have previously downloaded from GitHub and rename the folder as Snap.
2. Make a directory structure like this: \
    - app
      - code
        - Midtrans
            - Snap (the folder from step 1)

3. Locate the root Magento directory of your shop via FTP connection.
4. Copy the app folders into the Magento root folder.
5. Run this command on terminal

    `bin/magento module:enable Midtrans_Snap`
    
    `bin/magento setup:upgrade`
    
    `bin/magento cache:flush`



# How to use plugins
## Basic Plugins Configuration

Before you begin, make sure that you have successfully installed and enabled Midtrans Snap plugins.
Configure the Midtrans plugin in your Magento admin panel: 

1. Log in to your Magento admin panel. 
2. In the left navigation bar, go to **Stores(1)** > **Configuration(2)**. 
3. In the menu, go to **Sales(3)** > **Payment Methods(4)**

	



![](images/Midtrans-Magento1.png "image_tooltip")


4. In the Midtrans - Accept Online Payment section, click Basic Settings and fill out the following fields:

<table>
  <tr>
   <td>
<strong>Field</strong>
   </td>
   <td><strong>Description</strong>
   </td>
  </tr>
  <tr>
   <td>Is Production
   </td>
   <td>Select whether you want to use sandbox or production mode.
   </td>
  </tr>
  <tr>
   <td>Merchant ID
   </td>
   <td>Unique id of your Midtrans account for which the payments will be processed.
   </td>
  </tr>
  <tr>
   <td>Sandbox - ClientKey
   </td>
   <td>Used as an API key to be used for authorization sandbox environment on frontend API request/configuration. So it safe to put in your HTML / client code publicly.
   </td>
  </tr>
  <tr>
   <td>Sandbox - ServerKey
   </td>
   <td>Used as an API key to be used for authorization sandbox environment while calling Midtrans API from the backend. So keep it stored confidentially.
   </td>
  </tr>
  <tr>
   <td>Production - ClientKey
   </td>
   <td>Used as an API key to be used for authorization production environment on frontend API request/configuration. So it safe to put in your HTML / client code publicly.
   </td>
  </tr>
  <tr>
   <td>Production - ServerKey
   </td>
   <td>Used as an API key to be used for authorization production environment while calling Midtrans API from the backend. So keep it stored confidentially.
   </td>
  </tr>
  <tr>
   <td>Enable Snap redirect
   </td>
   <td>Change to Snap redirect mode, the default value is No.
   </td>
  </tr>
</table>


Note: Access Keys are unique for every merchant. Server Key is secret, please always keep Server Key confidential.


5. In the Log Options section, you will get default for request, notification and error log is turn on except Throw exception


## Config Plugins Payment Integration

In the Midtrans Magento plugins we have 4 option to use Snap model payment method, with the following description:


1. **Snap payment integration**

    This is the default Snap for Midtrans Magento plugins, Snap payment will auto-enabled when install the Midtrans plugins. Midtrans will show the available payment method on the Snap payment screen.

2. **Specific Payment integration | Optional** \
Specific payment it’s design as optional for specific payment that specify in Allow payment method field, Midtrans Snap will show directly payment method on the Snap screen.
3. **Online Installment payment integration | Optional**

    Online Installment payment it’s design as optional for Installment where the Card Issuer and Acquiring Bank is the same entity (e.g: BNI Card and BNI Acquiring bank).

4. **Offline Installment payment integration | Optional**

    Offline payment it’s desing as optional for Installment where the Card Issuer and Acquiring Bank don't have to be same entity (e.g: BNI Card and Mandiri Acquiring Bank)


Note: You can use different Midtrans Account for every Snap model payment method, should configure the access-key in Optional section `“Use another Midtrans account”`. If the optional access-key is empty, the plugins will automatically use access key on Basic Settings.

If you need custom configuration you can feel free to change the default configuration with the following fields 


<table>
  <tr>
   <td><strong>Field</strong>
   </td>
   <td><strong>Description</strong>
   </td>
  </tr>
  <tr>
   <td>Enable
   </td>
   <td>Payment snap section enable toggle
   </td>
  </tr>
  <tr>
   <td>Title
   </td>
   <td>The title for the payment method in the checkout page
   </td>
  </tr>
  <tr>
   <td>Custom Expiry
   </td>
   <td>This field will allow you to set custom duration on how long the transaction available to be paid.
   </td>
  </tr>
  <tr>
   <td>Sort Order
   </td>
   <td>The function do to sort your payment method in the checkout page
   </td>
  </tr>
  <tr>
   <td>Allowed Payment Method \
   </td>
   <td>Customize allowed payment method, separate payment method code with a comma. e.g: bank_transfer,credit_card. Leave it default if you are not sure.
   </td>
  </tr>
  <tr>
   <td>Acquiring Bank
   </td>
   <td>You can specify which Acquiring Bank they prefer to use for a specific transaction. The transaction fund will be routed to that specific acquiring bank. Leave it blank if you are not sure!
   </td>
  </tr>
  <tr>
   <td>BIN Number
   </td>
   <td>It is a feature that allows the merchant to accept only Credit Cards within a specific set of BIN numbers. Separate BIN number with comma Example: 4,5,4811,bni,Mandiri. Leave it blank if you are not sure!
   </td>
  </tr>
  <tr>
   <td>Installment Terms<br>
<p><small>
Only available in Offline payment specific</small>
   </td>
   <td>an arrangement for payment by installments.
   </td>
  </tr>
  <tr>
   <td>3D Secure
   </td>
   <td>You must enable 3D Secure for secure card transactions. Please contact us if you wish to disable this feature in the Production environment.
   </td>
  </tr>
  <tr>
   <td>Save Card
   </td>
   <td>This will allow your customer to save their card on the payment popup, for faster payment flow on the following purchase.
   </td>
  </tr>
</table>



## Magento Midtrans Notifications Handling

Login to your [Midtrans Account](https://dashboard.midtrans.com/settings/vtweb_configuration), select your environment (sandbox/production), go to menu **settings** > **configuration** and feel out the following URL fields:



*   Payment Notification URL:

    `https://[your-site-url]/snap/payment/notification`

*   Finish Redirect URL:

    `https://[your-site-url]/snap/index/finish`

*   Unfinish Redirect URL:

    `https://[your-site-url]/snap/index/finish`

*   Error Redirect URL:

    `https://[your-site-url]/snap/index/finish`


## How to online refund transaction

You can request refunds either in the [Midtrans Dashboard](https://dashboard.midtrans.com/transactions) or in the Magento admin. After a refund is issued, it cannot be cancelled or undone. Before you trigger this request, make sure that the refund amount and any other details are correct. The online refund function is available only for payment method gopay and credit card.

If you make a refund in the Midtrans Dashboard, a Refund notification is sent to Magento, set transaction state to CLOSE and for now is not created the credit memo.


### Request refund from Magento Admin:



1. Log in to your Magento admin panel. 
2. In the menu, go to **Sales** > **Orders**. This opens the order overview page. 
3. Click on the order you want to refund.
4. In the **Order list View** left-hand navigation sidebar, click **Invoices**tab.
5. In the invoice list page, selected the order. click the view button on invoice you need to request online refund.
6. Click **Credit Memo** on the top-right corner of the page. ****
7. In the **New Memo for Invoice** page, scroll down to the **Refund Totals** section.
8. In this section, you can request a online **Refund** or a **Refund Offline**.
    *   **Refund:** This option request refund Online to Midtrans, Midtrans automatically send refund notification and changes to **Closed**.
    *   **Refund Offline**: An offline refund does not trigger request refund to midtrans it’s only refund in Magento side. You need to take action and carry out the refund manually from Midtrans dashboard.

After a refund operation, the order status changes to **Closed**. This change is controlled by the Magento system, and we cannot influence it. The status change does not mean that the refund was carried out successfully on our side. If the transaction status in Midtrans dashboard changes to **REFUND**, then the refund went through successfully
