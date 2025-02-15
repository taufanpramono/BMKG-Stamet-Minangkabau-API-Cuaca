<?php 



function changeDescription($data, $size) {

	if($data == 'Berawan') {
		return '<img style="width:'.$size.'px" src="https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/berawan-am.svg">';
	} elseif ($data == 'Cerah' ) {
		return '<img style="width:'.$size.'px" src="https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/cerah-am.svg">';
	} elseif($data == 'Cerah Berawan') {
		return '<img style="width:'.$size.'px" src="https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/cerah-berawan-am.svg">';
	} elseif($data == 'Mendung') {
		return '<img style="width:'.$size.'px" src="https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/mendung.svg">';
	} elseif($data == 'Hujan Ringan') {
		return '<img style="width:'.$size.'px" src="https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/hujan-ringan-am.svg">';
	} elseif($data == 'Hujan Sedang') {
		return '<img style="width:'.$size.'px" src="https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/hujan-sedang-am.svg">';
	} elseif($data == 'Hujan Lebat') {
		return '<img style="width:'.$size.'px" src="https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/hujan-lebat-am.svg">';
	} elseif($data == 'Udara Kabur') {
		return '<img style="width:'.$size.'px" src="https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/udara-kabur.svg">';
	} elseif($data == "Hujan Petir") {
		return '<img style="width:'.$size.'px" src="https://stamet-minangkabau-bmkg.my.id/wp-content/uploads/2025/02/hujan-petir-am.svg">';
	}
}




 ?>