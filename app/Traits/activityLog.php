<?php

namespace App\Traits;
use App\Models\Fare;
use App\Brand;

trait activityLog {

	function createLog($controllerModel='',$controllerModelLog='',$id='')
	{
	$fares_log = $controllerModel::where('id', '=', $id )->get()->toArray();
       unset($fares_log[0]['id']);
     //  unset($fares_log[0]['created_at']);
       unset($fares_log[0]['updated_at']);
       foreach ($fares_log as $item) 
        {
          return  $controllerModelLog::insert($item);
        }
        
	}
        
        function mySqlDate($date='')
	{ 
        if ($date!= '') {
           return date('Y-m-d', strtotime($date));
        } else {
           return NULL;
        
	}
        }
        
        function displayView($fieldname) {
    if ($fieldname != '') {
        echo $fieldname;
    } else {
        echo "N/A";
    }
}

function dateView($date_blank) {
    if ($date_blank == "0000-00-00" || $date_blank == '') {
        echo "N/A";
    } else {
        echo $date_blank = date("d-m-Y", strtotime($date_blank));
        ;
    }
}
        
}

