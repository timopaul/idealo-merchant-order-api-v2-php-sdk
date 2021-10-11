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

use idealo\MOAv2\REST\Exceptions\InvalidParameterException;
use idealo\MOAv2\REST\Exceptions\MissingPropertyException;
use idealo\MOAv2\REST\Exceptions\UnknownParameterException;

trait HasParameters
{
    /**
     * The parameters for the request.
     *
     * @var array
     */
    private $parameters = [];

    /**
     * Magic method to set parameters dynamically.
     *
     * @param $method
     * @param array $parameters
     * @return mixed
     * @throws InvalidParameterException
     * @throws MissingPropertyException
     */
    public function __call($method, array $parameters) {

        $match = [];
        if (preg_match('#^set([A-Z][A-Za-z0-9]*)$#', $method, $match)) {
            $name = lcfirst($match[1]);
            if ($this->isValidParameter($name)) {
                return $this->addParameter($name, $parameters[0]);
            }
        }

        return is_callable([parent, '__call'])
            ? parent::__call($method, $parameters)
            : call_user_func_array([parent, $method], $parameters);
    }

    /**
     * Returns true if the parameter is valid for this request.
     *
     * @param string $name
     * @return bool
     * @throws MissingPropertyException
     */
    public function isValidParameter(string $name): bool
    {
        if ( ! isset($this->validParameters)) {
            throw MissingPropertyException::create($name, $this);
        }

        return in_array($name, $this->validParameters);
    }

    /**
     * Sets the parameters for the request.
     *
     * @param   array   $parameters
     * @return  self
     */
    public function setParameters(array $parameters): self
    {
        $this->clearParameters();
        return $this->addParameters($parameters);
    }

    /**
     * Returns the parameters for the request.
     *
     * @return  array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Adds parameters to the request.
     *
     * @param array $parameters
     * @return $this
     */
    public function addParameters(array $parameters): self
    {
        foreach ($parameters as $name => $value) {
            $this->addParameter($name, $value);
        }
        return $this;
    }

    /**
     * Adds a parameter to the request.
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     * @throws MissingPropertyException
     * @throws InvalidParameterException
     */
    public function addParameter(string $name, $value): self
    {
        if ( ! $this->isValidParameter($name)) {
            throw InvalidParameterException::create($name, $this);
        }

        // use boolean url-parameters as integers
        if (is_bool($value)) {
            $value = (int) $value;
        }

        $this->parameters[$name] = $value;
        return $this;
    }

    /**
     * Removes all parameters for this request.
     *
     * @return $this
     */
    public function clearParameters(): self
    {
        $this->parameters = [];
        return $this;
    }

    /**
     * Returns a parameter.
     *
     * @param string $name
     * @return mixed
     * @throws UnknownParameterException
     */
    public function getParameter(string $name)
    {
        if ( ! isset($this->parameters[$name])) {
            throw UnknownParameterException::create($name);
        }

        return $this->parameters[$name];
    }

    /**
     * Removes a parameter and returns its value.
     *
     * @param string $name
     * @return mixed
     * @throws UnknownParameterException
     */
    public function removeParameter(string $name)
    {
        $value = $this->getParameter($name);
        unset($this->parameters[$name]);
        return $value;
    }


    protected function getParameterString(): string
    {
        $parameters = [];
        foreach ($this->getParameters() as $name => $value) {
            $parameters[] = $name . '=' . $value;
        }

        if (0 < count($parameters)) {
            return implode('&', $parameters);
        }

        return '';
    }

    /**
     * Adds the parameters for the request to the URL.
     *
     * @param   string $urlPath
     * @return  string
     */
    protected function modifyParametersUrlPath(string $urlPath): string
    {
        // use url-parameters only for get-requests
        if (in_array(IsGetRequest::class, class_uses(static::class))) {
            $parameters = $this->getParameterString();
            if ('' !== $parameters) {
                $urlPath .= '?' . $parameters;
            }
        }

        return $urlPath;
    }

    /**
     * Sets the CURL options for a request with URL parameters.
     *
     * @param $curl
     */
    protected function setParametersCurlOptions(&$curl): void
    {
        // set the parameters for post-requests
        if (in_array(IsPostRequest::class, class_uses(static::class))) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($this->getParameters()));
        }
    }

}