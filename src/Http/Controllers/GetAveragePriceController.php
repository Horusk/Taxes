<?php
/*
This file is part of SeAT

Copyright (C) 2015, 2017  Leon Jacobs

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace UKOC\Seat\SocialistMining\Http\Controllers;

use Seat\Web\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Seat\Services\Repositories\Eve\EvePrices;
use \Datetime;
/**
 * Class GetAveragePriceController
 * @package UKOC\Seat\SocialistMining\Http\Controllers
 */
class GetAveragePriceController extends Controller
{
	use EvePrices;
	public function getPrices(Request $request)
    {

		$result = [];
		foreach($request['typeIds'] as $typeId){
			$typeTotal = 0;
			$numberOfDays = 0;

    		$begin = new DateTime( $request['startDate'] );
			$end   = new DateTime( $request['endDate'] );

			for($i = $begin; $i <= $end; $i->modify('+1 day')){

            	$histPrice = $this->getHistoricalPrice($typeId, $i->format('Y-m-d'));
            	if(!is_null($histPrice)){
					$numberOfDays++;
					$typeTotal += $histPrice->average_price;
				}
			}
			array_push($result, (object)['typeId'=>$typeId, 'avgPrice'=> round($typeTotal / $numberOfDays ,2)]);
		}
        return $result;
    }
}