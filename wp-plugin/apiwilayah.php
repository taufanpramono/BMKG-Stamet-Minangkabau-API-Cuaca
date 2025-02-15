<?php


//  JSON API WILAYAH
add_action('rest_api_init', function () {
    register_rest_route('csv-api/v1', '/get-data/', array(
        'methods' => 'GET',
        'callback' => 'csv_api_get_data',
        'permission_callback' => '__return_true', 
    ));
});


// Function to handle the API request
function csv_api_get_data(WP_REST_Request $request) {
    $csvUrl = 'https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/kode_wilayah.csv'; 
    $fileData = file_get_contents($csvUrl);
    $theCode = $request->get_param('kodes'); 

    if ($fileData !== false) {
        $lines = explode("\n", $fileData);
        $result = [];

        foreach ($lines as $line) {
            $data = str_getcsv($line);

            // Ensure the data is not empty and has at least two columns
            if (!empty($data[0]) && !empty($data[1])) {
                $kode = trim($data[0]);
                $nama = trim($data[1]);

                // Only fetch regions starting with '14' (for example, Sumatera Barat)
                if (strpos($kode, $theCode) === 0) {
                    $kodeParts = explode('.', $kode);

                    if (count($kodeParts) == 1) {
                        // Province
                        $result[$kode] = [
                            'nama_wilayah' => $nama,
                            'kabupaten_kota' => []
                        ];
                    } elseif (count($kodeParts) == 2) {
                        // City/District
                        $provKode = $kodeParts[0];
                        $result[$provKode]['kabupaten_kota'][$kode] = [
                            'nama_wilayah' => $nama,
                            'kecamatan' => []
                        ];
                    } elseif (count($kodeParts) == 3) {
                        // Subdistrict
                        $provKode = $kodeParts[0];
                        $kabKode = "{$kodeParts[0]}.{$kodeParts[1]}";
                        $result[$provKode]['kabupaten_kota'][$kabKode]['kecamatan'][$kode] = [
                            'nama_wilayah' => $nama,
                            'desa' => []
                        ];
                    } elseif (count($kodeParts) == 4) {
                        // Village/Suburb
                        $provKode = $kodeParts[0];
                        $kabKode = "{$kodeParts[0]}.{$kodeParts[1]}";
                        $kecKode = "{$kodeParts[0]}.{$kodeParts[1]}.{$kodeParts[2]}";
                        $result[$provKode]['kabupaten_kota'][$kabKode]['kecamatan'][$kecKode]['desa'][$kode] = [
                            'nama_wilayah' => $nama
                        ];
                    }
                }
            }
        }

        // Return the result as JSON
        return new WP_REST_Response($result, 200);
    } else {
        return new WP_REST_Response('Failed to fetch data from CSV.', 500);
    }
}
