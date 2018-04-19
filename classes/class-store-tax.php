<?php
/**
 * Ofertasmall Lojas - Store Tax
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Pablo S G Pacheco
 */

namespace TxToIT\OML;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'TxToIT\OML\Store_Tax' ) ) {
	class Store_Tax {

		public static $taxonomy = 'lojas-categorias';

		public static function register_taxonomy( $cpt ) {
			// Add new taxonomy, make it hierarchical (like categories)
			$labels = array(
				'name'              => _x( 'Categories', 'taxonomy general name', 'ofertasmall-lojas' ),
				'singular_name'     => _x( 'Category', 'taxonomy singular name', 'ofertasmall-lojas' ),
				'search_items'      => __( 'Search Categories', 'ofertasmall-lojas' ),
				'all_items'         => __( 'Categories', 'ofertasmall-lojas' ),
				'parent_item'       => __( 'Parent Category', 'ofertasmall-lojas' ),
				'parent_item_colon' => __( 'Parent Category:', 'ofertasmall-lojas' ),
				'edit_item'         => __( 'Edit Category', 'ofertasmall-lojas' ),
				'update_item'       => __( 'Update Category', 'ofertasmall-lojas' ),
				'add_new_item'      => __( 'Add New Category', 'ofertasmall-lojas' ),
				'new_item_name'     => __( 'New Category Name', 'ofertasmall-lojas' ),
				'menu_name'         => __( 'Category', 'ofertasmall-lojas' ),
			);

			$args = array(
				'hierarchical'      => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'lojas-categorias' ),
			);

			register_taxonomy( self::$taxonomy, array( $cpt ), $args );
		}
	}
}