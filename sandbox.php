<?php

require_once dirname(__FILE__) . '/autoload.php';

use idealo\MOAv2\REST\Client;
use idealo\MOAv2\REST\Requests\GetOrder;
use idealo\MOAv2\REST\Requests\GetOrders;
use idealo\MOAv2\REST\Requests\GetRefunds;
use idealo\MOAv2\REST\Requests\RefundOrder;
use idealo\MOAv2\REST\Requests\RevokeOrder;
use idealo\MOAv2\REST\Requests\SetFulfillmentInformation;
use idealo\MOAv2\REST\Requests\SetMerchantOrderNumber;

function getDefault(string $key): ?string
{
    switch ($key) {
        case 'clientId':
            return 'YOUR_SANDBOX_CLIENT_ID';
        case 'clientSecret':
            return 'YOUR_SANDBOX_CLIENT_SECRET';
        case 'mode':
            return 'test';
    }
    return null;
}

function getPostValue(string $key, ?bool $getDefault = false, $filter = FILTER_DEFAULT)
{
    if (filter_has_var(INPUT_POST, $key)) {
        $value =  filter_input(INPUT_POST, $key, $filter);
        return $value ?: null;
    }
    if ($getDefault === true) {
        return getDefault($key);
    }
    return null;
}

function getClient(): Client
{
    $clientId       = getPostValue('clientId');
    $clientSecret   = getPostValue('clientSecret');
    $isLive = 'live' === (string) getPostValue('mode');

    return (new Client($clientId, $clientSecret, $isLive))
        ->setERPShopSystem('SDK GUI')
        ->setERPShopSystemVersion('1.0.0')
        ->setIntegrationPartner('Timo Paul Dienstleistungen')
        ->setInterfaceVersion('1.0.0');
}

function getErrorOutput(Client $client): string
{
    return implode("\n", [
        'HTTP-Code: ' . $client->getHttpStatus(),
        'CURL-Error: ' . $client->getCurlError(),
        'CURL-Error-Nr: ' . $client->getCurlErrno(),
    ]);
}

function getResponseOutput(Client $client, $response): string
{
    if (null === $response) {
        return getErrorOutput($client);
    }

    $output = 'HTTP-Code: ' . $client->getHttpStatus();

    if (! is_array($response) || 0 < count($response)) {
        $output .= "\n\n" . print_r($response, true);
    }

    return $output;
}

function getOrders(): string
{
    $client = getClient();
    $request = $client->generateRequest(GetOrders::class);

    $status = implode(',', filter_input(INPUT_POST, 'getOrders-orderStatus', FILTER_DEFAULT , FILTER_REQUIRE_ARRAY) ?? []);
    $request->addParameter('status', $status);

    return getResponseOutput($client, $client->sendRequest($request));
}

function getOrder(): string
{
    $idealoOrderId = getPostValue('getOrder-idealoOrderId', null);
    if (null === $idealoOrderId) {
        return 'Idealo order-ID missing!';
    }

    $client = getClient();
    $request = $client->generateRequest(GetOrder::class)
        ->setIdealoOrderId($idealoOrderId);

    return getResponseOutput($client, $client->sendRequest($request));
}

function setMerchantOrderNumber(): string
{
    $idealoOrderId          = getPostValue('setMerchantOrderNumber-idealoOrderId');
    $merchantOrderNumber    = getPostValue('setMerchantOrderNumber-merchantOrderNumber');

    if (null === $idealoOrderId) {
        return 'Idealo order-ID missing!';
    } elseif (null === $merchantOrderNumber) {
        return 'Merchant order-number missing!';
    }

    $client = getClient();
    $request = $client->generateRequest(SetMerchantOrderNumber::class)
        ->setIdealoOrderId($idealoOrderId)
        ->setMerchantOrderNumber($merchantOrderNumber);

    return getResponseOutput($client, $client->sendRequest($request));
}

function setFulfillmentInformation(): string
{
    $idealoOrderId  = getPostValue('setFulfillmentInformation-idealoOrderId');
    $carrier        = getPostValue('setFulfillmentInformation-carrier');
    $trackingCode   = getPostValue('setFulfillmentInformation-trackingCode');

    if (null === $idealoOrderId) {
        return 'Idealo order-ID missing!';
    }

    $client = getClient();
    $request = $client->generateRequest(SetFulfillmentInformation::class)
        ->setIdealoOrderId($idealoOrderId);
    if ($carrier) {
        $request->setCarrier($carrier);
    }
    if ($trackingCode) {
        $request->setTrackingCode([$trackingCode]);
    }

    return getResponseOutput($client, $client->sendRequest($request));
}

function revokeOrder(): string
{
    $idealoOrderId      = getPostValue('revokeOrder-idealoOrderId');
    $sku                = getPostValue('revokeOrder-sku');
    $remainingQuantity  = getPostValue('revokeOrder-remainingQuantity');
    $reason             = getPostValue('revokeOrder-reason');
    $comment            = getPostValue('revokeOrder-comment');

    if (null === $idealoOrderId) {
        return 'Idealo order-ID is missing!';
    }
    if (null === $sku) {
        return 'SKU is missing!';
    }
    if (null === $reason) {
        return 'Reason is missing!';
    }

    $client = getClient();
    $request = $client->generateRequest(RevokeOrder::class)
        ->setIdealoOrderId($idealoOrderId)
        ->setSku($sku)
        ->setReason($reason);
    if ($remainingQuantity) {
        $request->setRemainingQuantity($remainingQuantity);
    }
    if ($comment) {
        $request->setComment($comment);
    }

    return getResponseOutput($client, $client->sendRequest($request));
}

function refundOrder(): string
{
    $idealoOrderId      = getPostValue('refundOrder-idealoOrderId');
    $refundAmount       = getPostValue('refundOrder-refundAmount');

    if (null === $idealoOrderId) {
        return 'Idealo order-ID is missing!';
    }

    $client = getClient();
    $request = $client->generateRequest(RefundOrder::class)
        ->setIdealoOrderId($idealoOrderId)
        ->setRefundAmount($refundAmount)
        ->setCurrency('EUR');

    return getResponseOutput($client, $client->sendRequest($request));
}

function getRefunds(): string
{
    $idealoOrderId = getPostValue('getRefunds-idealoOrderId');

    if (null === $idealoOrderId) {
        return 'Idealo order-ID is missing!';
    }

    $client = getClient();
    $request = $client->generateRequest(GetRefunds::class)
        ->setIdealoOrderId($idealoOrderId);

    return getResponseOutput($client, $client->sendRequest($request));
}

function handleRequest(string $request): ?string
{
    if (getPostValue($request, true) && function_exists($request)) {
        if ( ! getPostValue('clientId')) {
            return 'Client-Id missing!';
        }
        if ( ! getPostValue('clientSecret')) {
            return 'Client-Secret missing!';
        }
        return call_user_func($request);
    }
    return null;
}

?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>idealo MOAv2 SDK - sandbox</title>
        <style type="text/css">
            h3 a {
                color: #49B382;
                text-decoration: none;
            }
            .container {
                max-width: 1200px;
                margin: auto;
                padding: 20px 0;
            }
            fieldset {
                margin-bottom: 20px;
            }
            fieldset pre {
                width: 100%;
                overflow-x: auto;
            }
            h4 {
                margin-top: 20px;
                margin-bottom: 0px;
            }
            pre {
                margin-top: 5px;
                margin-bottom: 5px;
            }
            a.reset {
                font-size: 80%;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>idealo MOAv2 SDK - sandbox</h1>
            <h3>Presented by <a href="https://timopaul.biz/" target="_blank">Timo Paul Dienstleistungen</a></h3>
            <form id="sdk" name="sdk" method="post">
                <fieldset>
                    <legend>Base configuration</legend>
                    <table>
                        <tr>
                            <td width="175">Client-Id</td>
                            <td><input type="text" name="clientId" value="<?php echo getPostValue('clientId'); ?>" size="25"></td>
                        </tr>
                        <tr>
                            <td>Client-Secret</td>
                            <td><input type="text" name="clientSecret" value="<?php echo getPostValue('clientSecret'); ?>" size="25"></td>
                        </tr>
                        <tr>
                            <td>Mode</td>
                            <td>
                                <select name="mode" style="width:172px;">
                                    <option value="live" <?php if (getPostValue('mode') == 'live') echo 'selected'; ?>>Live</option>
                                    <option value="test" <?php if (getPostValue('mode') == 'test') echo 'selected'; ?>>Test</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </fieldset>
                <fieldset>
                    <legend>GetOrders</legend>
                    <div>
                        <?php foreach ([
                            'PROCESSING',
                            'COMPLETED',
                            'REVOKING',
                            'REVOKED',
                            'PARTIALLY_REVOKED'
                        ] as $status) { ?>
                            <input type="checkbox"
                                   name="getOrders-orderStatus[]"
                                   value="<?php echo $status; ?>"
                                   <?php echo ! filter_has_var(INPUT_POST, 'getOrders-orderStatus') || in_array($status, filter_input(INPUT_POST, 'getOrders-orderStatus', FILTER_DEFAULT , FILTER_REQUIRE_ARRAY)) ? ' checked="checked"' : '' ?>><?php echo $status; ?><br />
                        <?php } ?>
                        <input type="submit" name="getOrders" value="Send request"><br />
                    </div>
                    <?php if ($result = handleRequest('getOrders')) { ?>
                        <div class="result">
                            <h4>Result:</h4>
                            <pre><?php echo $result; ?></pre>
                        </div>
                    <?php } ?>
                </fieldset>
                <fieldset>
                    <legend>GetOrder</legend>
                    <div>
                        <table>
                            <tr>
                                <td width="300">Idealo order-ID</td>
                                <td><input type="text" name="getOrder-idealoOrderId" value="<?php echo getPostValue('getOrder-idealoOrderId'); ?>" size="25"></td>
                            </tr>
                        </table>
                        <input type="submit" name="getOrder" value="Send request">
                    </div>
                    <?php if ($result = handleRequest('getOrder')) { ?>
                        <div class="result">
                            <h4>Result:</h4>
                            <pre><?php echo $result; ?></pre>
                        </div>
                    <?php } ?>
                </fieldset>
                <fieldset>
                    <legend>SetMerchantOrderNumber</legend>
                    <div>
                        <table>
                            <tr>
                                <td width="300">Idealo order-ID</td>
                                <td><input type="text" name="setMerchantOrderNumber-idealoOrderId" value="<?php echo getPostValue('setMerchantOrderNumber-idealoOrderId'); ?>" size="25"></td>
                            </tr>
                            <tr>
                                <td>Merchant order-number</td>
                                <td><input type="text" name="setMerchantOrderNumber-merchantOrderNumber" value="<?php echo getPostValue('setMerchantOrderNumber-merchantOrderNumber'); ?>" size="25"></td>
                            </tr>
                        </table>
                        <input type="submit" name="setMerchantOrderNumber" value="Send request">
                    </div>
                    <?php if ($result = handleRequest('setMerchantOrderNumber')) { ?>
                        <div class="result">
                            <h4>Result:</h4>
                            <pre><?php echo $result; ?></pre>
                        </div>
                    <?php } ?>
                </fieldset>
                <fieldset>
                    <legend>SetFulfillmentInformation</legend>
                    <div>
                        <table>
                            <tr>
                                <td width="300">Idealo order-ID</td>
                                <td><input type="text" name="setFulfillmentInformation-idealoOrderId" value="<?php echo getPostValue('setFulfillmentInformation-idealoOrderId'); ?>" size="25"></td>
                            </tr>
                            <tr>
                                <td>Shipping carrier (optional)</td>
                                <td><input type="text" name="setFulfillmentInformation-carrier" value="<?php echo getPostValue('setFulfillmentInformation-carrier'); ?>" size="25"></td>
                            </tr>
                            <tr>
                                <td>Trackingcode (optional)</td>
                                <td><input type="text" name="setFulfillmentInformation-trackingCode" value="<?php echo getPostValue('setFulfillmentInformation-trackingCode'); ?>" size="25"></td>
                            </tr>
                        </table>
                        <input type="submit" name="setFulfillmentInformation" value="Send request">
                    </div>
                    <?php if ($result = handleRequest('setFulfillmentInformation')) { ?>
                        <div class="result">
                            <h4>Result:</h4>
                            <pre><?php echo $result; ?></pre>
                        </div>
                    <?php } ?>
                </fieldset>
                <fieldset>
                    <legend>RevokeOrder</legend>
                    <div>
                        <table>
                            <tr>
                                <td width="300">Idealo order-ID</td>
                                <td><input type="text" name="revokeOrder-idealoOrderId" value="<?php echo getPostValue('revokeOrder-idealoOrderId'); ?>" size="25"></td>
                            </tr>
                            <tr>
                                <td>SKU</td>
                                <td><input type="text" name="revokeOrder-sku" value="<?php echo getPostValue('revokeOrder-sku'); ?>" size="25"></td>
                            </tr>
                            <tr>
                                <td>remaining quantity (optional)</td>
                                <td><input type="text" name="revokeOrder-remainingQuantity" value="<?php echo getPostValue('revokeOrder-remainingQuantity'); ?>" size="25"></td>
                            </tr>
                            <tr>
                                <td>reason</td>
                                <td><input type="text" name="revokeOrder-reason" value="<?php echo getPostValue('revokeOrder-reason'); ?>" size="25"></td>
                            </tr>
                            <tr>
                                <td>comment (optional)</td>
                                <td><input type="text" name="revokeOrder-comment" value="<?php echo getPostValue('revokeOrder-comment'); ?>" size="25"></td>
                            </tr>
                        </table>
                        <input type="submit" name="revokeOrder" value="Send request">
                    </div>
                    <?php if ($result = handleRequest('revokeOrder')) { ?>
                        <div class="result">
                            <h4>Result:</h4>
                            <pre><?php echo $result; ?></pre>
                        </div>
                    <?php } ?>
                </fieldset>
                <fieldset>
                    <legend>RefundOrder</legend>
                    <div>
                        <table>
                            <tr>
                                <td width="300">Idealo order-ID</td>
                                <td><input type="text" name="refundOrder-idealoOrderId" value="<?php echo getPostValue('refundOrder-idealoOrderId'); ?>" size="25"></td>
                            </tr>
                            <tr>
                                <td>amount</td>
                                <td><input type="text" name="refundOrder-refundAmount" value="<?php echo getPostValue('refundOrder-refundAmount'); ?>" size="25"></td>
                            </tr>
                        </table>
                        <input type="submit" name="refundOrder" value="Send request">
                    </div>
                    <?php if ($result = handleRequest('refundOrder')) { ?>
                        <div class="result">
                            <h4>Result:</h4>
                            <pre><?php echo $result; ?></pre>
                        </div>
                    <?php } ?>
                </fieldset>
                <fieldset>
                    <legend>GetRefunds</legend>
                    <div>
                        <table>
                            <tr>
                                <td width="300">Idealo order-ID</td>
                                <td><input type="text" name="getRefunds-idealoOrderId" value="<?php echo getPostValue('getRefunds-idealoOrderId'); ?>" size="25"></td>
                            </tr>
                        </table>
                        <input type="submit" name="getRefunds" value="Send request">
                    </div>
                    <?php if ($result = handleRequest('getRefunds')) { ?>
                        <div class="result">
                            <h4>Result:</h4>
                            <pre><?php echo $result; ?></pre>
                        </div>
                    <?php } ?>
                </fieldset>
            </form>
        </div>
    </body>
</html>