<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AppLog extends Model
{
    //
    protected $table = 'log';

    protected $attributes = [
        'loggable_type' => '',
        'loggable_id' => 0
    ];

    public function loggable(){
        return $this->morphTo();
    }
    
    static public function logInfo($message, $object = NULL){
        $log = new AppLog;
        $log->message = $message;
        $log->object = $object;
        $log->tipe = 'INFO';
        $log->save();

        return $log;
    }

    static public function logWarning($message, $object = NULL){
        $log = new App\Log;
        $log->message = $message;
        $log->object = $object;
        $log->tipe = 'WARNING';
        $log->save();

        return $log;
    }

    static public function logError($message, $object = NULL){
        $log = new App\Log;
        $log->message = $message;
        $log->object = $object;
        $log->tipe = 'ERROR';
        $log->save();

        return $log;
    }


}
