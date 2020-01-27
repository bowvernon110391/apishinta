<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends ApiController
{
    public function handleUpload(Request $r) {
        // for now, just store it somewhere
        $uniqueFilename = uniqid() . str_random() . "Something.txt";
        Storage::disk('public')->put($uniqueFilename, 'Some shieeet bieeetch');
    }

    public function getFileUrl(Request $r) {
        $url = asset(Storage::url('Something.txt'));

        return response($url);
    }   
}
