# idealo Merchant Order API v2: PHP SDK
idealo Direktkauf v2 PHP SDK
#### Presented by [Timo Paul Dienstleistungen](https://timopaul.biz/)

# Implementation Guide

## License and usage
This SDK can be used under the conditions of the Apache License 2.0, see LICENSE for details

## Technical requirements
- Standard Apache webserver with at least PHP 7.1
- The curl library for PHP

## Introduction
The implementation of the idealo SDK is easy and straightforward. How it is used can be seen in the `sandbox.php` file located in the same folder as this readme file. To test to idealo orders API, follow these steps:

1. put the SDK into a folder on your webserver
2. open `http://<hostname>/path_to_sdk/sandbox.php`
3. enter the client id and secret token you that you have created in your idealo business account under `Settings > API clients > Credentials for Merchant Order API v2`

## Basics
The SDK has an autoloader file, which automatically loads the class(es) of the SDK, so that you can use all of them in your project. Simply include the autoloader file at the spot in your code, where you create the instance of the client object using "require_once".

	require_once dirname(__FILE__) . '/sdk/autoload.php';

Then you can instantiate the REST-client-class from anywhere in your code like this:

    $client = new idealo\MOAv2\REST\Client($clientId, $clientSecret, $isLive);

The client needs 3 parameters:

1. $clientId - the client-id for idealo
2. $clientSecret - the client-secret for idealo
3. $isLive - true for live-mode and false for test-mode

In order to let the idealo API know who is talking to it, you can transfer relevant information:

    use idealo\MOAv2\REST\Client;

    $client = (new Client($clientId, $clientSecret, $isLive))
        ->setERPShopSystem('SDK GUI')
        ->setERPShopSystemVersion('1.0.0')
        ->setIntegrationPartner('Timo Paul Dienstleistungen')
        ->setInterfaceVersion('1.0.0');

The documentation for all inquiries with the possible parameters can be found at: https://cdn.idealo.com/folder/Direktkauf/documentation/merchant-order-api-v2.html

## Implementation

With this client object you can execute all REST API requests from idealo.

### GetOrders

    use idealo\MOAv2\REST\Client;
    use idealo\MOAv2\REST\Requests\getOrders;

    $client = new Client($clientId, $clientSecret, $isLive);
    $request = $client->generateRequest(getOrders::class);
    $response = $client->sendRequest($request);

Or only request via orders with the status _PROCESSING_:

    use idealo\MOAv2\REST\Client;
    use idealo\MOAv2\REST\Requests\getOrders;

    $client = new Client($clientId, $clientSecret, $isLive);
    $request = $client->generateRequest(GetOrders::class)
        ->setStatus('PROCESSING');
    $response = $client->sendRequest($request);

Requests all orders from idealo. They are delivered as an stdClass, directly like idealo delivers them in the following format:

    stdClass Object
    (
        [idealoOrderId] => BDKAV9DF
        [created] => 2021-05-25T09:07:59Z
        [updated] => 2021-05-25T09:07:59.332Z
        [status] => REVOKING
        [currency] => EUR
        [offersPrice] => 1209.59
        [grossPrice] => 1213.39
        [shippingCosts] => 3.80
        [lineItems] => Array
            (
                [0] => stdClass Object
                (
                    [title] => ASUS ZENBOOK UX303LA-R4286H / 13,3 Full-HD / Intel Core i5-5200U / 8GB / 256GB SSD / Win8.1
                    [price] => 994.10
                    [priceRangeAmount] => 19.88
                    [quantity] => 1
                    [sku] => 100010
                    [merchantDeliveryText] => 1-2+Werktage
                )

                [1] => stdClass Object
                    (
                        [title] => Levi's® Jeans, Levis 511 Flex
                        [price] => 215.49
                        [quantity] => 1
                        [sku] => 100014
                        [merchantDeliveryText] => 1-2+Werktage
                    )

            )

        [customer] => stdClass Object
            (
                [email] => m-peh0njpsvdzabhjv@checkout-stg.idealo.de
            )
    
        [payment] => stdClass Object
            (
                [paymentMethod] => PAYPAL
                [transactionId] => snakeoil-e1844b3
            )
    
        [billingAddress] => stdClass Object
            (
                [salutation] => MR
                [firstName] => Bob
                [lastName] => Menke
                [addressLine1] => Straße 61
                [addressLine2] => Hinterhof 3
                [postalCode] => 04945
                [city] => Ort
                [countryCode] => DE
            )
    
        [shippingAddress] => stdClass Object
            (
                [salutation] => MR
                [firstName] => Bob
                [lastName] => Menke
                [addressLine1] => Straße 61
                [addressLine2] => Hinterhof 3
                [postalCode] => 04945
                [city] => Ort
                [countryCode] => DE
            )
    
        [fulfillment] => stdClass Object
            (
                [method] => POSTAL
                [tracking] => Array
                    (
                    )
    
                [options] => Array
                    (
                    )
    
            )
    
        [refunds] => Array
            (
            )
    
    )

### GetOrder

    use idealo\MOAv2\REST\Client;
    use idealo\MOAv2\REST\Requests\getOrder;

    $client = new Client($clientId, $clientSecret, $isLive);
    $request = $client->generateRequest(GetOrder::class)
        ->setIdealoOrderId($idealoOrderId);
    $response = $client->sendRequest($request);

Requests an order from idealo.

### SetMerchantOrderNumber

    use idealo\MOAv2\REST\Client;
    use idealo\MOAv2\REST\Requests\SetMerchantOrderNumber;

    $client = new Client($clientId, $clientSecret, $isLive);
    $request = $client->generateRequest(SetMerchantOrderNumber::class)
        ->setIdealoOrderId($idealoOrderId)
        ->setMerchantOrderNumber($merchantOrderNumber);

    $response = $client->sendRequest($request);

Sets the number of the order from the merchant for an order from Idealo.

### SetFulfillmentInformation

    use idealo\MOAv2\REST\Client;
    use idealo\MOAv2\REST\Requests\SetFulfillmentInformation;

    $client = new Client($clientId, $clientSecret, $isLive);
    $request = $client->generateRequest(SetFulfillmentInformation::class)
        ->setIdealoOrderId($idealoOrderId)
        ->setCarrier($carrier)
        ->setTrackingCode([$trackingCode]);

    $response = $client->sendRequest($request);

Sets the order status to "COMPLETED" and as "sent to the customer".

In addition, tracking information such as carrier and tracking codes can be transmitted so that idealo can track the order and transmit this information to the customer. Tracking information can be expanded by sending carrier and tracking codes multiple times.

### RevokeOrder

    use idealo\MOAv2\REST\Client;
    use idealo\MOAv2\REST\Requests\RevokeOrder;

    $client = new Client($clientId, $clientSecret, $isLive);
    $request = $client->generateRequest(RevokeOrder::class)
        ->setIdealoOrderId($idealoOrderId)
        ->setSku($sku)
        ->setReason($reason)
        ->setRemainingQuantity($remainingQuantity)
        ->setComment($comment);

    $response = $client->sendRequest($request);

Revocation of an order with idealo. The revocation is required. A withdrawal does not automatically trigger a refund. To refund an order, please use `RefundOrder`.

### RefundOrder

    use idealo\MOAv2\REST\Client;
    use idealo\MOAv2\REST\Requests\RefundOrder;

    $client = new Client($clientId, $clientSecret, $isLive);
    $request = $client->generateRequest(RefundOrder::class)
        ->setIdealoOrderId($idealoOrderId)
        ->setRefundAmount($refundAmount)
        ->setCurrency('EUR');

    $response = $client->sendRequest($request);

Refunding an order that was paid for with idealo checkout payments.

A refund does not automatically mean that the order will be revoked. To revoke an order, please use `RevokeOrder`.

### GetRefunds

    use idealo\MOAv2\REST\Client;
    use idealo\MOAv2\REST\Requests\GetRefunds;

    $client = new Client($clientId, $clientSecret, $isLive);
    $request = $client->generateRequest(GetRefunds::class)
        ->setIdealoOrderId($idealoOrderId);

    $response = $client->sendRequest($request);

Requests all refunds of an order from idealo. They are delivered ad an array, directly like idealo delivers them in the following format:

    Array
    (
        [0] => stdClass Object
            (
                [refundId] => snakeoil-7d759d41624159499446
                [refundTransactionId] => n/a
                [status] => FAILED
                [currency] => EUR
                [refundAmount] => 111.79
                [failureReason] => Initial Payment was not processed by Idealo Direktkauf Payments
                [created] => 2021-06-20T03:24:59.643Z
                [updated] => 2021-06-20T03:25:00.197Z
            )
    )

## Error-handling

The client throws an Exception when any of the above listed requests failed with a CURL-error.

You can access the information to this error for logging purposes or whatever you need them for, with the following methods:

    $client->getCurlError()` // error-message from CURL
    $client->getCurlErrno()` // error-number from CURL

In any case you can get the HTTP status code from the last request with the following method:

    $client->getHttpStatus()

If this method returns 200, the last request was fine.

In the idealo API documentation, you can find a list with the HTTP status error-codes and their meanings for every request.

### Logging

Errors will be logged to the default webserver error log.

### Testing

You can configure a direct link to a test-file filled with json-encoded orders like you would receive them directly from the API. You have to set the link with `$client->setDebugDirectUrl(?string $debugDirectUrl);`. For example like this: `http://YOUR_SERVER_HERE/order_test_file.txt`

# Problems

If you have any questions or problems regarding the SDK, please contact the author directly. You are also welcome to start a merge request for extensions, this will then be checked and approved.