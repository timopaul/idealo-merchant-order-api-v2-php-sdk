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


namespace idealo\MOAv2\REST\Exceptions;

use Exception;

class MissingPropertyException extends Exception
{
    /**
     * Creates a new exception of itself and returns it.
     *
     * @param string $property
     * @param ?mixed $class
     * @return self
     */
    static public function create(string $property, $class = null): self
    {
        if (null !== $class && ! is_string($class)) {
            $class = basename(get_class($class));
        }

        return new static(sprintf(
            'Missing property `%s`' . (null !== $class ? ' in `' . $class . '`' : '') . '!',
            $property,
            $class
        ));
    }
}