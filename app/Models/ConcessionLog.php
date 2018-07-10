<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ConcessionLog extends Model
{
  /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'concession_logs';
    protected $guarded = [];
      public function services()
    {
       
        return $this->hasMany('App\Models\Service', 'bus_type_id');
    }
  
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    
}
