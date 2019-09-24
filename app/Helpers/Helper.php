<?php

// function: getSequence(kode_sequence, tahun)
// desc: mengembalikan sequence dari database (call getSequence() from database)
// return: sequence in integer
if (!function_exists('getSequence')) {
    // declare if not exists
    function getSequence($kode_sequence, $tahun = null ) {
        // if tahun is unspecified, use current year
        $tahun = $tahun ?? (int)date('Y');
        return collect( DB::select("SELECT getSequence(?, ?) AS seq", [$kode_sequence, $tahun]) )->first()->seq;
    }
}

?>