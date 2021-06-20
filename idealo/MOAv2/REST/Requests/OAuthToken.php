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


namespace idealo\MOAv2\REST\Requests;

use idealo\MOAv2\REST\Exceptions\MissingPropertyException;
use idealo\MOAv2\REST\Traits\IsPostRequest;

class OAuthToken extends Request
{
    use IsPostRequest;

    /**
     * The ID of the client to authenticate with idealo.
     *
     * @var string
     */
    private $clientId;

    /**
     * The secret of the client for authentication with idealo.
     *
     * @var string
     */
    private $clientSecret;

    /**
     * Path from the URL for the request.
     *
     * @var string
     */
    protected $urlPath = 'oauth/token';

    /**
     * Sets the ID of the client to authenticate with idealo.
     *
     * @param   string $clientId
     * @return  self
     */
    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * Returns the ID of the client for authentication with idealo.
     *
     * @return  string
     * @throws  MissingPropertyException
     */
    protected function getClientId(): string
    {
        if ( ! isset($this->clientId)) {
            throw MissingPropertyException::create('clientId', $this);
        }

        return $this->clientId;
    }

    /**
     * Sets the secret of the client to authenticate with idealo.
     *
     * @param   string $clientSecret
     * @return  self
     */
    public function setClientSecret(string $clientSecret): self
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    /**
     * Returns the secret of the client for authentication with idealo.
     *
     * @return  string
     * @throws  MissingPropertyException
     */
    protected function getClientSecret(): string
    {
        if ( ! isset($this->clientSecret)) {
            throw MissingPropertyException::create('clientSecret', $this);
        }

        return $this->clientSecret;
    }

    /**
     * Generates the HTTP headers for the request and returns them as an array.
     *
     * @return  array
     * @throws MissingPropertyException
     */
    public function getHttpHeaders(): array
    {
        return array_merge(
            parent::getHttpHeaders(),
            ['Authorization: Basic ' . $this->getClientCredentials()]
        );
    }

    /**
     * Returns the base64-encoded client credentials for authentication.
     *
     * @return  string
     * @throws  MissingPropertyException
     */
    private function getClientCredentials(): string
    {
        return base64_encode(implode(':', [
            $this->getClientId(),
            $this->getClientSecret(),
        ]));
    }
}