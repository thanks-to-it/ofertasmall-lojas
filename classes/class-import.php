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
		private $api_result;

		public function __construct( $import_args = array() ) {
			$import_args       = wp_parse_args( $import_args, array(
				'db_key_prefix'    => '_oml_',
				'stores_post_type' => '',
				'stores_tax'       => ''
			) );
			$this->import_args = $import_args;
		}

		public function import_stores_from_array( $stores_array, $background_processing = true ) {
			if ( ! $background_processing ) {
				foreach ( $stores_array as $store ) {
					self::import_store( $store );
				}
			} else {
				//update_option( '_oml_import_count', 0, false );
				update_option( '_oml_imported_store_ids', array(), false );
				update_option( '_oml_import_error_store_ids', array(), false );
				$plugin      = Core::get_instance();
				$bkg_process = $plugin->import_bkg_process;
				$bkg_process->cancel_process();
				foreach ( $stores_array as $store ) {
					$bkg_process->push_to_queue( $store['id'] );
				}
				$bkg_process->save()->dispatch();
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
			//error_log(print_r($store,true));

			// Remove unwanted custom fields
			$metas_to_save = array_filter( $store, array( $this, 'filter_unwanted_custom_fields' ), ARRAY_FILTER_USE_KEY );

			// Turns null custom fields into empty ones
			$metas_to_save = array_map( array( $this, 'turn_null_custom_fields_into_empty' ), $metas_to_save );

			// Add prefix
			$metas_to_save_with_prefix = array_combine(
				array_map( function ( $k ) {
					return $this->import_args['db_key_prefix'] . $k;
				}, array_keys( $metas_to_save ) ),
				$metas_to_save
			);

			$the_query = new \WP_Query( array(
				'post_status'            => 'any',
				'cache_results'          => false,
				'no_found_rows'          => false,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'fields'                 => 'ids',
				'post_type'              => $this->import_args['stores_post_type'],
				'meta_query'             => array(
					array(
						'key'     => $this->import_args['db_key_prefix'] . 'id',
						'value'   => $store['id'],
						'compare' => '=',
					),
				),
			) );

			if ( $the_query->have_posts() ) {
				foreach ( $the_query->posts as $post_id ) {
					$post_update_arr       = $this->get_post_update_array( $store, $metas_to_save_with_prefix );
					$post_update_arr['ID'] = $post_id;
					wp_update_post( $post_update_arr );
					$this->import_store_terms( $store, $post_id );
					$this->download_logo_to_post_thumbnail( $store, $post_id );
				}

				/* Restore original Post Data */
				wp_reset_postdata();
			} else {
				$store_wp_id = wp_insert_post( $this->get_post_update_array( $store, $metas_to_save_with_prefix ) );
				$this->import_store_terms( $store, $store_wp_id );
				$this->download_logo_to_post_thumbnail( $store, $store_wp_id );
			}
		}

		protected function download_logo_to_post_thumbnail( $store, $store_wp_id ) {
			$download = filter_var( Admin_Settings::get_general_option( 'download_images', 'oml_general', 'on' ), FILTER_VALIDATE_BOOLEAN );
			if (
				! $download ||
				! isset( $store['logo'] ) ||
				empty( $store['logo'] )
			) {
				return;
			}

			//$result = media_sideload_image( $store['logo'], $store_wp_id, null, 'id' );
			$result = $this->media_sideload_image( $store['logo'], $store_wp_id, null, 'id' );

			if ( ! is_wp_error( $result ) ) {
				if ( has_post_thumbnail( $store_wp_id ) ) {
					$old_image_id = get_post_thumbnail_id( $store_wp_id );
					wp_delete_attachment( $old_image_id, true );
				}
				set_post_thumbnail( $store_wp_id, $result );
				update_post_meta( $store_wp_id, $this->import_args['db_key_prefix'] . 'logo_wp_id', $result );
			} else {
				error_log( print_r( $result, true ) );
			}
		}

		/**
		 * Downloads an image from the specified URL and attaches it to a post.
		 *
		 * @since 2.6.0
		 * @since 4.2.0 Introduced the `$return` parameter.
		 * @since 4.8.0 Introduced the 'id' option within the `$return` parameter.
		 *
		 * @param string $file The URL of the image to download.
		 * @param int $post_id The post ID the media is to be associated with.
		 * @param string $desc Optional. Description of the image.
		 * @param string $return Optional. Accepts 'html' (image tag html) or 'src' (URL), or 'id' (attachment ID). Default 'html'.
		 *
		 * @return string|WP_Error Populated HTML img tag on success, WP_Error object otherwise.
		 */
		function media_sideload_image( $file, $post_id, $desc = null, $return = 'html' ) {
			if ( ! empty( $file ) ) {

				// Set variables for storage, fix file filename for query strings.
				preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
				if ( ! $matches ) {
					$image_type = exif_imagetype( $file );
					if ( $image_type ) {
						$fileextension = image_type_to_extension( $image_type );
						$matches       = array( $fileextension );
					} else {
						return new WP_Error( 'image_sideload_failed', __( 'Invalid image URL' ) );
					}
				}

				require_once( ABSPATH . 'wp-admin/includes/media.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/image.php' );

				$file_array         = array();
				$file_array['name'] = basename( $matches[0] );

				// Download file to temp location.
				$file_array['tmp_name'] = download_url( $file );

				// If error storing temporarily, return the error.
				if ( is_wp_error( $file_array['tmp_name'] ) ) {
					return $file_array['tmp_name'];
				}

				// Do the validation and storage stuff.
				$id = media_handle_sideload( $file_array, $post_id, $desc );

				// If error storing permanently, unlink.
				if ( is_wp_error( $id ) ) {
					@unlink( $file_array['tmp_name'] );

					return $id;
					// If attachment id was requested, return it early.
				} elseif ( $return === 'id' ) {
					return $id;
				}

				$src = wp_get_attachment_url( $id );
			}

			// Finally, check to make sure the file has been saved, then return the HTML.
			if ( ! empty( $src ) ) {
				if ( $return === 'src' ) {
					return $src;
				}

				$alt  = isset( $desc ) ? esc_attr( $desc ) : '';
				$html = "<img src='$src' alt='$alt' />";

				return $html;
			} else {
				return new WP_Error( 'image_sideload_failed' );
			}
		}

		protected function import_store_terms( $store, $store_wp_id ) {
			if (
				! isset( $store['segmentos'] ) ||
				! is_array( $store['segmentos'] ) ||
				empty( $store['segmentos'] )
			) {
				return;
			}
			$store_tax            = $this->import_args['stores_tax'];
			$custom_fields_prefix = $this->import_args['db_key_prefix'];

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

		public function save_stores_on_database( $stores ) {
			$prefix = $this->import_args['db_key_prefix'];
			update_option( $prefix . 'stores_from_api', $stores, false );
		}

		public function update_bkg_process_task( $store_id ) {
			$stores = get_option( '_oml_stores_from_api' );
			$store  = wp_list_filter( $stores, array(
				'id' => $store_id
			) );

			$imported_store_ids = get_option( '_oml_imported_store_ids', 0 );
			if ( ! array_search( $store_id, $imported_store_ids ) ) {
				array_push( $imported_store_ids, $store_id );
				update_option( '_oml_imported_store_ids', $imported_store_ids, false );
			} else {
				$error_stores = get_option( '_oml_import_error_store_ids', array() );
				array_push( $error_stores, $store_id );
				update_option( '_oml_import_error_store_ids', $error_stores, false );

				return false;
			}

			reset( $store );
			$first_key = key( $store );
			$this->import_store( $store[ $first_key ] );

			return false;
		}

		public function get_bkg_process_percentage() {
			$stores     = get_option( '_oml_stores_from_api' );
			$total      = count( $stores );
			$count      = count( get_option( '_oml_imported_store_ids', array() ) );
			$percentage = round( $count / $total, 2 );

			return $percentage;
		}

		public function show_error_message_from_api() {
			$class   = 'notice notice-error';
			$message = __( 'Sorry, some error ocurred.', 'ofertasmall-lojas' );
			$message .= '<br /><strong>' . __( 'API Message:', 'ofertasmall-lojas' ) . '</strong>';
			$message .= ' '. $this->api_result['message'];
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
		}

		public function show_ok_notice() {
			$class   = 'notice notice-success';
			$message = __( 'The API is working. The import process has started.', 'ofertasmall-lojas' );
			$message .= '<br />' . __( 'You can navigate normally while the process continues', 'ofertasmall-lojas' );
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
		}

		public function import_stores_from_stores_api( Ofertasmall_Stores_API $api ) {

			$stores = $api->get_lojas( array(
				'hasSegmento' => 1,
			) );
			if (
				! is_array( $stores ) ||
				( isset( $stores['success'] ) && ! filter_var( $stores['success'], FILTER_VALIDATE_BOOLEAN ) )
			) {
				$this->api_result = $stores;
				add_action( 'admin_notices', array( $this, 'show_error_message_from_api' ) );

				return;
			}

			add_action( 'admin_notices', array( $this, 'show_ok_notice' ) );
			$this->save_stores_on_database( $stores );
			$this->import_stores_from_array( $stores );

			//error_log(count($stores));

			/*$test_ids = array( 1111, 1260, 990, 1339, 1009, 1340, 1342, 1142);
			foreach ( $test_ids as $id ) {
				$stores = $api->get_lojas( array(
					'hasSegmento' => 1,
					'id'          => $id
				) );
				$this->import_stores_from_array( $stores );
			}*/

		}

	}
}