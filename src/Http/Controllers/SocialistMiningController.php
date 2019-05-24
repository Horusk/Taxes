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

use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Services\Repositories\Character\Character;
use Seat\Web\Models\User;
use Seat\Web\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Industry\CharacterMining;
//use Seat\Services\Repositories\Eve;


/**
 * Class SocialistMiningController
 * @package UKOC\Seat\SocialistMining\Http\Controllers
 */
class SocialistMiningController extends Controller
{

    use Character;
    /**
     * @return \Illuminate\View\View
     */
    public function getHome()
    {
        return view('socialistmining::index');
    }
	public function getList(Request $request)
    {
        $characters = ($request->filtered === 'true') ?
            auth()->user()->group->users
                ->filter(function ($user) {
                    return $user->name !== 'admin' && $user->id !== 1;
                })
                ->map(function ($user) {
                    return $user->character;
            }) :
            $this->getAllCharactersWithAffiliations(false);

			foreach($characters as $i => $char){
				$characters[$i]->ledger = $this->getSocialistCorporationLedger($characters[$i].corporation_id,2019,4);
			}
        return DataTables::of($characters)
            ->addColumn('name_view', function ($row) {
                $character = $row;
                return view('web::partials.character', compact('character'));
            })
            ->editColumn('corporation_id', function ($row) {
                $corporation = $row->corporation_id;
                return view('web::partials.corporation', compact('corporation'));
            })
            ->editColumn('alliance_id', function ($row) {
                $alliance = $row->alliance_id;
                if (empty($alliance))
                    return '';
                return view('web::partials.alliance', compact('alliance'));
            })
            ->rawColumns(['name_view', 'corporation_id', 'alliance_id'])
            ->make(true);
    }
	/**
     * @param int  $corporation_id
     * @param int  $year
     * @param int  $month
     * @param bool $get
     *
     * @return mixed
     */
    public function getSocialistCorporationLedger(Request $request)
    {
        //$characters = $this->getAllCharactersWithAffiliations(false);
		//$characterIds = array_map(function($o) { return $o->character_id ;},$objects);
		//$tempMiningList = CharacterMining::select('groupType.typeID as typeId','groupType.marketGroupId as marketGroupId','character_minings.date as miningDate')
		//->join('invTypes as ogtype','ogtype.typeID', 'character_minings.type_id')
		//->join('invTypes as groupType','groupType.marketGroupId', 'ogtype.marketGroupId')
		//->groupBy('marketGroupId','typeId','miningDate')
		//->get();
		//foreach($tempMiningList as $tempItem){
		//	$this->getHistoricalPrice($tempItem->typeId, $ledger_entry->miningDate);
		//}
        $ledger = 
		CharacterMining::select('character_minings.character_id','originalTypes.typeName as typeName', 
		'character_minings.date as miningDate', 
		DB::raw('SUM(quantity) as quantity'), 
		DB::raw('ROUND(SUM(quantity)*0.01,2) as compressed_quantity'), 
		DB::raw('ROUND(SUM(quantity * originalPrice.adjusted_price),2) as originalAmounts'),
        'usr.name as userName',
        'usr.group_id as userGroupId',
        DB::raw('ROUND(SUM(quantity*0.01 * compressedPrice.adjusted_price),2) as compressedAmounts'))
            ->join('invTypes as originalTypes', 'originalTypes.typeID', 'character_minings.type_id')
            ->leftJoin('historical_prices as originalPrice', function ($join) {
                $join->on('originalPrice.type_id', '=', 'originalTypes.typeID')
                     ->on('originalPrice.date', '=', 'character_minings.date');
            })
			->join('invTypes as compressedTypes', function($join){$join->whereRaw("compressedTypes.typeName like concat('Compressed ',originalTypes.typeName)");})
            ->leftJoin('historical_prices as compressedPrice', function ($join) {
                $join->on('compressedPrice.type_id', '=', 'compressedTypes.typeID')
                     ->on('compressedPrice.date', '=', 'character_minings.date');
            })
            ->join('users as usr','usr.id', 'character_minings.character_id');
            //->where('character_id', $request->input('$corporation_id'))
            //->where('year',2019)
            //->where('month', 4)
        if($request['$startDate'] && $request['$endDate'])
            $ledger = $ledger->whereBetween('character_minings.date', [$request['$startDate'],$request['$endDate']]);

        $ledger = $ledger->groupBy('userName', 'miningDate', 'typeName');

			//if(!$request->$get)
				//return $ledger;
				return DataTables::of($ledger->get())->editColumn('miningDate', function ($user) 
{
    //change over here
    return date('Y-m-d', strtotime($user->miningDate) );
})->make();
            //->editColumn('quantity', function ($row) {
                //return view('web::partials.miningquantity', compact('row'));
            //})
            //->editColumn('month', function ($row) {
            //    return view('web::partials.miningvolume', compact('row'));
            //})
            //->rawColumns(['value', 'volume'])
            //->make(true);
    }
}
