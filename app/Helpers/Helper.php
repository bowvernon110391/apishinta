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

// function: getUserInfo(access_token) 
// desc: mengambil data user dari SSO menggunakan jasny/sso library
// return: data user dalam array
if (!function_exists('getUserInfo')) {
    // declare
    function getUserInfo($access_token) {
        // cek environment, untuk development gunakan mockup
        if (App::environment('production')) {
            // belum didefinisikan, jadi trigger error aja heheh 
            // nanti klo udh integrasi, taro di sini
            // required: secret_id, secret_key, trus bkin token buat 
            //              request ke SSO
            trigger_error("Belum diintegrasikan dengan SSO!", E_USER_ERROR);
        } 

        // mockup token dengan mockup data usernya
        $mockupUsers = [
            // mockup profil pdtt
            'token_pdtt'  => [
                'id'    => 2,
                'nama'  => 'FAKE PDTT',
                'nip'   => 'XXX6666XXX',
                'roles' => [
                    'PDTT'
                ]
            ],

            // mockup profil admin
            'token_admin' => [
                'id'    => 1,
                'nama'  => 'FAKE ADMIN',
                'nip'   => '45462626',
                'roles' => [
                    'PDTT',
                    'KASI',
                    'P2_XRAY',
                    'CONSOLE'
                ]
            ]
        ];

        // ambil data, atau return null klo gk ketemu
        if (!array_key_exists($access_token, $mockupUsers)) {
            return null;
        }

        // ada
        return $mockupUsers[$access_token];
    }
}

// function: userHasRole(user_info) 
// desc: ngecek apakah user punya role? parameter ambil dr getUserInfo($access_token)
// return: true klo punya, false klo enggak
if (!function_exists('userHasRole')) {
    // declare function
    function userHasRole($roleName, $userInfo = null) {
        // empty data means false
        if (!$userInfo) {
            return false;
        }

        // check existence of key 'role'
        if (!array_key_exists('roles', $userInfo)) {
            return false;
        }

        // okay, find it
        return in_array($roleName, $userInfo['roles']);
    }
}

?>