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
use Seat\Services\Models\HistoricalPrices;
use Illuminate\Support\Facades\Cache;

/**
 * Class GetAveragePriceController
 * @package UKOC\Seat\SocialistMining\Http\Controllers
 */
class GetEvepraisalPriceController extends Controller
{
	use EvePrices;
	public function getPrices(Request $request)
    {
    	if(!is_array($request['typeNames']))
    		return abort(400);
    	if(sizeof($request['typeNames'])==0)
    		return abort(400);
    	$evepraisalRequest = implode("\r\n", $request['typeNames']);

    	$cachedResponse = Cache::get($evepraisalRequest);
    	if(!is_null($cachedResponse))
    		return response()->json($cachedResponse);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://evepraisal.com/appraisal.json?market=jita&persist=no" );
		// SSL important
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, true);
		// TODO: replace with seat admin email?
		curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain', 'User-Agent: "SeAT mining tax calculator christianeliasson1985@gmail.com"')); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $evepraisalRequest);
		$output = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($output);

		$result = [];
		foreach ($response->appraisal->items as $item) {
			array_push($result, ['typeId' => $item->typeID, 'price' => $item->prices->buy->max]);
		}

		Cache::add($evepraisalRequest, $result, now()->addMinutes(60));
        return response()->json($result);
    }
	public function test()
    {
         $datesAndCompressedTypes = DB::select("SELECT cm.type_id FROM character_minings cm  group by cm.type_id;");

        foreach($datesAndCompressedTypes as $dateAndType){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://esi.evetech.net/latest/markets/10000002/history/?datasource=tranquility&type_id=". $dateAndType->type_id );
			// SSL important
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($ch);
			curl_close($ch);
			$historicPriceDataForItems = json_decode($output);

	        foreach($historicPriceDataForItems as $historicPriceDataForItem){
	        	HistoricalPrices::updateOrCreate(
	        		['type_id'=> $dateAndType->type_id, 'date' => $historicPriceDataForItem->date], 
	        		['type_id'=> $dateAndType->type_id, 'date' => $historicPriceDataForItem->date, 'average_price' => $historicPriceDataForItem->average, 'adjusted_price' => $historicPriceDataForItem->highest]);
	        }
        }
    	return $datesAndCompressedTypes;
    }
}