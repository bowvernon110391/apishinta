<?php

namespace App;

trait TraitHasDokkaps
{
    public function dokkap() {
        return $this->morphMany(Dokkap::class, 'master');
    }

    // this will syncDokkap
    public function syncDokkap($dokkaps) {
        // delete all that is not in the list
        $saved_ids = array_values(array_map(function($e) { return $e['id']; }, $dokkaps));

        $this->dokkap()->whereNotIn('id', $saved_ids)->delete();

        // iterate over all of them
        foreach ($dokkaps as $d) {
            // if it's new, save it
            if (!$d['id']) {
                $dokkap = new Dokkap([
                    'jenis_dokkap_id' => $d['jenis_dokkap_id'],
                    'nomor_lengkap_dok' => $d['nomor_lengkap'],
                    'tgl_dok' => $d['tgl_dok'],
                ]);

                $dokkap->master()->associate($this);
                $dokkap->save();
            } else {
                // update it, but check if it belongs to us!
                $dokkap = Dokkap::findOrFail($d['id']);

                // does it belong to us?
                if ($dokkap->master_type != get_class($this) || $dokkap->master_id != $this->id) {
                    // it doesnt
                    throw new \Exception("Dokkap #{$dokkap->id} does not belong to me!");
                } else {
                    // safe to continue
                    $dokkap->jenis_dokkap_id = $d['jenis_dokkap_id'];
                    $dokkap->nomor_lengkap_dok = $d['nomor_lengkap'];
                    $dokkap->tgl_dok = $d['tgl_dok'];

                    $dokkap->save();
                }
            }
        }
    }

    /**
     * copyDokkap
     */
    public function copyDokkap($s) {
        if (!method_exists($s, 'dokkap')) {
            throw new \Exception("Cannot copy dokkap. class type '". get_class($s) . "' doesnt support dokkap");
        }

        $ds = $s->dokkap;

        foreach ($ds as $d) {
            $s->dokkap()->save($d->replicate());
        }
    }
}
