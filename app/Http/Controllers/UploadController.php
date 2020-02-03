<?php

namespace App\Http\Controllers;

use App\CD;
use App\Dokkap;
use App\IS;
use App\Lampiran;
use App\SPMB;
use App\Transformers\LampiranTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends ApiController
{
    protected static $acceptedType = [
        'cd',
        'is',
        'spmb',
        'dokkap'
    ];

    public function handleUpload(Request $r, $doctype = null, $docid = null) {
        // use try catch?

        try {
            // the path
            $path       = $r->path();

            $master_type    = $doctype;
            $master_id      = $docid;
            $master         = null;

            // parse the master data?
            if (preg_match("/(\w+)\/(\d+)\/lampiran$/i", $path, $matches)) {
                $master_type    = $matches[1];
                $master_id      = $matches[2];

                // only valid type is 'cd', 'is', 'spmb', 'dokkap'
                if (!in_array($master_type, UploadController::$acceptedType)) {
                    throw new \Exception("Unacceptable master doctype: {$master_type}");
                }

                // now try to get it
                switch ($master_type) {
                    case 'cd':
                        $master = CD::find($master_id);
                        break;    
                    
                    case 'is':
                        $master = IS::find($master_id);
                        break;
                    
                    case 'spmb':
                        # code...
                        break;

                    case 'dokkap':
                        $master = Dokkap::find($master_id);
                        break;
                }

                // do we get it?
                if (!$master) {
                    throw new \Exception("Master doctype {$master_type} #{$master_id} not found, possibly user is drunk");
                } else {
                    // by default, we can upload
                    $canUpload  = true;

                    // but, gotta check the document's lock state too!
                    if ($master_type != 'dokkap') {
                        $canUpload  = canEdit( $master->is_locked, $r->userInfo);
                    } else {
                        $canUpload  = canEdit( $master->master->is_locked, $r->userInfo);
                    }

                    // if we cannot uplod, tell em
                    if (!$canUpload) {
                        throw new \Exception("Cannot add more attachment because document is already locked, or you have no sufficient privileges...");
                    }
                }
                
            } else {
                // assume error. tell em
                throw new \Exception("No attachable object of type: {$master_type}");
            }

            // read all data
            $body       = $r->getContent();

            $filename   = $r->header('X-Content-Filename');
            $filesize   = $r->header('X-Content-Filesize');

            $filetype   = $r->header('Content-Type');
            $blobsize   = $r->header('Content-Length') || strlen($body);

            // jenis file?
            $jenis_file = 'LAIN-LAIN';
            if (preg_match("/^image\/.*/i", $filetype)) {
                $jenis_file = "GAMBAR";
            } else if (preg_match("/^application\/pdf$/i", $filetype)) {
                $jenis_file = "DOKUMEN";
            }

            // parse base64 data
            $base64_data    = explode(',', $body);

            // for now, just store it somewhere
            $uniqueFilename = uniqid() . str_random() . $filename;
            Storage::disk('public')->put($uniqueFilename, base64_decode($base64_data[1]) );

            // generate lampiran object
            $l  = new Lampiran([
                'resumable_upload_id'   => '-',
                'jenis'                 => $jenis_file,
                'mime_type'             => $filetype,
                'filename'              => $filename,
                'filesize'              => $filesize,
                'diskfilename'          => $uniqueFilename,
                'blob'                  => $base64_data[1]
            ]);

            // attach it
            $master->lampiran()->save($l);

            return $this->respondWithArray([
                'id'        => $l->id,
                'path'      => $path,
                'jenis'     => $jenis_file,
                'filename'  => $filename,
                'diskfilename'  => $uniqueFilename,
                'filesize'  => $filesize,
                'blobsize'  => $blobsize,
                'mime_type' => $filetype,

                'master'    => [
                    'type'  => $master_type,
                    'id'    => $master_id
                ]
            ]);
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }

        
    }

    public function getFileUrl(Request $r) {
        $url = asset(Storage::url('Something.txt'));

        return response($url);
    }
    
    // Grab all  data lampiran
    public function getAttachments(Request $r, $doctype, $docid) {
        try {
            // grab the doc instance first
            $masterDoc = null;

            switch ($doctype) {
                case 'cd':
                    $masterDoc = CD::find($docid);
                    break;
                
                case 'is':
                    $masterDoc = IS::find($docid);
                    break;
                
                case 'spmb':
                    $masterDoc = SPMB::find($docid);
                    break;

                case 'dokkap':
                    $masterDoc = Dokkap::find($docid);
                    break;
                
                default:
                    # code...
                    break;
            }

            if (!$masterDoc) {
                throw new \Exception("Master doc {$doctype} #{$docid} was not found!");
            }

            // welp, it was found! return instances
            return $this->respondWithCollection($masterDoc->lampiran, new LampiranTransformer);
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
