# **Flying Blue+ Payment Method**

## Introduction

##

The target audience of this document is Online Merchants who intend to offer Flying Blue+ as an alternate payment method in their shops which are created on Magento v2.4.x. Please also note that the visuals included in this document are to illustrate the user experience and do not necessarily represent the actual calculations involved in the transaction.


## Supported versions

Magento CE: 2.4.x

Magento EE: 2.4.x



## Configurations

This section provides information about the overall installation via SFTP and Magento admin panel.

>Please note – User and group file/directory permissions of all the files/directories of the plugin and the certificate you will generate per later sections should be same as the Magento installation folder.

##

## Installation

Since Magento 2.4.x platform doesn't provide inline installation/plugin manager, the installation needs to be done using FTP.

1. Copy the plugin files to your main directory of Magento 2 (the directory structure in our files has been retained).
2. Connect through ssh and go to the main folder with Magento files.
3. To verify that the extension installed properly, run the following command:

```bin/magento module:status```

4. By default, the extension is disabled.

List of disabled module:

```Magento\_FlyingBluePayment```

5. Some extensions won't work properly unless you clear the Magento-generated static view files first. Use the --clear-static-content option to clear the static view files when you're enabling the extension.

Enable the extension and clear static view files:

```bin/magento module:enable Magento\_FlyingBluePayment --clear-static-content```

6. You should see the following output:

The following modules have been enabled:

```Magento\_FlyingBluePayment```



##

## Certificate Configurations

#### Generating the digital certificate for signing API requests

For enhanced security of the payment transaction, all the API requests are signed digitally using the X.509 certificate. Follow the steps below to generate the certificate.


1. Generate a private-public key pair and obtain a certificate corresponding to the public key. We recommend using an 8192 bit key to accommodate the payload size.

2. The certificate can be either self-signed or signed by a Certificate Authority (CA). Self-signed certificates can be generated either using the openssl command or the Java keytool command.

##

    Commands for self-signed certificate generation using openssl command
    -- Generate private key
    openssl req -newkey rsa:8192 -nodes -keyout key.pem -x509 -days 1095 -out certificate.cer -sha256

    -- Convert to .p12 format
    openssl pkcs12 -inkey key.pem -in certificate.cer -export -out certificate.p12 -name <merchant code>


###

Alternatively, below Java keytool command can be used:

    Commands for self-signed certificate generation using keytool command
    -- Generate a private/public key pair
    keytool -genkeypair -alias  <<your alias>>  -keys -keyalg RSA -keysize 8192 -dname "CN=<<Name>>" -validity 1095 -storetype PKCS12 -keystore <<KeyStore Name>> -storepass  <<your password>>

    e.g.
    keytool -genkeypair -alias prod-merchant-signature-keys -keyalg RSA -keysize 8192 -dname "CN=prod-merchant" -validity 1095 -storetype PKCS12 -keystore prod-merchant-signature-keys.p12 -storepass 12345

    --Export the public certificate from a p12/pfx file
    keytool -exportcert -alias <<your alias>>  -keys -storetype PKCS12 -keystore <<your alias .p12 -file <<CertificateName>>.cer -rfc -storepass <<your password>>

    e.g.
    keytool -exportcert -alias prod-merchant-signature-keys -storetype PKCS12 -keystore prod-merchant-signature-keys.p12 -file prod-merchant-public-certificate.cer -rfc -storepass 12345



3. Share the public certificate (.cer) file with Pointspay which will be added in the trust store at Pointspay side.

4. The private key should not be shared with anyone; it will be known only to the partner merchant.

##

## Admin Configuration page

Go to Magento Admin -> STORES -> Settings -> Configuration -> Sales -> Payment Methods -> Flying Blue+ 

### Basic Settings

![](./app/code/Magento/FlyingBluePayment/view/frontend/web/img/Picture1.png)


| **Configuration** | **Description** |
| --- | --- |
| Enabled | Option to turn plugin on/off (Yes/No). If "No" is selected, Flying Blue+ wouldn't appear among payment methods on the checkout page. |
| Test Mode | If "Yes" is selected, all requests are sent to the Pointspay test server, and all such payments will not be valid for pay-out. |
| Title | Title text is shown on checkout process under selection of payment methods. |
| Debug | In case of any error, the Pointspay support team will ask you to turn on (Yes) this option; by default this is set to No. |
| Payment From Applicable Countries | This option defines the applicability of Flying Blue+ either to All countries or to the selected countries. |
| Payment From Specific Countries | This option defines the applicability of Flying Blue+ to the selected countries. Flying Blue+ payment method will not be shown at the checkout page if the customer is from a country that isn't listed here. |
| Sort Order | This option defines the sort order for payment methods on the checkout page. |


##

##### Access Settings

![](./app/code/Magento/FlyingBluePayment/view/frontend/web/img/Picture2.png)


| **Configuration** | **Description** |
| --- | --- |
| API Username | Will be supplied by Pointspay |
| API Password | Will be supplied by Pointspay |
| API Access Token | Will be supplied by Pointspay |
| Merchant Code (Shop ID) | Will be supplied by Pointspay |
| Directory to certificate | The full path on the server (including file name) pointing to the digital certificate .p12 file generated as part of the section "Generating digital certificate for signing API requests" |
| Private key password | Password of the private key |


##

### Refunds

The Pointspay Refund service is integrated with the Magento Online Refund Service under Credit Memo.

1. 1.	Navigate to Sales -> Orders -> View the order -> Select invoice -> Go to invoice by clicking “View” link -> Click on "Credit Memo" (on top-right)
2. Select the amount to be refunded under Shipping. You can also include any Adjustment refund/fee.
3. Click on "Online Refund" if present, or on "Refund". Please note that only Online Refund is supported for Flying Blue+.


![](./app/code/Magento/FlyingBluePayment/view/frontend/web/img/Picture3.png)


#### Troubleshooting

If you are unable to pay with Flying Blue+ and are stuck with an activity indicator at the checkout page, it is very likely that the certificate file (.p12 file) does not have the expected file permissions. The user and group of certificate file permissions should be same as the Magento installation folder.

www.pointspay.com
