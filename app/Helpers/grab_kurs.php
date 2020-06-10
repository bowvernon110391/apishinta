<?php

if (!function_exists('grabsKursData')) {
	// buat konversi dari teks ke kode bulan

	// // database
	// $host = "localhost";
	// $dbname = "db_pibk2019";
	// $username = "kurs_updater";
	// $password = "123456";

	/*
	grabKursData
		fungsi ini ngambil data dari alamat kurs pajak, dan ngembaliin data yg siap dipake
	*/
	function grabKursData() {	
		$monthLookup = array(
			'Januari'	=> '01',
			'January'	=> '01',
			'Februari'	=> '02',
			'February'	=> '02',
			'Maret'		=> '03',
			'March'		=> '03',
			'April'		=> '04',
			'Mei'		=> '05',
			'May'		=> '05',
			'Juni'		=> '06',
			'June'		=> '06',
			'Juli'		=> '07',
			'July'		=> '07',
			'Agustus'	=> '08',
			'August'	=> '08',
			'September'	=> '09',
			'Oktober'	=> '10',
			'October'	=> '10',
			'Nopember'	=> '11',
			'November'	=> '11',
			'Desember'	=> '12',
			'December'	=> '12'
			);	
		// ssl context (BYPASS SSL)
		$arrContextOptions=array(
			"ssl"=>array(
				"cafile" => "/home/services/kurs_kemenkeu.crt",
				"verify_peer"=>false,
				"verify_peer_name"=>false,
			),
		);  
		// source data
		try {
			//code...
			$html = file_get_contents('https://fiskal.kemenkeu.go.id/informasi-publik/kurs-pajak', false, stream_context_create($arrContextOptions) );
		} catch (\Exception $e) {
			// return empty data, will be interpreted as service unavailable
			return null;
		}
		

		// echo $html;

		// grab tanggal awal dan akhir
		$patTanggal = '/Tanggal Berlaku\:\s(\d{1,2})\s+(\w+)\s+(\d{4})\s\-\s(\d{1,2})\s+(\w+)\s+(\d{4})/i';


		$result = preg_match($patTanggal, $html, $matches);

		// var_dump($matches);

		if (count($matches) >= 6) {
			// fix tanggal
			if (strlen($matches[1]) == 1)
				$matches[1] = '0'.$matches[1];

			if (strlen($matches[4]) == 1)
				$matches[4] = '0'.$matches[4];	

			$retData = array(
				'dateStart'	=> $matches[3].'-'.$monthLookup[$matches[2]].'-'.$matches[1],
				'dateEnd'	=> $matches[6].'-'.$monthLookup[$matches[5]].'-'.$matches[4],
				'data' => array()
				);
		} else
			return null;

		// grab data asli (KODE KURS + NILAI TUKARNYA)
		// $patKurs = '/\(([A-Z]{3})\).+.+>(.+)\s<img/';
		$patKurs = '/\((\w{3})\)<\/td>\s+<td.+>\s+<img.+\/>\s+([0-9\,\.]+)<\/td>/';

		$result = preg_match_all($patKurs, $html, $matches);

		// var_dump($matches);

		if (count($matches) > 2) {
			// dump it all?
			for ($i = 0; $i < count($matches[0]); $i++) {
				$kdValuta = $matches[1][$i];

				// FIX: AUTO-DETECT NUMBER FORMAT. CHECK RIGHT MOST
				$comma = substr($matches[2][$i], -3, 1);

				if ($comma == ',') {
					// some idiot at BKF decides to use comma as decimal separator					
					$nilai = str_replace('.', '', $matches[2][$i]);
					$nilai = str_replace(',', '.', $nilai);
				} else if ($comma == '.') {
					// the usual format. just remove all commas
					$nilai = str_replace(',', '', $matches[2][$i]);
				} else {
					// must be error. throw something
					throw new \Exception("Unknown decimal separator '{$comma}'", 400);
				}
				

				$kurs = $nilai * 1;

				// for JPY, divide further by 100
				if ($kdValuta == 'JPY')
					$kurs /= 100.0;

				// echo $kdValuta . ' = ' . sprintf("%.4f", $kurs) . "\n";

				// just plug it in I guess
				$retData['data'][$kdValuta] = sprintf("%.4f", $kurs);
			}	
		} else
			return null;

		if (isset($retData))
			return $retData;

		return null;
	}
}



// var_dump(grabKursData());
// print_r(grabKursData());

// TODO:
//	1. buat fungsi utk update database (panggil grabKursData, cek return value, update db)
//	2. buat file ini berjalan secara periodik

/* $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, array(PDO::ATTR_EMULATE_PREPARES=>false, PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));

try {
	$db->beginTransaction();

	$qstring = "UPDATE
					cd_valas a
				SET
					a.tgl_awal = :dateStart,
					a.tgl_akhir = :dateEnd,
					a.kurs_valas = :kursValas
				WHERE
					a.id_valas = :valuta
				LIMIT
					1;";

	$stmt = $db->prepare($qstring);

	// grab kurs data
	$kursData = grabKursData();

	foreach ($kursData['data'] as $valuta => $kursValue) {
		$result = $stmt->execute(array(
			':dateStart'	=> $kursData['dateStart'],
			':dateEnd'		=> $kursData['dateEnd'],
			':kursValas'	=> $kursValue,
			':valuta'		=> $valuta
			));

		if ($result)
			echo "$valuta UPDATED => $kursValue\r\n";
	}

	$db->commit();
} catch (PDOException $e) {
	$db->rollback();
	echo "Error: ".$e->getMessage();
} */

?>