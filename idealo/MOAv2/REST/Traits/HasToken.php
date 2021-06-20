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


namespace idealo\MOAv2\REST\Traits;

use idealo\MOAv2\REST\Exceptions\MissingPropertyException;

trait HasToken
{
    /**
     * The token is used for authentication.
     *
     * @var string
     */
    private $token;

    /**
     * Sets the token for authentication.
     *
     * @param   string  $token
     * @return  self
     */
    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Returns the token used for authentication.
     *
     * @return  string
     * @throws  MissingPropertyException
     */
    protected function getToken(): string
    {
        if ( ! isset($this->token)) {
            throw MissingPropertyException::create('token', $this);
        }

        return $this->token;
    }

    /**
     * Returns the http headers for authentication against the token.
     *
     * @return array
     * @throws MissingPropertyException
     */
    protected function getTokenHttpHeader(): array
    {
        return [
            'Authorization: Bearer ' . $this->getToken(),
        ];
    }
}