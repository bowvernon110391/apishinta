<?php

use Jasny\SSO\Broker;
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
            // trigger_error("Belum diintegrasikan dengan SSO!", E_USER_ERROR);

            $broker = new Broker(
                'http://sso.soetta.xyz/',
                '5',
                '5h1n74aPPs'
            );

            // set token
            $broker->token = $access_token;

            // call get user
            try {
                $userInfo = $broker->getUserInfo();
                return $userInfo;
            } catch (\Exception $e) {
                // echo "getUserInfo error: {$e->getMessage()}";
                return null;
            }
        } 

        // mockup token dengan mockup data usernya
        $mockupUsers = [
            // mockup profil pdtt
            'token_pdtt'  => [
                "user_id" => "572",
                "username" => "setiadi.001",
                "name" => "Setiadi",
                "nip" => "198911102010011001",
                "pangkat" => "Pengatur - II/c",
                "status" => true,
                "apps_data" => [
                  3 => [
                    "app_name" => "AKANG",
                    "roles" => [
                      "PEMERIKSA",
                    ],
                  ],
                  5 => [
                    "app_name" => "SiBAPE",
                    "roles" => [
                      "PDTT",
                    ],
                  ],
                ],
              ],

            // mockup profil admin
            'token_admin' => [
                "user_id" => "612",
                "username" => "tri.mulyadi",
                "name" => "Tri Mulyadi Wibowo",
                "nip" => "199103112012101001",
                "pangkat" => "Penata Muda - III/a",
                "status" => true,
                "apps_data" => [
                  1 => [
                    "app_name" => "SSO",
                    "roles" => [
                      "Administrator",
                    ],
                  ],
                  2 => [
                    "app_name" => "APPFOTO",
                    "roles" => [
                      "Administrator",
                    ],
                  ],
                  3 => [
                    "app_name" => "AKANG",
                    "roles" => [
                      "PJT",
                      "ADMIN_PABEAN",
                      "SUPERUSER",
                    ],
                  ],
                  5 => [
                    "app_name" => "SiBAPE",
                    "roles" => [
                      "KASI",
                      "CONSOLE",
                    ],
                  ],
                ],
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