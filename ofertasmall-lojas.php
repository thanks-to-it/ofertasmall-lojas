<?php
/*
Plugin Name: WP Lojas
Description: Gets stores from ofertasmall API
Version: 1.0.0
Author: Thanks to IT
Author URI: https://github.com/thanks-to-it
Text Domain: ofertasmall-lojas
Domain Path: /languages
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

__( 'Gets stores from ofertasmall API', 'ofertasmall-lojas' );

// Autoload
require_once( "vendor/autoload.php" );

// Initializes plugin
$plugin = \TxToIT\OML\Core::get_instance();
$plugin->set_args( array(
	'plugin_file_path' => __FILE__,
	'action_links'     => array(
		array(
			'url'  => admin_url( 'options-general.php?page=ofertasmall-lojas' ),
			'text' => __( 'Settings', ' ofertasmall-lojas' ),
		),
		array(
			'url'  => admin_url( 'tools.php?page=ofertasmall-lojas-import' ),
			'text' => __( 'Import', ' ofertasmall-lojas' ),
		),
	),
	'translation'      => array(
		'text_domain' => ' ofertasmall-lojas',
	),
) );
$plugin->init();

/*$meta_query_args = array(
	array(
		'key'     => 'alg_wc_civs_term_color_color',
		'value'   => '#1e73be',
		'compare' => '='
	)
);
$meta_query = new \WP_Meta_Query( $meta_query_args );
error_log(print_r($meta_query->get_sql('post'),true));*/

/*
add_action( 'init', function () {
	global $wpdb;
	$meta_key   = 'alg_wc_civs_term_color_color';
	$meta_value = '#1e73be';
	$result   = $wpdb->get_var( $wpdb->prepare(
		"
		SELECT term_id
		FROM $wpdb->termmeta 
		WHERE meta_key = %s AND meta_value = %s
	",
		$meta_key, $meta_value
	) );
} );
*/

/*add_action('admin_init',function(){
	$stores = get_option( '_oml_stores_from_api' );
	$store  = wp_list_filter( $stores, array(
		'id' => 991
	) );
	error_log( print_r( $store, true ) );
});
*/