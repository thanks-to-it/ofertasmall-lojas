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

__('Gets stores from ofertasmall API','ofertasmall-lojas');

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
	),
	'translation'      => array(
		'text_domain' => ' ofertasmall-lojas',
	),
) );
$plugin->init();