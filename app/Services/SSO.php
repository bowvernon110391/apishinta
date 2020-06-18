<?php
namespace App\Services;

use Illuminate\Http\Request;
use Jasny\SSO\Broker;

class SSO {
    public $sso = null;

    public function __construct(Request $r)
    {
        $this->sso = new Broker(
            'http://sso.soetta.xyz/',
            '5',
            '5h1n74aPPs'
        ); 

        // auto set token
        $this->sso->token = $r->bearerToken();
    }

    public function getUserInfo($accessToken) {
        // $this->sso->token = $accessToken;

        // call get user
        try {
            $userInfo = $this->sso->getUserInfo();
            return $userInfo;
        } catch (\Exception $e) {
            // echo "getUserInfo error: {$e->getMessage()}";
            return null;
        }
    }

    public function __call($fn, $args) {
        return $this->sso->__call($fn, $args);
    }
}