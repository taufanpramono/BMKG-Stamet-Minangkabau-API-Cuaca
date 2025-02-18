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

//ambil semua data 
$queryKec   = "SELECT * FROM wp0v_data_cuaca_kab";
$exKec      = mysqli_query($conn, $queryKec);
$arrayKab   = [];
while($rows = mysqli_fetch_assoc($exKec)) {
    $arrayKab[] = $rows;
}

// echo '<pre>';
// print_r($arrayKab);
// echo '</pre>';

$results = [];
foreach ($arrayKab as $entry) {

	$weatherData = json_decode($entry['av_cuaca'], true);
	foreach ($weatherData as $weather) {
            // Ambil tanggal dari local_datetime
        $date = date('Y-m-d', strtotime($weather['waktu']));
        if (!isset($results[$date])) {
                $results[$date] = [
                    'total_temperature' => 0,
                    'total_wind_speed' => 0,
                    'total_humidity' => 0,
                    'count' => 0,
                    'weather_descriptions' => []
                ];
        }

        // Tambahkan nilai ke total
        $results[$date]['total_temperature'] += $weather['av_temperature'];
        $results[$date]['total_wind_speed'] += $weather['av_windspeed'];
        $results[$date]['total_humidity'] += $weather['av_humidity'];
        $results[$date]['weather_descriptions'][] = $weather['av_description'];
        $results[$date]['count']++;

    }
}


$arrayCuacaProv = [];
    // Menyusun output akhir
    foreach ($results as $date => $result) {
        $averageTemperature = $result['total_temperature'] / $result['count'];
        $averageWindSpeed = $result['total_wind_speed'] / $result['count'];
        $averageHumidity = $result['total_humidity'] / $result['count'];
    
        // Menghitung deskripsi cuaca yang paling sering muncul
        $descriptionCount = array_count_values($result['weather_descriptions']);
        $averageWeatherDesc = array_search(max($descriptionCount), $descriptionCount);
        
        $arrayCuacaProv[] = [

            'waktu'             => $date,
            'av_temperature'    => round($averageTemperature, 2),
            'av_description'    => $averageWeatherDesc,
            'av_windspeed'      => round($averageWindSpeed, 2),
            'av_winddir'        => (isset($weather['av_winddir']) ? $weather['av_winddir'] : 'N/A'),
            'av_humidity'       => round($averageHumidity, 2)
        ];
    }

    $jsonCuacaProv = json_encode($arrayCuacaProv, true);


	//validasi cek data, jika ada cukup update, jika belum ready ganti ready
    $selectFirst   = "SELECT * FROM wp0v_data_cuaca_provinsi WHERE id_cuaca_prov = 1";
    $executeFirst  = mysqli_query($conn, $selectFirst);
    $fetchArr      = mysqli_fetch_assoc($executeFirst);
    $haveRows      = mysqli_num_rows($executeFirst);

    if($haveRows == 1) {

        $query_update = mysqli_query($conn, "UPDATE wp0v_data_cuaca_provinsi SET 

            data_cuaca_prov    = '$jsonCuacaProv',
            status             = 'pending'

            WHERE id_cuaca_prov = 1");

        if($query_update) {
            if(!empty($jsonCuacaProv)) {
                $id_prov = $fetchArr['id_cuaca_prov'];
                $query_after_update = mysqli_query($conn, "UPDATE wp0v_data_cuaca_provinsi SET status='ready' WHERE id_cuaca_prov = '$id_prov'");
                if($query_after_update) {
                    echo 'Update sukses <br>';
                }
                
            } else {
                echo 'Update gagal <br>';
            }
        } else {
            echo 'Update gagal <br>';
        }

    } else {

        $query         = "INSERT INTO wp0v_data_cuaca_provinsi (id_cuaca_prov, data_cuaca_prov, status) VALUES ('1','$jsonCuacaProv','pending')";
        $executeQuery  = mysqli_query($conn, $query);

        if($executeQuery) {
            if(!empty($jsonCuacaProv)) {
                $id_prov = mysqli_insert_id($conn);
                $query_after_update = mysqli_query($conn, "UPDATE wp0v_data_cuaca_provinsi SET status='ready' WHERE id_cuaca_prov = '$id_prov'");
                if($query_after_update) {
                    echo 'Upload Sukses <br>';
                }
                
            } else {
                echo 'Upload gagal <br>';
            }
            
        } else {
            echo 'Upload gagal <br>';
        }


    }





// echo '<pre>';
// print_r($arrayCuacaProv);
// echo '</pre>';








?>