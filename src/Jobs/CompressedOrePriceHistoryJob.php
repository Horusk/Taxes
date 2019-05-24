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
use UKOC\Seat\SocialistMining\Models\CompressedOrePriceHistory;
use Seat\Services\Repositories\Eve\EvePrices;

/**
 * Class Mining.
 * @package Seat\Eveapi\Jobs\Industry\Character
 */
class CompressedOrePriceHistoryJob extends EsiBase
{
    use EvePrices;
    /**
     * @var array
     */
    protected $tags = ['CompressedOrePriceHistory'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {
        $this->getHistoricalPrice(28418, '2019-04-01');
        //fetch names of all compressed ores but with some pretty code
        //SELECT inType.typeName FROM seat.invTypes as inType 
        //JOIN seat.invGroups as ingroup on ingroup.groupID = inType.groupID 
        //where ingroup.categoryID = 25 and inType.typeName like 'Compressed%';

        //upsert daily prices for items
		
        return;
    }

}