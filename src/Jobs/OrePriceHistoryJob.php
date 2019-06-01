<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace UKOC\Seat\SocialistMining\Jobs;

use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Jobs\EsiBase;
use Seat\Services\Repositories\Eve\EvePrices;
use Seat\Services\Models\HistoricalPrices;

/**
 * Class Mining.
 * @package Seat\Eveapi\Jobs\Industry\Character
 */
class OrePriceHistoryJob extends EsiBase
{
    use EvePrices;
    /**
     * @var array
     */
    protected $tags = ['OrePriceHistoryJob'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
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
        return;
    }

}