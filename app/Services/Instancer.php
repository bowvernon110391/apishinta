<?php

namespace App\Services;

use App\BPJ;
use App\CD;
use App\InstruksiPemeriksaan;
use App\IS;
use App\Lampiran;
use App\LHP;
use App\SPMB;
use App\SPP;
use App\ST;

class Instancer
{
    static protected $resolvables = [
        // what path resolves to what
        'cd'    => CD::class,
        'spp'   => SPP::class,
        'st'    => ST::class,
        'is'    => IS::class,
        'spmb'  => SPMB::class,
        'bpj'   => BPJ::class,
        'lampiran'  => Lampiran::class,
        'lhp'   => LHP::class,
        'ip'    => InstruksiPemeriksaan::class,
        'instruksi_pemeriksaan' => InstruksiPemeriksaan::class,

    ];

    public function resolveClassname($name)
    {
        if (!key_exists($name, Instancer::$resolvables)) {
            return null;
        }
        return Instancer::$resolvables[$name];
    }

    // from /cd/2 we can try to resolve(cd, 2)
    public function findOrFail($name, $id)
    {
        try {
            $classname = $this->resolveClassname($name);
            if (!$classname) {
                throw new \Exception("'{$name}' is not resolvable");
            }

            return $classname::findOrFail($id);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // same as above, non exception version
    public function find($name, $id)
    {
        $classname = $this->resolveClassname($name);

        if (!$classname) return null;

        return $classname::find($id);
    }
}
