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

// URL API
$url = 'https://stamet-minangkabau-bmkg.my.id/wp-json/csv-api/v1/get-data?kodes=13';

// Mengambil data dari URL
$response = file_get_contents($url);

if ($response === FALSE) {
    die('Gagal mengambil data.');
}

// Mendekode JSON response
$arrayData = json_decode($response, true);

if (!$arrayData) {
    die('Data tidak valid.');
}

// Batasi jumlah yang diambil
$max_kab = 5000;  // Jumlah kabupaten yang diambil
$desa_list = [];
$count_kab = 0;

foreach ($arrayData['13']['kabupaten_kota'] as $kabupaten) {
    if ($count_kab >= $max_kab) {
        break;
    }
    $count_kab++;

    foreach ($kabupaten['kecamatan'] as $kecamatan) {
        foreach ($kecamatan['desa'] as $kode_desa => $desa) {
            $desa_list[] = [
                'kode_wilayah' => htmlspecialchars($kode_desa, ENT_QUOTES, 'UTF-8'), // Escape output
            ];
        }
    }
}

// Output dalam format array JSON
$jsonData = json_encode($desa_list, true);

if ($jsonData) {

    $stmt = $conn->prepare("SELECT * FROM wp0v_data_wilayah WHERE id = ?");
    $id = 2;
    $stmt->bind_param("i", $id); // Bind parameter
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->num_rows;

    if ($rows == 1) {
        // Update data
        $stmt = $conn->prepare("UPDATE wp0v_data_wilayah SET wilayahJSON = ? WHERE id = ?");
        $stmt->bind_param("si", $jsonData, $id); // Bind parameter
        if ($stmt->execute()) {
            echo 'Pembaruan data berhasil';
        } else {
            echo 'Pembaruan gagal: ' . $stmt->error;
        }
    } else {
        // Insert data
        $stmt = $conn->prepare("INSERT INTO wp0v_data_wilayah (id, wilayahJSON) VALUES (?, ?)");
        $stmt->bind_param("is", $id, $jsonData); // Bind parameter
        if ($stmt->execute()) {
            echo 'Simpan data berhasil';
        } else {
            echo 'Penyimpanan gagal: ' . $stmt->error;
        }
    }

    $stmt->close();
}

$conn->close();
?>
