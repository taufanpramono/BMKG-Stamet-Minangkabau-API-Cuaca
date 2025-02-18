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
$queryKec   = "SELECT * FROM wp0v_data_cuaca";
$exKec      = mysqli_query($conn, $queryKec);
$arrayKec   = [];
while($rows = mysqli_fetch_assoc($exKec)) {
    $arrayKec[] = $rows;
}

//ambil semua nama kecamatan & kabupaten
$allKec = [];
foreach ($arrayKec as $kec) {
    $allkec[] = [
        'nama_kec' => $kec['nama_kecamatan'],
        'nama_kab' => $kec['nama_kabkota']
    ];
}

// echo '<pre>';
// print_r($allkec);
// echo '</pre>';

$ambilsatu = $uniqueData = array_map("unserialize", array_unique(array_map("serialize", $allkec)));

// echo '<pre>';
// print_r($ambilsatu);
// echo '</pre>';


foreach ($ambilsatu as $datkec) {
    $namaKec    = $datkec['nama_kec'];
    $namaKab    = $datkec['nama_kab'];
    $check      = "SELECT * FROM wp0v_data_cuaca WHERE nama_kecamatan ='$namaKec'";
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



    $arrayCuacaKec = [];
    // Menyusun output akhir
    foreach ($results as $date => $result) {
        $averageTemperature = $result['total_temperature'] / $result['count'];
        $averageWindSpeed = $result['total_wind_speed'] / $result['count'];
        $averageHumidity = $result['total_humidity'] / $result['count'];
    
        // Menghitung deskripsi cuaca yang paling sering muncul
        $descriptionCount = array_count_values($result['weather_descriptions']);
        $averageWeatherDesc = array_search(max($descriptionCount), $descriptionCount);
        
        $arrayCuacaKec[] = [

            'waktu'             => $date,
            'av_temperature'    => round($averageTemperature, 2),
            'av_description'    => $averageWeatherDesc,
            'av_windspeed'      => round($averageWindSpeed, 2),
            'av_winddir'        => (isset($weather['wind_direction']) ? $weather['wind_direction'] : 'N/A'),
            'av_humidity'       => round($averageHumidity, 2)
        ];
    }


    $jsonCuacaKec = json_encode($arrayCuacaKec, true);
    
    // echo $namaKab.'<br>';
    // echo $namaKec. '<br>';
    // echo $jsonCuacaKec.'<br>';



    //validasi cek data, jika ada cukup update, jika belum ready ganti ready
    $selectFirst   = "SELECT * FROM wp0v_data_cuaca_kec WHERE nama_kec='$namaKec'";
    $executeFirst  = mysqli_query($conn, $selectFirst);
    $fetchArr      = mysqli_fetch_assoc($executeFirst);
    $haveRows      = mysqli_num_rows($executeFirst);

    if($haveRows == 1) {

        $query_update = mysqli_query($conn, "UPDATE wp0v_data_cuaca_kec SET 

            nama_kab           = '$namaKab',
            data_cuaca_kec     = '$jsonCuacaKec',
            status             = 'pending'

            WHERE nama_kec = '$namaKec'");

        if($query_update) {
            if(!empty($namaKec)) {
                $id_kec = $fetchArr['id_cuaca_kec'];
                $query_after_update = mysqli_query($conn, "UPDATE wp0v_data_cuaca_kec SET status='ready' WHERE id_cuaca_kec = '$id_kec'");
                if($query_after_update) {
                    echo 'Update sukses : '.$namaKec. '<br>';
                }
                
            } else {
                echo 'Update gagal : '. $namaKec. '<br>';
                continue;
            }
        } else {
            echo 'Update gagal : '. $namaKec. '<br>';
            continue;
        }

    } else {

        $query         = "INSERT INTO wp0v_data_cuaca_kec (nama_kab, nama_kec, data_cuaca_kec, status) VALUES ('$namaKab','$namaKec','$jsonCuacaKec','pending')";
        $executeQuery  = mysqli_query($conn, $query);

        if($executeQuery) {
            if(!empty($namaKec)) {
                $id_kec = mysqli_insert_id($conn);
                $query_after_update = mysqli_query($conn, "UPDATE wp0v_data_cuaca_kec SET status='ready' WHERE id_cuaca_kec = '$id_kec'");
                if($query_after_update) {
                    echo 'Upload Sukses : '. $namaKec. '<br>';
                }
                
            } else {
                echo 'Upload gagal : '. $namaKec. '<br>';
                continue;
            }
            
        } else {
            echo 'Upload gagal : '. $namaKec. '<br>';
            continue;
        }


    }
  







    
    // echo '<pre>';
    // print_r($arrayCuacaKab);
    // echo '</pre>';

}


 ?>