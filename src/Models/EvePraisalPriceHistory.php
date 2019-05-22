<?php
/**
 * User: Christian Eliasson <christianeliasson1985@gmail.com>
 * Date: 16/05/2019
 * Time: 14:42
 */
namespace UKOC\Seat\SocialistMining\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
class CompressedOrePriceHistory extends Model {

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $table = 'seat_socialistmining_CompressedOrePriceHistory';
    protected $fillable = [
            'typeId', 'buyMin', 'buyMedian', 'buyMax', 'buyPercentile', 'updated_at', 'created_at'
    ];
    protected static function boot()
    {
        parent::boot();
    }
}