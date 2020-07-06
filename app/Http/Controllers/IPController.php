<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\InstruksiPemeriksaan;
use App\Services\Instancer;
use App\SSOUserCache;
use App\Transformers\InstruksiPemeriksaanTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use League\Fractal\Manager;

class IPController extends ApiController
{
    // our constructor. inject instancer too
    public function __construct(Manager $fractal, Request $r, Instancer $instancer)
    {
        parent::__construct($fractal, $r);
        $this->instancer = $instancer;
    }
    // browse
    public function index(Request $r)
    {
        $q = $r->get('q');

        $query = InstruksiPemeriksaan::query()
            ->when($q, function ($query) use ($q) {
                $query->byIssuer($q);
            })
            ->when($r->get('self'), function ($query) use ($r) {
                if (is_numeric($r->get('self'))) {
                    $query->byPemeriksaId((int) $r->get('self'));
                } else {
                    $query->byPemeriksaId($r->userInfo['user_id']);
                }
            })
            ->latest()
            ->orderBy('id', 'desc');

        $paginator = $query
            ->paginate($r->get('number'))
            ->appends($r->except('page'));

        // build output
        return $this->respondWithPagination($paginator, new InstruksiPemeriksaanTransformer);
    }

    // store ip to a document
    public function store(Request $r, $doctype, $docid)
    {
        try {
            $doc = $this->instancer->findOrFail($doctype, $docid);

            // does it implement instruksiPemeriksaan?
            if (!method_exists($doc, 'instruksiPemeriksaan')) {
                throw new \Exception("Document type '$doctype' is not supported for this operation: (spawning Instruksi Pemeriksaan)");
            }

            // read data
            $jumlah_periksa = expectSomething($r->get('jumlah_periksa'), 'Jumlah Periksa');
            $ajukan_contoh = expectSomething($r->get('ajukan_contoh'), 'Ajukan Contoh');
            $ajukan_foto = expectSomething($r->get('ajukan_foto'), 'Ajukan Foto');
            $pemeriksa_id = expectSomething($r->get('pemeriksa_id'), 'ID Pemeriksa');

            // gotta check pemeriksa_id too (auto throw exception)
            $pemeriksa = SSOUserCache::byId($pemeriksa_id);

            // save, let's attach...or save?
            if (($ip = $doc->instruksiPemeriksaan) != null) {
                // update here...check if lhp already closed
                if (($lhp = $ip->lhp) != null) {
                    // sudah diambil pemeriksa
                    throw new \Exception("LHP sedang dalam proses perekaman, IP tidak dapat diterbitkan");
                }

                // save, let's update the data
                $ip->{'nama_issuer'}   = $r->userInfo['name'];
                $ip->{'nip_issuer'}    = $r->userInfo['nip'];
                $ip->{'jumlah_periksa'} = $jumlah_periksa;
                $ip->{'ajukan_contoh'} = $ajukan_contoh;
                $ip->{'ajukan_foto'} = $ajukan_foto;
                $ip->{'pemeriksa_id'} = $pemeriksa_id;

                $ip->tgl_dok    = date('Y-m-d');

                $ip->save();

                $ip->appendStatus('MODIFIED', null, "Diterbitkan ulang oleh {$r->userInfo['username']}");
                AppLog::logInfo("IP #{$ip->id} diterbitkan ulang oleh {$r->userInfo['username']}", $ip);
            } else {
                // create new IP
                $ip = new InstruksiPemeriksaan([
                    'nama_issuer'   => $r->userInfo['name'],
                    'nip_issuer'    => $r->userInfo['nip'],
                    'jumlah_periksa' => $jumlah_periksa,
                    'ajukan_contoh' => $ajukan_contoh,
                    'ajukan_foto' => $ajukan_foto,
                    'pemeriksa_id' => $pemeriksa_id,

                    'tgl_dok'   => date('Y-m-d')
                ]);

                $doc->instruksiPemeriksaan()->save($ip);
                $ip->refresh();

                // gotta append log?
                $doc->appendStatus('INSTRUKSI_PEMERIKSAAN', null, null, $ip, 'instruksi_pemeriksaan');
                $ip->appendStatus('CREATED', null, null, $doc, 'instructable');
                AppLog::logInfo("IP #{$ip->id} diterbitkan oleh {$r->userInfo['username']}", $ip);

                $ip->lockAndSetNumber();    // lock it so it gets numbered?
            }


            // success, let's return its id?
            return $this->respondWithArray([
                'id'    => $ip->id,
                'uri'   => $ip->uri
            ]);
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // show specific ip
    public function show(Request $r, $id)
    {
        try {
            $ip = InstruksiPemeriksaan::findOrFail($id);

            return $this->respondWithItem($ip, new InstruksiPemeriksaanTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("IP #{$id} was not found");
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
