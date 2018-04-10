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
				'name'              => _x( 'Categories', 'taxonomy general name', 'textdomain' ),
				'singular_name'     => _x( 'Category', 'taxonomy singular name', 'textdomain' ),
				'search_items'      => __( 'Search Categories', 'textdomain' ),
				'all_items'         => __( 'Categories', 'textdomain' ),
				'parent_item'       => __( 'Parent Category', 'textdomain' ),
				'parent_item_colon' => __( 'Parent Category:', 'textdomain' ),
				'edit_item'         => __( 'Edit Category', 'textdomain' ),
				'update_item'       => __( 'Update Category', 'textdomain' ),
				'add_new_item'      => __( 'Add New Category', 'textdomain' ),
				'new_item_name'     => __( 'New Category Name', 'textdomain' ),
				'menu_name'         => __( 'Category', 'textdomain' ),
			);

			$args = array(
				'hierarchical'      => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'categoria' ),
			);

			register_taxonomy( self::$taxonomy, array( $cpt ), $args );
		}
	}
}