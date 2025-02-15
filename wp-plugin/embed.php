<?php


// Fungsi untuk membuat shortcode iframe
function custom_iframe_shortcode($atts) {
    // Mengambil atribut dari shortcode
    $atts = shortcode_atts(
        array(
            'src' => '', // URL iframe
            'width' => '100%', // Lebar default
            'height' => '750', // Tinggi default
        ),
        $atts,
        'iframe'
    );

    // Mengembalikan HTML iframe
    return '<iframe src="' . esc_url($atts['src']) . '" width="' . esc_attr($atts['width']) . '" height="' . esc_attr($atts['height']) . '" style="border:none; border-radius: 8px;"></iframe>';
}

// Mendaftarkan shortcode
add_shortcode('theiframe', 'custom_iframe_shortcode');