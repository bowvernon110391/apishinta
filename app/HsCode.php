<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HsCode extends Model
{
    use SoftDeletes;
    //
    // public $timestamps = false;
    protected $table = 'hs_code';

    // Helper
    static public function storeFullDesc() {
        // 1st, query all shit
        echo "Storing full desc for each row...expensive operation here!\n";

        $rows = HsCode::all();

        foreach ($rows as $hs) {
            echo "Storing data for {$hs->kode}...\n";
            $hs->full_desc = $hs->deskripsi;
            $hs->save();
        }

        echo "Done!";
    }

    public function scopeDistinctFirstNChar($query, $uraian, $n) {
        return $query->selectRaw("LEFT(`kode`, ?) ukod", [$n])
                ->byFullDesc($uraian)
                ->where('kode', '<>', '')
                ->distinct();
    }

    // query related rows based on uraian
    public static function queryRelated($uraian) {
        // grab lvl 4 kode
        $q1 = HsCode::distinctFirstNChar($uraian, 4);
        // grab lvl 6 kode
        $q2 = HsCode::distinctFirstNChar($uraian, 6);
        // union them
        $relatedKode = $q1->union($q2);
        // grab related 
        $relatedRows = HsCode::joinSub($relatedKode, 't', function ($join) {
            $join->on('kode', '=', 't.ukod');
        })->select('hs_code.*');

        return $relatedRows;
    }

    // query based on fulldesc and unionize with related rows, sorted out
    public static function queryWildcard($uraian) {
        $q1 = HsCode::byFullDesc($uraian);
        $q2 = HsCode::queryRelated($uraian);
        $q3 = ($q1->union($q2))->orderBy('id');
        return $q3;
    }

    public function scopeByFullDesc($query, $uraian) {
        return $query->where('full_desc', 'like', "%{$uraian}%");
    }

    public function scopeByHS($query, $code) {
        return $query->where('kode', 'like', "{$code}%");
    }

    public function scopeByJenis($query, $jenis) {
        return $query->where('jenis_tarif', $jenis);
    }

    public function scopeByUraian($query, $uraian) {
        return $query->where('uraian', 'like', "%{$uraian}%");
    }

    public function scopeByUraianFamily($query, $uraian) {
        return $query->byUraian($uraian);
    }

    public function scopeByExactHS($query, $code) {
        return $query->where('kode', $code);
    }

    public function scopeByHSHierarchy($query, $code) {
        // initially
        $q = $query->byHS($code);
        // iteratively union
       
        while ( ($len = strlen($code)) > 2 ) {
            $code = substr($code, 0, $len - ($len % 2 ? 1 : 2));
            $q = $q->orWhere(function ($query) use ($code) {
                $query->byExactHS($code);
            });
        }

        return $q;
    }

    public function scopeUsable($query) {
        return $query->whereRaw('LENGTH(`kode`) = 8');
    }

    // attribute
    public function getDeskripsiAttribute () {
        // parent stack
        $stack = [$this->parent];
        // text stack
        $deskripsi = $this->uraian;

        while (count($stack)) {
            $p = $stack[count($stack)-1];
            array_pop($stack);

            // check if it's valid
            if ($p) {
                $deskripsi = $p->uraian . "\n" . $deskripsi;
                // push its parent?
                $stack[] = $p->parent;
            }
        }
        return $deskripsi;
    }

    // Relations (with self)
    public function parent() {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children() {
        return $this->hasMany(self::class, 'parent_id');
    }
}
