<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRegionAndSolarSystemTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $mapSolarSystemsPath = realpath(__DIR__ . '/socialistmining_mapSolarSystems.sql');
        $mapSolarSystemsSql = file_get_contents($mapSolarSystemsPath);
        DB::unprepared($mapSolarSystemsSql);
        
        $mapRegionsPath = realpath(__DIR__ . '/socialistmining_mapRegions.sql');
        $mapRegionsSql = file_get_contents($mapRegionsPath);
        DB::unprepared($mapRegionsSql);
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mapSolarSystems');
        Schema::dropIfExists('mapRegions');
    }
}