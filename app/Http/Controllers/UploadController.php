<?php

namespace App\Http\Controllers;

use App\CD;
use App\Dokkap;
use App\IS;
use App\Lampiran;
use App\Pembatalan;
use App\Services\Instancer;
use App\SPMB;
use App\Transformers\LampiranTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Fractal\Manager;
use PDOException;

class UploadController extends ApiController
{
    protected static $acceptedType = [
        'cd'    => CD::class,
        'is'    => IS::class,
        'spmb'  => SPMB::class,
        'dokkap'    => Dokkap::class,
        'pembatalan'    => Pembatalan::class
    ];

    public function __construct(Instancer $instancer, Manager $fractal, Request $r)
    {
        parent::__construct($fractal, $r);

        // use instancer
        $this->instancer = $instancer;
    }

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
                // if (! key_exists($master_type, UploadController::$acceptedType)/* !in_array($master_type, UploadController::$acceptedType) */) {
                //     throw new \Exception("Unacceptable master doctype: {$master_type}");
                // }

                // now try to get it
                // $master = UploadController::$acceptedType[$master_type]::find($master_id);
                $master = $this->instancer->findOrFail($master_type, $master_id);

                // do we get it?
                if (!$master) {
                    throw new \Exception("Master doctype {$master_type} #{$master_id} not found, possibly user is drunk");
                } else {
                    // can it accept lampiran?
                    if (!method_exists($master, 'lampiran')) {
                        throw new \Exception("Object Type '" . get_class($master) . "' is not attachable!");
                    }
                    // by default, we can upload
                    $canUpload  = canEdit( $master->is_locked, $r->userInfo);
                    // $canUpload  = true;

                    // but, gotta check the document's lock state too!
                    // if ($master_type != 'dokkap') {
                        // $canUpload  = canEdit( $master->is_locked, $r->userInfo);
                    // } else {
                        // $canUpload  = canEdit( $master->master->is_locked, $r->userInfo);
                    // }

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
            $uniqueFilename = uniqid() . Str::random() . $filename;
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

            return $this->respondWithItem($l, new LampiranTransformer);

            /* return $this->respondWithArray([
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
            ]); */
        } catch (\InvalidArgumentException $e) {
            return $this->errorInternalServer($e->getMessage());
        } catch (PDOException $e) {
            return $this->errorBadRequest("File too large, bruh! 16 MB Maximum allowed.");
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
            $masterDoc = $this->instancer->findOrFail($doctype, $docid);

            /* switch ($doctype) {
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

                case 'pembatalan':
                    $masterDoc = Pembatalan::find($docid);
                    break;
                
                default:
                    # code...
                    break;
            } */

            if (!$masterDoc) {
                throw new \Exception("Master doc {$doctype} #{$docid} was not found!");
            }

            // welp, it was found! return instances
            return $this->respondWithCollection($masterDoc->lampiran, new LampiranTransformer);
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }


    // grab specific lampiran
    public function showAttachment(Request $r, $id) {
        $l = Lampiran::find($id);

        if (!$l) {
            return $this->errorNotFound("Lampiran #{$id} was not found.");
        }

        try {
            return $this->respondWithItem($l, new LampiranTransformer);
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // delete specific lampiran
    public function deleteAttachment(Request $r, $id) {
        $l = Lampiran::find($id);

        if (!$l) {
            return $this->errorNotFound("Lampiran #{$id} was not found.");
        }

        try {
            // attempt deletion here
            // first, make sure if we have parent
            if (!$l->Attachable) {
                // no parent, safe to delete
                $l->delete();

                return $this->setStatusCode(204)
                            ->respondWithEmptyBody();
            } else {
                // welp, we have parent. check if we're locked
                if ($l->Attachable->is_locked) {
                    throw new \Exception("Can't do that, our parent document is locked already!");
                }

                // safe to delete
                $l->delete();
                
                return $this->setStatusCode(204)
                            ->respondWithEmptyBody();
            }
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
