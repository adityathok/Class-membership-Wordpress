<?php

if ( ! function_exists( 'justg_scripts' ) ) {
	/**
	 * Load theme's JavaScript and CSS sources.
	 */
	function justg_scripts() {
		// Get the theme data.
		$the_theme     = wp_get_theme();
		$theme_version = $the_theme->get( 'Version' );

		$css_version = $theme_version;
		wp_enqueue_style( 'leaflet-styles', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css', array(), $css_version );
    
		wp_enqueue_script( 'jquery' );

		$js_version = $theme_version;
		wp_enqueue_script( 'leaflet-scripts', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), $js_version, true );
	}
} // End of if function_exists( 'justg_scripts' ).

add_action( 'wp_enqueue_scripts', 'justg_scripts', 20 );


function theme_enqueue_scripts() {
    /**
     * frontend ajax requests.
     */
    wp_enqueue_script( 'lockr-ajax', get_template_directory_uri() . '/js/lockr.js', array('jquery'), null, true );
    wp_localize_script( 'lockr-ajax', 'justg_params',
        array( 
				'ajax_url'      => admin_url( 'ajax.php' ),
				'json'          => array(
					'country_url'     => add_query_arg( array( 't' => time() ), get_template_directory_uri() .'/inc/data/country.json' ),
					'country_key'     => 'justg_country_data',
					'province_url'    => add_query_arg( array( 't' => time() ), get_template_directory_uri() .'/inc/data/province.json' ),
					'province_key'    => 'justg_province_data',
					'city_url'        => add_query_arg( array( 't' => time() ), get_template_directory_uri() .'/inc/data/city.json' ),
					'city_key'        => 'justg_city_data',
					'subdistrict_url' => add_query_arg( array( 't' => time() ), get_template_directory_uri() .'/inc/data/subdistrict.json' ),
					'subdistrict_key' => 'justg_subdistrict_data',
				),
				'text'          => array(
					'placeholder' => array(
						'state'     => __( 'Province', 'justg' ),
						'city'      => __( 'Town / City', 'justg' ),
						'address_2' => __( 'Subdistrict', 'justg' ),
					),
					'label'       => array(
						'state'     => __( 'Province', 'justg' ),
						'city'      => __( 'Town / City', 'justg' ),
						'address_2' => __( 'Subdistrict', 'justg' ),
					),
				),
        )
    );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_scripts' );


