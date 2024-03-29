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

// Namespace all of the routes for this package.
Route::group([
    'namespace'  => 'UKOC\Seat\SocialistMining\Http\Controllers',
    'middleware' => ['web','auth','bouncer:socialistmining.TaxCalculator']
], function () {

    // Your route definitions go here.
    Route::get('/socialistmining', [
        'as'   => 'socialistmining',
        'uses' => 'SocialistMiningController@getHome'
    ]);
	
	Route::get('/socialistmining/data', [
		'as'   => 'socialistmining.list.data',
		'uses' => 'SocialistMiningController@getList',
	]);
	Route::post('/socialistmining/getSocialistCorporationLedger', [
		'as'   => 'socialistmining.corp.ledger',
		'uses' => 'SocialistMiningController@getSocialistCorporationLedger',
	]);

    Route::post('/averageprices', [
        'as'   => 'averageprices',
        'uses' => 'GetAveragePriceController@getPrices'
    ]);

    Route::post('/evepraisalprices', [
        'as'   => 'evepraisalprices',
        'uses' => 'GetEvepraisalPriceController@getPrices'
    ]);

    Route::get('/test', [
        'as'   => 'averageprices.test',
        'uses' => 'GetAveragePriceController@test'
    ]);
    Route::get('/test2', [
        'as'   => 'regions.test2',
        'uses' => 'GetRegionsController@regionTest'
    ]);
    Route::get('/regions/autocomplete', [
        'as'   => 'regions.autocomplete',
        'uses' => 'GetRegionsController@getRegionsAutoComplete'
    ]);
    Route::get('/region/{regionName}', [
        'as'   => 'region.get',
        'uses' => 'GetRegionsController@getRegion'
    ]);
});
