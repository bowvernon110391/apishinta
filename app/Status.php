<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Status extends Model
{
    // table name
    protected $table = 'status';


    protected $fillable = ['status', 'lokasi'];

    protected $attributes = [
        'statusable_id' => 0,
        'statusable_type' => ''
    ];

    public function statusable(){
        return $this->morphTo();
    }

    public function scopeByDoctype($query, $doctype) {
        return $query->where('statusable_type', $doctype);
    }

    public function scopeLatestPerDoctype($query, $timerange=null) {
        return $query->latest()
                    ->join(
                        DB::raw("
                        (SELECT
                            a.statusable_id,
                            MAX(a.id) last_id
                        FROM
                            `status` a
                        GROUP BY
                            a.statusable_id
                        ) stat
                        "),
                        function ($join) {
                            $join->on('status.statusable_id', '=', 'stat.statusable_id');
                            $join->on('status.id', '=', 'stat.last_id');
                        }
                    );
    }

    public function scopeByStatus($query, $status) {
        return $query->where('status', '=', $status);
    }

    public function scopeByStatusOtherThan($query, $status) {
        return $query->where('status', '<>', $status);
    }
}
