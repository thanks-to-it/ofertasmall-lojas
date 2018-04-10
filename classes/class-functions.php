<?php
/**
 * Ofertasmall Lojas - General functions
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Pablo S G Pacheco
 */

namespace TxToIT\OML;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'TxToIT\OML\Functions' ) ) {
	class Functions {

		public static function import_stores_from_array( $stores_array ) {
			foreach ( $stores_array as $store ) {
				self::import_store( $store );
			}
		}

		public static function import_store( $store ) {
			$custom_field_prefix = '_oml_';

			$the_query = new \WP_Query( array(
				'fields'     => 'ids',
				'post_type'  => Store_CPT::$post_type,
				'meta_query' => array(
					array(
						'key'     => $custom_field_prefix . 'id',
						'value'   => $store['id'],
						'compare' => 'IN',
					),
				),
			) );

			if ( $the_query->have_posts() ) {

				foreach ( $the_query->posts as $post_id ) {

				}

				/* Restore original Post Data */
				wp_reset_postdata();
			} else {
				$post_id = wp_insert_post( array(
					'post_type'     => Store_CPT::$post_type,
					'post_title'    => $store['nome'],
					'post_name'     => $store['slug'],
					'post_date'     => $store['criado'],
					'post_modified' => $store['atualizado'],
					'post_status' => $store['ativo'] == 1 ? 'publish' : 'draft',
					'meta_input'    => array_filter($store, function($k) {
						return !in_array($k, array(
							'ativo',
							'nome',
							'slug',
							'criado',
							'atualizado',
							'segmentos'
						));
						//return $k != 'nome' ;
					},ARRAY_FILTER_USE_KEY)
					/*'meta_input'    => wp_list_filter( $store, array(
						'nome',
						'slug',
						'criado',
						'atualizado',
						'segmentos'
					), 'NOT' )*/
				) );
				error_log(print_r($post_id,true));
			}
		}

		public static function import_stores() {
			$token     = Admin_Settings::get_general_option( 'token' );
			$lojas_api = new Ofertasmall_Stores_API( array(
				'token' => $token,
			) );
			$lojas     = $lojas_api->get_lojas( array(
				'hasSegmento' => 1,
				'id'          => 990
			) );
			self::import_stores_from_array( $lojas );
			error_log( print_r( $lojas, true ) );
		}

	}
}