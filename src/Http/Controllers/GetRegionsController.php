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
use \Datetime;
/**
 * Class GetRegionsController
 * @package UKOC\Seat\SocialistMining\Http\Controllers
 */
class GetRegionsController extends Controller
{
	public function getRegionsAutoComplete(Request $request)
    {
          $search = $request->get('term');
      
          $result = DB::table('mapRegions')->where('regionName','like','%'. $search . '%')->select('regionName')->get();
 
          return response()->json($result);
            
    } 
	public function getRegion($regionName)
    {      
          $result = DB::table('mapRegions')->where('regionName','like', $regionName)->get();
 			if(sizeof($result) == 1)
          		return response()->json($result[0]);
          	return abort(404);;
            
    } 
	public function regionTest()
    {
    	return "OK";
    }
}