<?php
/**
 * Ofertasmall Lojas - Core class
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Pablo S G Pacheco
 */

namespace TxToIT\OML;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'TxToIT\OML\Core' ) ) {
	class Core extends WP_Plugin {

		public function init() {
			parent::init();

			// Admin Settings page
			add_action( 'admin_init', array( 'TxToIT\OML\Admin_Settings', 'admin_init' ) );
			add_action( 'admin_menu', array( 'TxToIT\OML\Admin_Settings', 'admin_menu' ) );

			// Tools Settings page
			add_action( 'admin_init', array( 'TxToIT\OML\Admin_Tools', 'import_stores' ) );
			add_action( 'admin_menu', array( 'TxToIT\OML\Admin_Tools', 'admin_menu' ) );

			// Register Custom post type
			add_action( 'init', array( 'TxToIT\OML\Store_CPT', 'register_cpt' ) );

			// Register taxonomy
			add_action( 'init', array( 'TxToIT\OML\Store_Tax', 'register_taxonomy' ) );

		}

	}
}