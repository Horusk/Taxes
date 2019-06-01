<?php
/**
 * User: Christian Eliasson <christianeliasson1985@gmail.com>
 * Date: 01/06/2019
 * Time: 21:30
 */
namespace UKOC\Seat\SocialistMining\Commands;

use Illuminate\Console\Command;
use UKOC\Seat\SocialistMining\Jobs\OrePriceHistoryJob;

class OrePriceHistoryUpdate extends Command {
    protected $signature = 'esi:OrePriceHistoryJob:update';
    protected $description = 'Queue a job which will fetch and store historical price data for mined ores';
    public function handle()
    {
        OrePriceHistoryJob::dispatch();
    }
}