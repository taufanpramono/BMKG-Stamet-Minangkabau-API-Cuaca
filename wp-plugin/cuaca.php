<?php


//=========================================
//
//CUACA PROVINSI
//
//=========================================

function get_weather_api() {
	global $wpdb;
	$results    = $wpdb->get_results("SELECT * FROM wp0v_data_cuaca_provinsi WHERE status='ready'", ARRAY_A);
	$dataCuaca  = $results[0]['data_cuaca_prov'];
	$arrayProv  = json_decode($dataCuaca);
	
	// Ambil tanggal terkini
	$date = date('Y-m-d');
	$result = [];
	
	// Iterasi melalui data
	foreach ($arrayProv as $item) {
		if ($item->waktu === $date) {
			$result = [
				'waktu' => $item->waktu,
				'av_temperature' => $item->av_temperature,
				'av_description' => $item->av_description,
				'av_windspeed' => $item->av_windspeed,
				'av_winddir' => $item->av_winddir,
				'av_humidity' => $item->av_humidity,
			];
        	break; // Keluar dari loop setelah menemukan data
        }
    }

	// echo '<pre>';
	// print_r($result);
	// echo '</pre>';

    ob_start();
    ?>
    <div class='cuaca-provinsi-sb'>
    	<div class='column-1'>
    		<h1>Provinsi Sumatera Barat</h1>
    		<p style='color : white; margin-top : -20px; margin-bottom: -5px;'><?= $result['waktu'] ?></p>
    		<div class='img-cuaca'><?= changeDescription($result['av_description'], 80) ?></div>
    		<div class='cuaca-content'><span class='cuaca-sb'><?= $result['av_description'] ?></span></div>
    	</div>
    	<div class='column-2'>	
    		<span><img src='https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/temperature-svgrepo-com.svg' style='width:20px;'><?= $result['av_temperature'] ?> °C</span>
    		<span><img src='https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/humidity-svgrepo-com-1.svg' style='width:20px;'><?= $result['av_humidity'] ?>  %</span>
    		<span><img src='https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/udara-kabur.svg' style='width:20px;'><?= $result['av_windspeed'] ?> Km/j</span>
    	</div>
    </div>	
    <?php
    return ob_get_clean();

}
add_shortcode('cuaca_provinsi', 'get_weather_api');




//=========================================
//
//CUACA PERKABUPATEN CAROUSEL
//
//=========================================

function cuacaPerkota() {
	global $wpdb;
	$results = $wpdb->get_results("SELECT * FROM wp0v_data_cuaca_kab WHERE status='ready'", ARRAY_A);

	$date = date('Y-m-d');

	ob_start();

	?>

	<div class="container swiper">
		<div class="card-wrapper">
			<ul class="card-list swiper-wrapper">
				<?php
				foreach ($results as $city) {
					$weatherData = json_decode($city['av_cuaca'], true);
					if (json_last_error() !== JSON_ERROR_NONE) {
						echo "Error decoding JSON for " . esc_html($city['nama_kab']) . ": " . json_last_error_msg();
            continue; // Skip to the next city
        }
        $todayWeather = array_filter($weatherData, function($weather) use ($date) {
        	return $weather['waktu'] === $date;
        });
        if (empty($todayWeather)) {
        	continue;
        }
        ?>  


        <li class="card-item swiper-slide">
        	<a href="https://stamet-minangkabau-bmkg.my.id/prakicu/prakicu-kab-kota/#<?php echo esc_html($city['nama_kab']); ?>" class="card-link">
        		<h2 class="card-title"><?php echo esc_html($city['nama_kab']); ?></h2>
        		<?php foreach ($todayWeather as $weather) { ?>
        			<div class="card-text-bmkg">
        				<?= changeDescription(esc_html($weather['av_description']), 60); ?>
        				<h4><?= esc_html($weather['av_description']); ?></h4><br> 
        				<strong><?= esc_html($weather['waktu']); ?></strong><br>
        				Suhu: <?= esc_html($weather['av_temperature']); ?> °C<br> 
        				Kecepatan Angin: <?= esc_html($weather['av_windspeed']); ?> Km/j<br> 
        				Arah Angin: <?= esc_html($weather['av_winddir']); ?><br>
        				Kelembapan: <?= esc_html($weather['av_humidity']); ?> %
        			</div>
        		<?php } ?>

        	</a>
        </li>

        <?php
    }

    ?>

</ul>
<div class="swiper-pagination"></div>
<div class="swiper-slide-button swiper-button-prev"></div>
<div class="swiper-slide-button swiper-button-next"></div>
</div>
</div>

<?php

return ob_get_clean();
}

add_shortcode('cuaca_kabkota', 'cuacaPerkota');


//======================
//
//CUACA KABUPATEN TABLE
//
//======================

function cuacaKabTable() {
	global $wpdb;
	$results = $wpdb->get_results("SELECT * FROM wp0v_data_cuaca_kab WHERE status='ready'", ARRAY_A);

	//mengambil data tanggal
	$tanggalCuaca = [];
	foreach ($results as $row) {
		$dataCuacaArr = json_decode($row['av_cuaca'], true);
		foreach($dataCuacaArr as $cuac) {
			$tanggalCuaca[] = $cuac['waktu'];
		}

	}

	$dataTanggal = array_unique($tanggalCuaca);
	$nomor       = 1;


	// echo '<pre>';
	// print_r($dataTanggal);
	// echo '</pre>';

	ob_start();
	?>
	<div class="table-container">
		<table>
			<thead>
				<tr>
					<th>No</th>
					<th>Kota / Kab</th>
					<?php foreach($dataTanggal as $head) : ?>
						<th><?= $head ?></th>
					<?php endforeach ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($results as $rows) : ?>
					<?php $datcu = json_decode($rows['av_cuaca'], true); ?>

					<tr id="<?= $rows['nama_kab']; ?>">
						<td><?= $nomor ?></td>
						<td><?= $rows['nama_kab']; ?></td>
						<?php foreach ($datcu as $cuaca) : ?>
							<td>
								<?= 
								changeDescription(esc_html($cuaca['av_description']), 50).'<br>
								<h5 style="margin-bottom:-30px; margin-top:-8px;font-size:18px">'.$cuaca['av_description'].'</h5><br>

								<p class="content-table">
								<img src="https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/temperature-svgrepo-com.svg" class="img-kontent"> : '. esc_html($cuaca['av_temperature']) .'°C<br>
								<img src="https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/humidity-svgrepo-com-1.svg" class="img-kontent"> : '. esc_html($cuaca['av_humidity']) .' %<br>
								<img src="https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/udara-kabur.svg" class="img-kontent"> : '. esc_html($cuaca['av_windspeed']) .'<span class="km-hour">Km/j</span><br>
								<img src="https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/arah_angin.svg" class="img-kontent"> '. esc_html($cuaca['av_winddir']) .' <br>

								</p>'
								?>
							</td>
						<?php endforeach ?>
					</tr>
					<?php $nomor++ ?>
				<?php endforeach ?>

			</tbody>
		</table>
	</div>
	<?php
	return ob_get_clean();

}
add_shortcode('cuacakab_table','cuacaKabTable');



//=========================
//
//CUACA KECAMATAN TABLE
//
//=========================

function cuacaKecamatan() {
	global $wpdb;

    // Sanitasi input GET
	$param     = isset($_GET['param']) ? sanitize_text_field($_GET['param']) : null;
	$tglIn     = isset($_GET['tgl']) ? sanitize_text_field($_GET['tgl']) : null;
	
	//sumber data dynamic GET
	$dataParam = tableKec($param);

	//filter tanggal 
	$dataDate  = (isset($tglIn)) && !empty($tglIn) ? $tglIn : date('Y-m-d');



	// echo '<pre>';
	// print_r($dataParam);
	// echo '</pre>';

    // Mengambil data dari database
	$results = $wpdb->get_results("SELECT * FROM wp0v_data_cuaca WHERE status='ready'", ARRAY_A);

    // Membuat array untuk menyimpan nama kecamatan
	$datKec   = [];
	$datcuaca = [];
	foreach ($results as $kec) {
		$datKec[]   = $kec['nama_kecamatan'];
		$datcuaca[] = $kec['data_cuaca'];
	}

	//pecah data cuaca & tanggal
	$datTgl = [];
	$datJam = [];
	foreach($datcuaca as $tgl) {
		$jsonCuaca = json_decode($tgl);
		foreach ($jsonCuaca as $tglcuaca) {
			$arryDate = explode(' ',$tglcuaca->local_datetime);
			$datTgl[] = $arryDate[0];
			$datJam[] = [

				'tanggal' => $arryDate[0],
				'jam'     => $arryDate[1]
			];
		}
	}

	//mengambil jam berdasarkan tanggal 
	$datjams = [];
	foreach ($datJam as $filtang) {
		if($filtang['tanggal'] == $dataDate) {
			$datjams[] = $filtang['jam'];
		}	
	}

	//UNTUK SELECTOR
    // Seluruh Kecamanatan
	$allKec  = array_unique(array_filter($datKec));

	// seluruh tanggal
	$allDate = array_unique(array_filter($datTgl));

	// seluruh jam 
	$allJam = array_unique(array_filter($datjams));

	//MEMBENTUK JAM MENJADI FORMAT H:m untuk tampilan tabel
	$jamFormat = [];
	foreach($allJam as $formating) {
		$setupJam = explode(':', $formating);
		$jamFormat[] = $setupJam[0].':'.$setupJam[1];
	}





	// echo '<pre>';
	// print_r($jamFormat);
	// echo '</pre>';


	ob_start();
	?>
	<div class="master-container">
		<div class="container">
			<form action="" method="GET">
				<div class="form-group">
					<select name="param">
						<option value="">Pilih Kecamatan</option>
						<?php foreach ($allKec as $sel) : ?>
							<option value="<?php echo esc_attr($sel); ?>" <?php selected($param, $sel); ?>>
								<?php echo esc_html($sel); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<select name="tgl">
						<option value="">Pilih Tanggal</option>
						<?php foreach ($allDate as $dt) : ?>
							<option value="<?php echo esc_attr($dt); ?>" <?php selected($tglIn, $dt); ?>>
								<?php echo esc_html($dt); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<input type="submit" value="Tampilkan">
				</div>
			</form>
		</div>
		<div class="container-2"></div>
	</div>
	<div class="master-container-2">
		<table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Desa</th>
                    <?php foreach ($jamFormat as $aljam) : ?>
                    	<th><?= $aljam ?></th>
                	<?php endforeach?>
                    
                </tr>
            </thead>
            <tbody>
            <?php $nomor = 1; ?>
            <?php foreach ($dataParam as $ckec) : ?>
            	<?php $cuacaKec = json_decode($ckec['data_cuaca']); ?>
                <tr>
                    <td><?= $nomor ?></td>
                    <td><?= $ckec['nama_desa'] ?></td>
                    <?php 
                    $filterTang = [];
                    foreach($cuacaKec as $kecCu ) {
                    	$filterTang[] = $kecCu;
                    } 

                    $cuacaReal = filterData($filterTang);

                    // echo '<pre>';
					// print_r($cuacaReal);
					// echo '</pre>';
                    ?>

                    <?php foreach ($cuacaReal as $thec) : ?>                		
                    	<?php if ($thec['tanggal'] == $dataDate) : ?>
                    		<?php for ($i = 0; $i < count($allJam); $i++) : ?>
                    			<?php if ($thec['jam'] == $allJam[$i]) : ?>
                    				<td> 

                    					<img src="<?= $thec['icon'] ?>" width="45px">
                    					<h5 style="margin-bottom:-30px; margin-top:5px;font-size:14px; line-height: 15px;"><?= $thec['weat_desc'] ?><br></h5><br>
                    					<p class="content-table">
                    						<p class="content-table">                    						
                    						<img src="https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/temperature-svgrepo-com.svg" class="img-kontent"><?= $thec['temperature'] ?>°C<br>
                    						<img src="https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/humidity-svgrepo-com-1.svg" class="img-kontent"> <?= $thec['humidity'] ?> %<br>
                    						<img src="https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/udara-kabur.svg" class="img-kontent"> <?= $thec['wind_speed'] ?><span class="km-hour">Km/j</span><br>
                    						<img src="https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/arah_angin.svg" class="img-kontent"> <?= $thec['wind_direction'] ?><br>
                    					
                    					</p>
                    				</td>
                    			<?php endif; ?>
                    		<?php endfor; ?>
                    	<?php endif; ?>
                    <?php endforeach; ?>
                </tr>
                <?php $nomor++ ?>
            <?php endforeach ?>
        </tbody>
        </table>
	</div>

	<?php
	return ob_get_clean();
}
add_shortcode('cuaca_kec','cuacaKecamatan');




function tableKec($data) {
    global $wpdb;

    // Jika data tidak kosong, gunakan data tersebut; jika kosong, gunakan 'Pancung Soal'
    $nama_kecamatan = !empty($data) ? $data : 'Pancung Soal';

    // Gunakan prepared statement untuk mencegah SQL Injection
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM wp0v_data_cuaca WHERE status='ready' AND nama_kecamatan=%s",
            $nama_kecamatan
        ),
        ARRAY_A
    );

    return $results;
}



function filterTanggal($tgl) {

}




function filterData($data) {

	$arr = [];
	foreach($data as $row) {

		$localtime  = explode(' ', $row->local_datetime);
		$tanggal    = $localtime[0];
		$jam        = $localtime[1];

		// var_dump($localtime);
		$arr[] = [

			'tanggal'        => $tanggal,
			'jam'            => $jam,
			'temperature'    => $row->temperature,
			'weat_desc'      => $row->weat_desc,
			'wind_direction' => $row->wind_direction,
			'wind_speed'     => $row->wind_speed,
			'humidity'       => $row->humidity,
			'icon'           => $row->icon
		];
		// echo '<pre>';
		// print_r($arr);
		// echo '</pre>';
		
	}

	return $arr;

}