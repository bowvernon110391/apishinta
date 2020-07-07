<?php
namespace App;

trait TraitTariffable {
    public function tarif()
    {
        return $this->morphMany(Tarif::class, 'tariffable');
    }

    // this will syncTarif
    public function syncTarif($tarifs) {
        // delete all that is not in the list
        $saved_ids = array_values(array_map(function($e) { return $e['id']; }, $tarifs));

        $this->tarif()->whereNotIn('id', $saved_ids)->delete();

        // iterate over all of them
        foreach ($tarifs as $t) {
            // if it's new, save it
            if (!$t['id']) {
                $tarif = new Tarif([
                    'jenis_pungutan_id' => $t['jenis_pungutan_id'],
                    'jenis' => $t['jenis'],
                    'tarif' => $t['tarif'],
                    'bayar' => $t['bayar'],
                    'bebas' => $t['bebas'],
                    'tunda' => $t['tunda'],
                    'tanggung_pemerintah' => $t['tanggung_pemerintah'],
                    'overridable' => $t['overridable']
                ]);

                $tarif->tariffable()->associate($this);
                $tarif->save();
            } else {
                // update it, but check if it belongs to me
                $tarif = Tarif::findOrFail($t['id']);

                // does it belong to us?
                if ($tarif->tariffable_type != get_class($this) || $tarif->tariffable_id != $this->id) {
                    // it doesnt
                    $reason = "{$tarif->tariffable_type} != " . get_class($this) .", ";
                    $reason.= "{$tarif->tariffable_id} != " . $this->id;
                    throw new \Exception("Tarif #{$tarif->id} does not belong to this tariffable. reason: " . $reason);
                } else {
                    // safe to continue
                    $tarif->jenis_pungutan_id = $t['jenis_pungutan_id'];
                    $tarif->jenis = $t['jenis_pungutan_id'] >= 2 && $t['jenis_pungutan_id'] <=5 ? $t['jenis'] : null;
                    $tarif->tarif = $t['tarif'];
                    $tarif->bayar = $t['bayar'];
                    $tarif->bebas = $t['bebas'];
                    $tarif->tunda = $t['tunda'];
                    $tarif->tanggung_pemerintah = $t['tanggung_pemerintah'];
                    $tarif->overridable = $t['overridable'];

                    $tarif->save();
                }
            }
        }
    }
}