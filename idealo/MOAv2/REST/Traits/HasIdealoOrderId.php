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

trait HasIdealoOrderId
{
    /**
     * The ID of the shop at idealo.
     *
     * @var string
     */
    private $idealoOrderId;

    /**
     * Sets the ID of the shop at idealo.
     *
     * @param string $idealoOrderId
     * @return self
     */
    public function setIdealoOrderId(string $idealoOrderId): self
    {
        $this->idealoOrderId = $idealoOrderId;
        return $this;
    }

    /**
     * Returns the ID of the shop at idealo.
     *
     * @return string
     * @throws MissingPropertyException
     */
    protected function getIdealoOrderId(): string
    {
        if ( ! isset($this->idealoOrderId)) {
            throw MissingPropertyException::create('idealoOrderId', $this);
        }

        return $this->idealoOrderId;
    }

    /**
     * Replaces the placeholder for the shop ID in the URL path and returns it
     *
     * @param string $urlPath
     * @return string
     * @throws MissingPropertyException
     */
    protected function modifyIdealoOrderIdUrlPath(string $urlPath): string
    {
        return str_replace('{idealoOrderId}', $this->getIdealoOrderId(), $urlPath);
    }
}