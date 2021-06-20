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

use idealo\MOAv2\REST\Traits\HasParameters;
use idealo\MOAv2\REST\Traits\HasShopId;
use idealo\MOAv2\REST\Traits\HasToken;
use idealo\MOAv2\REST\Traits\IsGetRequest;

class getOrders extends Request
{
    use HasParameters;
    use HasShopId;
    use HasToken;
    use IsGetRequest;

    protected $urlPath = 'shops/{shopId}/orders';

    /**
     * All parameters that are valid for this request as an array.
     *
     * @var array
     */
    protected $validParameters = [
        'status',
        'acknowledged',
        'pageSize',
        'pageNumber',
    ];
}