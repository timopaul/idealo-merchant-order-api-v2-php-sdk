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

trait IsPostRequest
{
    /**
     * Returns the method with which the request should be executed.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return 'POST';
    }

    /**
     * Returns the HTTP headers for a POST request
     *
     * @param $curl
     */
    protected function setPostRequestCurlOptions(&$curl): void
    {
        curl_setopt($curl, CURLOPT_POST, true);
    }

    /**
     * Returns the http headers for authentication against the token.
     *
     * @return array
     */
    protected function getPostRequestHttpHeader(): array
    {
        return [
            'Content-Type: application/json',
        ];
    }

}