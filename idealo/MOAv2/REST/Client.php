<?php
/*
   Copyright 2021 Timo Paul Dienstleistungen

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/


namespace idealo\MOAv2\REST;

use idealo\MOAv2\REST\Requests\Request;
use stdClass;

use idealo\MOAv2\REST\Exceptions\UnauthorizedAccessException;
use idealo\MOAv2\REST\Exceptions\MissingPropertyException;

use idealo\MOAv2\REST\Requests\OAuthToken;

class Client
{
    /**
     * The URL under which the productive API can be reached at idealo.
     *
     * @var string
     */
    protected $apiLiveUrl = 'https://orders.idealo.com/api/v2/';

    /**
     * The URL under which the sandbox API can be reached for testing at idealo.
     *
     * @var string
     */
    protected $apiTestUrl = 'https://orders-sandbox.idealo.com/api/v2/';

    /**
     * You can enter a URL to a test file with order-data here
     * This will be used to bypass the idealo API for testing purposes
     * Handle with caution!
     * Testfile needs to be utf8 encoded
     * Will not be used if set to false
     *
     * @var string|null
     */
    protected $debugDirectUrl = null;

    /**
     * The access token for requests to idealo.
     *
     * @var string
     */
    protected $token;

    /**
     * Expiry date of the access token as a timestamp.
     *
     * @var integer
     */
    protected $tokenValidUntil;

    /** @var string **/
    protected $clientId;

    /** @var string **/
    protected $clientSecret;

    /** @var string **/
    protected $shopId;

    /** @var int|null **/
    protected $httpStatus = null;

    /** @var bool **/
    protected $isLiveMode = false;

    /** @var string|null **/
    protected $curlError = null;

    /** @var integer|null **/
    protected $curlErrno = null;


    /** @var string|null **/
    protected $erpShopSystem = null;

    /** @var string|null **/
    protected $erpShopSystemVersion = null;

    /** @var string|null **/
    protected $integrationPartner = null;

    /** @var string|null **/
    protected $interfaceVersion = null;


    /**
     * Client constructor.
     * 
     * @param string $clientId
     * @param string $clientSecret
     * @param bool $live
     * @param string|null $erpShopSystem
     * @param string|null $erpShopSystemVersion
     * @param string|null $integrationPartner
     * @param string|null $interfaceVersion
     */
    public function __construct(
        string $clientId,
        string $clientSecret,
        bool $live = false,
        ?string $erpShopSystem = null,
        ?string $erpShopSystemVersion = null,
        ?string $integrationPartner = null,
        ?string $interfaceVersion = null
    )
    {
        $this->setClientId($clientId);
        $this->setClientSecret($clientSecret);
        $this->setIsLiveMode($live);
        null === $erpShopSystem         || $this->setErpShopSystem($erpShopSystem);
        null === $erpShopSystemVersion  || $this->setErpShopSystemVersion($erpShopSystemVersion);
        null === $integrationPartner    || $this->setIntegrationPartner($integrationPartner);
        null === $interfaceVersion      || $this->setInterfaceVersion($interfaceVersion);
    }

    /**
     * Sets the debug url and returns the current object.
     *
     * @param string|null $debugDirectUrl
     * @return $this
     */
    public function setDebugDirectUrl(?string $debugDirectUrl): self
    {
        $this->debugDirectUrl = $debugDirectUrl;
        return $this;
    }

    /**
     * Returns the debug url.
     * If none is set, null is returned.
     *
     * @return string|null
     */
    public function getDebugDirectUrl(): ?string
    {
        return $this->debugDirectUrl;
    }

    /**
     * Sets the access token and returns the current object.
     *
     * @param string $token
     * @return $this
     */
    protected function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Returns the access token.
     * If none exists, one will be requested.
     *
     * @return  string
     * @throws  UnauthorizedAccessException
     */
    protected function getToken(): string
    {
        if ( ! isset($this->token)) {
            $request = (new OAuthToken())
                ->setClientId($this->getClientId())
                ->setClientSecret($this->getClientSecret());
            $response = $this->sendRequest($request);

            $this->setToken($response->access_token)
                ->setTokenExpiration($response->expires_in)
                ->setShopId($response->shop_id);
        }

        return $this->token;
    }

    /**
     * Sets the time in seconds for how long the token is valid.
     *
     * @param   int     $expiresId
     * @return  $this
     */
    protected function setTokenExpiration(int $expiresId): self
    {
        $this->tokenValidUntil = time() + $expiresId;
        return $this;
    }

    /**
     * Sets the ID of the client at idealo and returns the current object.
     *
     * @param string $clientId
     * @return $this
     */
    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * Returns the ID of the client at idealo.
     *
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * Sets the shop ID at idealo and returns the current item.
     *
     * @param string $shopId
     * @return $this
     */
    protected function setShopId(string $shopId): self
    {
        $this->shopId = $shopId;
        return $this;
    }

    /**
     * Returns the shop ID at idealo.
     *
     * @return string
     */
    public function getShopId(): string
    {
        return $this->shopId;
    }

    /**
     * Sets the secret of the client at idealo and returns the current object.
     *
     * @param string $clientSecret
     * @return $this
     */
    public function setClientSecret(string $clientSecret): self
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    /**
     * Returns the secret of the client at idealo.
     *
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * Sets the HTTP status of the last request and returns the current object.
     *
     * @param string|null $httpStatus
     * @return $this
     */
    protected function setHttpStatus(?int $httpStatus): self
    {
        $this->httpStatus = $httpStatus;
        return $this;
    }

    /**
     * Returns the HTTP status of the last request.
     *
     * @return string|null
     */
    public function getHttpStatus(): ?int
    {
        return $this->httpStatus;
    }

    /**
     * Sets the flag as to whether the productive environment should be requested from idealo
     * and returns the current object.
     *
     * @param bool $live
     * @return $this
     */
    public function setIsLiveMode(bool $live): self
    {
        $this->isLiveMode = $live;
        return $this;
    }

    /**
     * Returns the flag as to whether the productive environment should be requested from idealo.
     *
     * @return bool
     */
    public function getIsLiveMode(): bool
    {
        return $this->isLiveMode;
    }

    /**
     * Sets the CURL error of the last request and returns the current object.
     *
     * @param string|null $curlError
     * @return $this
     */
    protected function setCurlError(?string $curlError): self
    {
        $this->curlError = $curlError;
        return $this;
    }

    /**
     * Returns the CURL error of the last request.
     *
     * @return string|null
     */
    public function getCurlError(): ?string
    {
        return $this->curlError;
    }

    /**
     * Sets the CURL error number of the last request and returns the current object.
     *
     * @param int|null $curlErrno
     * @return $this
     */
    protected function setCurlErrno(?int $curlErrno): self
    {
        $this->curlErrno = $curlErrno;
        return $this;
    }

    /**
     * Returns the CURL error number of the last request.
     *
     * @return int|null
     */
    public function getCurlErrno(): ?int
    {
        return $this->curlErrno;
    }

    /**
     * Sets the name of the ERP shop system used and returns the current object.
     *
     * @param string $erpShopSystem
     * @return $this
     */
    public function setErpShopSystem(string $erpShopSystem): self
    {
        $this->erpShopSystem = $erpShopSystem;
        return $this;
    }

    /**
     * Returns the name of the ERP shop system used.
     *
     * @return string|null
     */
    public function getErpShopSystem(): ?string
    {
        return $this->erpShopSystem;
    }

    /**
     * Sets the version of the ERP shop system used and returns the current object.
     *
     * @param string $erpShopSystemVersion
     * @return $this
     */
    public function setErpShopSystemVersion(string $erpShopSystemVersion): self
    {
        $this->erpShopSystemVersion = $erpShopSystemVersion;
        return $this;
    }

    /**
     * Returns the version of the ERP shop system used.
     *
     * @return string|null
     */
    public function getErpShopSystemVersion(): ?string
    {
        return $this->erpShopSystemVersion;
    }

    /**
     * Sets the name of the integration partner and returns the current object.
     *
     * @param string $integrationPartner
     * @return $this
     */
    public function setIntegrationPartner(string $integrationPartner): self
    {
        $this->integrationPartner = $integrationPartner;
        return $this;
    }

    /**
     * Returns the name of the integration partner.
     *
     * @return string|null
     */
    public function getIntegrationPartner(): ?string
    {
        return $this->integrationPartner;
    }

    /**
     * Sets the version of the interface used and returns the current object.
     *
     * @param string $interfaceVersion
     * @return $this
     */
    public function setInterfaceVersion(string $interfaceVersion): self
    {
        $this->interfaceVersion = $interfaceVersion;
        return $this;
    }

    /**
     * Returns the version of the interface.
     *
     * @return string|null
     */
    public function getInterfaceVersion(): ?string
    {
        return $this->interfaceVersion;
    }

    /**
     * Generates a new request, sets the token for authentication, possibly the ID of the shop at iDealo, and returns this request.
     *
     * @param string $request
     * @return Request
     * @throws UnauthorizedAccessException
     */
    public function generateRequest(string $request): Request
    {
        $request = (new $request())
            ->setToken($this->getToken());

        if (method_exists($request, 'setShopId')) {
            $request->setShopId($this->getShopId());
        }

        return $request;
    }

    /**
     * Returns information about the interface as an HTTP header in an array.
     *
     * @return array
     */
    protected function getReportingHeaders(): array
    {
        $headers = [];
        if (null !== $this->getErpShopSystem()) {
            $headers[] = 'ERP-Shop-System:' . $this->getErpShopSystem();
        }
        if (null !== $this->getErpShopSystemVersion()) {
            $headers[] = 'ERP-Shop-System-Version:' . $this->getErpShopSystemVersion();
        }
        if (null !== $this->getIntegrationPartner()) {
            $headers[] = 'Integration-Partner:' . $this->getIntegrationPartner();
        }
        if (null !== $this->getInterfaceVersion()) {
            $headers[] = 'Interface-Version:' . $this->getInterfaceVersion();
        }
        return $headers;
    }

    /**
     * Resets the properties of the last request.
     */
    protected function resetStatusProperties(): void
    {
        $this->setHttpStatus(null);
        $this->setCurlErrno(null);
        $this->setCurlError(null);
    }

    /**
     * Generates the URL for a request.
     *
     * @param Request $request
     * @return string
     * @throws MissingPropertyException
     */
    protected function generateRequestUrl(Request $request): string
    {
        if (null !== $this->getDebugDirectUrl()
            && false === $this->getIsLiveMode()
        ) {
            return $this->getDebugDirectUrl();
        }

        if (true === $this->getIsLiveMode()) {
            $baseUrl = $this->apiLiveUrl;
        } else {
            $baseUrl = $this->apiTestUrl;
        }

        return $baseUrl . $request->getUrlPath();
    }

    /**
     * Generates the HTTP header for a request.
     *
     * @param Request $request
     * @return array
     */
    protected function buildHttpHeaders(Request $request): array
    {
        return array_merge($this->getReportingHeaders(), $request->getHttpHeaders());
    }

    /**
     * Send a request to the API and return the result as an object.
     *
     * @param Request $request
     * @param int $tries
     * @return stdClass|array|null
     * @throws MissingPropertyException
     * @throws UnauthorizedAccessException
     */
    public function sendRequest(Request $request, int $tries = 2)
    {
        $this->resetStatusProperties();

        $url = $this->generateRequestUrl($request);
        $curl = curl_init($url);

        $httpHeaders = $this->buildHttpHeaders($request);
        if (0 < count($httpHeaders)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $httpHeaders);
        }

        $request->setCurlOptions($curl);

        curl_setopt($curl, CURLOPT_TIMEOUT, 60); // timeout in seconds
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);

        $this->setHttpStatus((int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE));

        if ('' != curl_error($curl)) {
            $this->setCurlError(curl_error($curl));
            $this->setCurlErrno(curl_errno($curl));
        }
        curl_close($curl);

        if (false === $response && 0 < --$tries && null !== $this->getCurlError()) {
            return $this->sendRequest($request, $tries);
        }

        if (200 != $this->getHttpStatus()) {
            switch ($this->getHttpStatus()) {
                case 502;
                    $this->setCurlError('API down');
                    break;
                case 401:
                    throw UnauthorizedAccessException::build($url);
                default:
                    $this->setCurlError(null);
                    break;
            }
        }

        return json_decode($response);
    }

}