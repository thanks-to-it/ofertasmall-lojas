<?php
/**
 * Ofertasmall Lojas - Import
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Pablo S G Pacheco
 */

namespace TxToIT\OML;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'TxToIT\OML\Import' ) ) {
	class Import {

		public $import_args = array();

		public function __construct( $import_args = array() ) {
			$import_args       = wp_parse_args( $import_args, array(
				'custom_fields_prefix' => '_oml_',
				'stores_post_type'     => '',
				'stores_tax'           => ''
			) );
			$this->import_args = $import_args;
		}

		public function import_stores_from_array( $stores_array ) {
			foreach ( $stores_array as $store ) {
				self::import_store( $store );
			}
		}

		private function filter_unwanted_custom_fields( $k ) {
			return ! in_array( $k, array(
				'ativo',
				'nome',
				'criado',
				'atualizado',
				'segmentos'
			) );
		}

		private function turn_null_custom_fields_into_empty( $v ) {
			if ( $v == null ) {
				$v = '';
			}

			return $v;
		}

		public function import_store( $store ) {
			// Remove unwanted custom fields
			$metas_to_save = array_filter( $store, array( $this, 'filter_unwanted_custom_fields' ), ARRAY_FILTER_USE_KEY );

			// Turns null custom fields into empty ones
			$metas_to_save = array_map( array( $this, 'turn_null_custom_fields_into_empty' ), $metas_to_save );

			// Add prefix
			$metas_to_save_with_prefix = array_combine(
				array_map( function ( $k ) {
					return $this->import_args['custom_fields_prefix'] . $k;
				}, array_keys( $metas_to_save ) ),
				$metas_to_save
			);

			$the_query = new \WP_Query( array(
				'fields'     => 'ids',
				'post_type'  => $this->import_args['stores_post_type'],
				'meta_query' => array(
					array(
						'key'     => $this->import_args['custom_fields_prefix'] . 'id',
						'value'   => $store['id'],
						'compare' => 'IN',
					),
				),
			) );

			if ( $the_query->have_posts() ) {
				foreach ( $the_query->posts as $post_id ) {
					$post_update_arr       = $this->get_post_update_array( $store, $metas_to_save_with_prefix );
					$post_update_arr['ID'] = $post_id;
					wp_update_post( $post_update_arr );
					$this->import_store_terms( $store, $post_id );
				}

				/* Restore original Post Data */
				wp_reset_postdata();
			} else {
				$store_wp_id = wp_insert_post( $this->get_post_update_array( $store, $metas_to_save_with_prefix ) );
				$this->import_store_terms( $store, $store_wp_id );
			}
		}

		protected function import_store_terms( $store, $store_wp_id ) {
			if ( ! isset( $store['segmentos'] ) ) {
				return;
			}
			$store_tax            = $this->import_args['stores_tax'];
			$custom_fields_prefix = $this->import_args['custom_fields_prefix'];

			$segmentos = $store['segmentos'][0];

			$parent_wp_term_id = - 1;
			for ( $i = 1; $i <= 3; $i ++ ) {
				$segmento_meta_key = $custom_fields_prefix . 'segmento_id';
				$term_segmento_id  = $segmentos["segmento_n{$i}"];
				if ( empty( $term_segmento_id ) ) {
					continue;
				}
				global $wpdb;
				$result = $wpdb->get_var( $wpdb->prepare(
					"
						SELECT term_id
						FROM $wpdb->termmeta 
						WHERE meta_key = %s AND meta_value = %s
					",
					$segmento_meta_key, $term_segmento_id
				) );

				$segmento_nome = $segmentos["segmento_n{$i}_nome"];

				$term_args = array();
				if ( $parent_wp_term_id != - 1 ) {
					$term_args['parent'] = $parent_wp_term_id;
				}

				if ( empty( $result ) ) {
					$term = wp_insert_term( $segmento_nome, $store_tax, $term_args );
					if ( ! is_wp_error( $term ) ) {
						$parent_wp_term_id = $term['term_id'];
						update_term_meta( $parent_wp_term_id, $segmento_meta_key, $term_segmento_id );
					}
					wp_set_post_terms( $store_wp_id, $parent_wp_term_id, $store_tax );
				} else {
					$term_args['name'] = $segmento_nome;
					wp_update_term( $result, $store_tax, $term_args );
					wp_set_post_terms( $store_wp_id, $result, $store_tax );
				}
			}
		}

		protected function get_post_update_array( $store, $metas ) {
			return array(
				'post_type'     => $this->import_args['stores_post_type'],
				'post_title'    => $store['nome'],
				'post_name'     => $store['slug'],
				'post_date'     => $store['criado'],
				'post_modified' => $store['atualizado'],
				'post_status'   => $store['ativo'] == 1 ? 'publish' : 'draft',
				'meta_input'    => $metas
			);
		}

		public function import_stores() {
			$token     = Admin_Settings::get_general_option( 'token' );
			$lojas_api = new Ofertasmall_Stores_API( array(
				'token' => $token,
			) );
			$lojas     = $lojas_api->get_lojas( array(
				'hasSegmento' => 1,
				'id'          => 990
			) );
			self::import_stores_from_array( $lojas );
			//error_log( print_r( $lojas, true ) );
		}

	}
}