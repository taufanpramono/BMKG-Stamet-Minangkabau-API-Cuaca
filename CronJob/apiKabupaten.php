<?php 

// Database configuration
$servername = "localhost";
$username = "";
$password = "";
$dbname = "";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$loc = [

	'Pesisir Selatan',
	'Solok',
	'Sijunjung',
	'Tanah Datar',
	'Padang Pariaman',
	'Agam',
	'Lima Puluh Kota',
	'Pasaman',
	'Kepulauan Mentawai',
	'Dharmasraya',
	'Solok Selatan',
	'Pasaman Barat',
	'Kota Padang',
	'Kota Solok',
	'Kota Sawahlunto',
	'Kota Padang Panjang',
	'Kota Bukittinggi',
	'Kota Payakumbuh',
	'Kota Pariaman' ];


foreach ($loc as $loca) {

	$check      = "SELECT * FROM wp0v_data_cuaca WHERE nama_kabkota='$loca'";
	$queryStart = mysqli_query($conn, $check);
	$dataJson   = [];
	while($row = mysqli_fetch_assoc($queryStart)) {
		$dataJson[] = $row;
	}



	// Inisialisasi variabel untuk menyimpan hasil
	$results = [];

	// Proses setiap data cuaca
	foreach ($dataJson as $entry) {
    // Decode data cuaca dari JSON
    	$weatherData = json_decode($entry['data_cuaca'], true);
    
    		foreach ($weatherData as $weather) {
        	// Ambil tanggal dari local_datetime
        	$date = date('Y-m-d', strtotime($weather['local_datetime']));
        
        	// Inisialisasi array untuk tanggal jika belum ada
       		 if (!isset($results[$date])) {
            	$results[$date] = [
                	'nama_kab' => $entry['nama_kabkota'],
                	'total_temperature' => 0,
                	'total_wind_speed' => 0,
                	'total_humidity' => 0,
                	'count' => 0,
                	'weather_descriptions' => []
            	];
        	}
        
        	// Tambahkan nilai ke total
        	$results[$date]['total_temperature'] += $weather['temperature'];
        	$results[$date]['total_wind_speed'] += $weather['wind_speed'];
        	$results[$date]['total_humidity'] += $weather['humidity'];
        	$results[$date]['weather_descriptions'][] = $weather['weat_desc'];
        	$results[$date]['count']++;
    	}
	}	



	$arrayCuacaKab = [];
	// Menyusun output akhir
	foreach ($results as $date => $result) {
    	$averageTemperature = $result['total_temperature'] / $result['count'];
    	$averageWindSpeed = $result['total_wind_speed'] / $result['count'];
    	$averageHumidity = $result['total_humidity'] / $result['count'];
    
    	// Menghitung deskripsi cuaca yang paling sering muncul
    	$descriptionCount = array_count_values($result['weather_descriptions']);
    	$averageWeatherDesc = array_search(max($descriptionCount), $descriptionCount);
    	
   		$arrayCuacaKab[] = [

   			'waktu'             => $date,
   			'av_temperature' 	=> round($averageTemperature, 2),
   			'av_description'    => $averageWeatherDesc,
   			'av_windspeed'      => round($averageWindSpeed, 2),
   			'av_winddir'		=> (isset($weather['wind_direction']) ? $weather['wind_direction'] : 'N/A'),
   			'av_humidity'       => round($averageHumidity, 2)
   		];
	}



	$jsonCuacaKab = json_encode($arrayCuacaKab, true);

	 //validasi cek data, jika ada cukup update, jika belum ready ganti ready
    $selectFirst   = "SELECT * FROM wp0v_data_cuaca_kab WHERE nama_kab='$loca'";
    $executeFirst  = mysqli_query($conn, $selectFirst);
    $fetchArr      = mysqli_fetch_assoc($executeFirst);
    $haveRows      = mysqli_num_rows($executeFirst);

    if($haveRows == 1) {

        $query_update = mysqli_query($conn, "UPDATE wp0v_data_cuaca_kab SET 

            av_cuaca           = '$jsonCuacaKab',
            status             = 'pending'

            WHERE nama_kab = '$loca'");

        if($query_update) {
            $id_kab = $fetchArr['id_kab'];
            $query_after_update = mysqli_query($conn, "UPDATE wp0v_data_cuaca_kab SET status='ready' WHERE id_kab = '$id_kab'");
            if($query_after_update) {
                echo 'Update sukses : '.$loca. '<br>';
            }
           
        } else {
            echo 'Update gagal : '. $loca. '<br>';
        }

    } else {

        $query         = "INSERT INTO wp0v_data_cuaca_kab (nama_kab, av_cuaca, status) VALUES ('$loca','$jsonCuacaKab','pending')";
        $executeQuery  = mysqli_query($conn, $query);

        if($executeQuery) {
            $id_kab = mysqli_insert_id($conn);
            $query_after_update = mysqli_query($conn, "UPDATE wp0v_data_cuaca_kab SET status='ready' WHERE id_kab = '$id_kab'");
            if($query_after_update) {
                 echo 'Upload Sukses : '. $loca. '<br>';
            }
            
        } else {
            echo 'Upload gagal : '. $loca. '<br>';
        }


    }


	// echo $loca.'<br>';
	// echo $jsonCuacaKab.'<br>';

}







?>