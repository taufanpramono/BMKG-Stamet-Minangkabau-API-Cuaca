<?php 

// Database configuration
$servername = "localhost";
$username = "idwordpr_wp852";
$password = "4(Q3p6xF]S";
$dbname = "idwordpr_wp852";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$check      = "SELECT * FROM wp0v_data_wilayah WHERE id=2";
$queryStart = mysqli_query($conn, $check);
$rows       = mysqli_fetch_assoc($queryStart);
$json       = json_decode($rows['wilayahJSON']);

// print_r($json);
// $sample     = $json[0]->kode_wilayah;
// $asRay      = ['13.01.01.2002','13.01.01.2001'];


foreach ($json  as $kod) {
    // var_dump($kod);

    $url        = 'https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4='.$kod->kode_wilayah;
    $response = file_get_contents($url);
    if ($response === FALSE) {
        echo 'KODE WILAYAH ERROR : '. $kod->kode_wilayah.' API NO RESPONSES <br>';
        continue;
    }

    // Mendekode JSON response
    $arrayData = json_decode($response, true);

        // Menampilkan informasi lokasi
     $lokasi = $arrayData['lokasi'];
     $kode      = $lokasi['adm4'];
     $desa      = $lokasi['desa'];
     $kecamatan = $lokasi['kecamatan'];
     $kabkota   = $lokasi['kotkab'];


        $arrayWeather = [];
        // Memeriksa apakah ada data cuaca
        if (isset($arrayData['data']) && is_array($arrayData['data'])) {
            foreach ($arrayData['data'] as $data) {
                if (isset($data['cuaca']) && is_array($data['cuaca'])) {
                    foreach ($data['cuaca'] as $cuaca) {
                        foreach ($cuaca as $weather) {

                            $arrayLos = [
                                'local_datetime'     => $weather['local_datetime'],
                                'temperature'        => $weather['t'],
                                'weat_desc'          => $weather['weather_desc'],
                                'wind_direction'     => $weather['wd'],
                                'wind_speed'         => $weather['ws'],
                                'humidity'           => $weather['hu'],
                                'icon'               => $weather['image'],
                            ];

                        $arrayWeather[] = $arrayLos;
                    
                    }

                }
            } else {
                echo "Data cuaca tidak tersedia.<br>";
            }
        }
    } else {
        echo "Data cuaca tidak tersedia.<br>";
    }

    $weatherJson = json_encode($arrayWeather, true);


    //validasi cek data, jika ada cukup update, jika belum ready ganti ready
    $selectFirst   = "SELECT * FROM wp0v_data_cuaca WHERE kode_wilayah='$kode'";
    $executeFirst  = mysqli_query($conn, $selectFirst);
    $fetchArr      = mysqli_fetch_assoc($executeFirst);
    $haveRows      = mysqli_num_rows($executeFirst);
    $datetoday     = date('d-m-Y H:i');

    if($haveRows == 1) {

        $query_update = mysqli_query($conn, "UPDATE wp0v_data_cuaca SET 

            nama_desa          = '$desa',
            nama_kecamatan     = '$kecamatan',
            nama_kabkota       = '$kabkota',
            data_cuaca         = '$weatherJson',
            update_date        = '$datetoday',
            status             = 'pending'

            WHERE kode_wilayah = '$kode'");

        if($query_update) {
            $id_cuaca = $fetchArr['id_cuaca'];
            $query_after_update = mysqli_query($conn, "UPDATE wp0v_data_cuaca SET status='ready' WHERE id_cuaca = '$id_cuaca'");
            echo 'Update sukses : '. $kode. ' - '. $desa.' - Update : '. $datetoday .'<br>';
        } else {
        echo 'Update gagal : '. $kode. ' - '. $desa.' - Update : '. $datetoday .'<br>';
        }

    } else {

        $query         = "INSERT INTO wp0v_data_cuaca (kode_wilayah, nama_desa, nama_kecamatan, nama_kabkota, data_cuaca, update_date, status) VALUES ('$kode','$desa','$kecamatan','$kabkota','$weatherJson','$datetoday','pending')";
        $executeQuery  = mysqli_query($conn, $query);

        if($executeQuery) {
            $id_cuaca = mysqli_insert_id($conn);
            $query_after_update = mysqli_query($conn, "UPDATE wp0v_data_cuaca SET status='ready' WHERE id_cuaca = '$id_cuaca'");
             echo 'Upload Sukses : '. $kode. ' - '. $desa.' - Update : '. $datetoday .'<br>';
        } else {
        echo 'Upload gagal : '. $kode. ' - '. $desa.' - Update : '. $datetoday .'<br>';
        }


    }


// echo '<pre>';
// print_r($arrayWeather);
// echo '</pre>';

}










?>