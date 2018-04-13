<?php
/**
 * Ofertasmall Lojas - Store CPT
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Pablo S G Pacheco
 */

namespace TxToIT\OML;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'TxToIT\OML\Store_CPT' ) ) {
	class Store_CPT {

		public static $post_type = 'lojas';

		public static function register_cpt() {
			$labels = array(
				'name'               => _x( 'Stores', 'post type general name', 'ofertasmall-lojas' ),
				'singular_name'      => _x( 'Store', 'post type singular name', 'ofertasmall-lojas' ),
				'menu_name'          => _x( 'Stores', 'admin menu', 'ofertasmall-lojas' ),
				'name_admin_bar'     => _x( 'Store', 'add new on admin bar', 'ofertasmall-lojas' ),
				'add_new'            => _x( 'Add New', 'Store', 'ofertasmall-lojas' ),
				'add_new_item'       => __( 'Add New Store', 'ofertasmall-lojas' ),
				'new_item'           => __( 'New Store', 'ofertasmall-lojas' ),
				'edit_item'          => __( 'Edit Store', 'ofertasmall-lojas' ),
				'view_item'          => __( 'View Store', 'ofertasmall-lojas' ),
				'all_items'          => __( 'Stores', 'ofertasmall-lojas' ),
				'search_items'       => __( 'Search Stores', 'ofertasmall-lojas' ),
				'parent_item_colon'  => __( 'Parent Stores:', 'ofertasmall-lojas' ),
				'not_found'          => __( 'No Stores found.', 'ofertasmall-lojas' ),
				'not_found_in_trash' => __( 'No Stores found in Trash.', 'ofertasmall-lojas' )
			);

			$args = array(
				'labels'             => $labels,
				'description'        => __( 'Description.', 'ofertasmall-lojas' ),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'menu_icon'          => 'dashicons-admin-multisite',
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'lojas' ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'title', 'thumbnail' )
			);

			register_post_type( self::$post_type, $args );
		}
	}
}