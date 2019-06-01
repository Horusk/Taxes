<?php
/**
 * User: Christian Eliasson <christianeliasson1985@gmail.com>
 * Date: 14/05/2019
 * Time: 12:52
 */
namespace UKOC\Seat\SocialistMining\Commands;

use Illuminate\Console\Command;
use UKOC\Seat\SocialistMining\Jobs\CompressedOrePriceHistoryJob;

class CompressedOrePriceHistoryUpdate extends Command {
    protected $signature = 'esi:CompressedOrePriceHistory:update';
    protected $description = 'Queue a job which will fetch and store historical price data for mined compressed ores';
    public function handle()
    {
        CompressedOrePriceHistoryJob::dispatch();
    }
}