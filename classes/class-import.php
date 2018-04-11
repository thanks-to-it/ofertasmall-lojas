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
			//error_log(print_r($this->checkRemoteFile( $store['logo'] ),true));

			if (
				! isset( $store['logo'] ) ||
				empty( $store['logo'] )
				//! $this->checkRemoteFile( $store['logo'] )
			) {
				return;
			}

			//$store['logo'] = 'http://wordpress.org/about/images/logos/wordpress-logo-stacked-rgb.png';



			//error_log($store['logo']);
			$result = media_sideload_image( $store['logo'], $store_wp_id, null, 'id' );
			//$result = $this->media_sideload_image( $store['logo'], $store_wp_id, null, 'id' );

			//$result = $this->upload_image_from_url($store['logo']);
			//error_log(print_r($result,true));

			if ( ! is_wp_error( $result ) ) {
				if ( has_post_thumbnail( $store_wp_id ) ) {
					$old_image_id = get_post_thumbnail_id( $store_wp_id );
					wp_delete_attachment( $old_image_id, true );
				}
				set_post_thumbnail( $store_wp_id, $result );
			} else {
				error_log( print_r( $result, true ) );
			}
		}

		/*function upload_image_from_url( $imageurl )
		{
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );

			// Get the file extension for the image
			$fileextension = image_type_to_extension( exif_imagetype( $imageurl ) );

			//error_log(print_r($fileextension,true));

			// Save as a temporary file
			$tmp = download_url( $imageurl );

			// Check for download errors
			if ( is_wp_error( $tmp ) )
			{
				@unlink( $file_array[ 'tmp_name' ] );
				return $tmp;
			}

			// Image base name:
			$name = basename( $imageurl );

			// Take care of image files without extension:
			$path = pathinfo( $tmp );
			if( ! isset( $path['extension'] ) ):
				$tmpnew = $tmp . '.tmp';
				if( ! rename( $tmp, $tmpnew ) ):
					return '';
				else:
					$ext  = pathinfo( $imageurl, PATHINFO_EXTENSION );
					$name = pathinfo( $imageurl, PATHINFO_FILENAME )  . $fileextension;
					$tmp = $tmpnew;
				endif;
			endif;

			// Upload the image into the WordPress Media Library:
			$file_array = array(
				'name'     => $name,
				'tmp_name' => $tmp
			);
			$id = media_handle_sideload( $file_array, 0 );

			// Check for handle sideload errors:
			if ( is_wp_error( $id ) )
			{
				@unlink( $file_array['tmp_name'] );
				return $id;
			}

			// Get the attachment url:
			$attachment_url = wp_get_attachment_url( $id );

			return $attachment_url;
		}*/

		/**
		 * Downloads an image from the specified URL and attaches it to a post.
		 *
		 * @since 2.6.0
		 * @since 4.2.0 Introduced the `$return` parameter.
		 * @since 4.8.0 Introduced the 'id' option within the `$return` parameter.
		 *
		 * @param string $file    The URL of the image to download.
		 * @param int    $post_id The post ID the media is to be associated with.
		 * @param string $desc    Optional. Description of the image.
		 * @param string $return  Optional. Accepts 'html' (image tag html) or 'src' (URL), or 'id' (attachment ID). Default 'html'.
		 * @return string|WP_Error Populated HTML img tag on success, WP_Error object otherwise.
		 */
		/*function media_sideload_image( $file, $post_id, $desc = null, $return = 'html' ) {
			if ( ! empty( $file ) ) {

				// Set variables for storage, fix file filename for query strings.
				preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
				if ( ! $matches ) {

					$fileextension = image_type_to_extension( exif_imagetype( $file ) );
					$matches = array($fileextension);
					//error_log(print_r($matches,true));

					//error_log(print_r($content_type,true));
					//return new WP_Error( 'image_sideload_failed', __( 'Invalid image URL' ) );
				}

				$file_array = array();
				$file_array['name'] = basename( $matches[0] );

				// Download file to temp location.
				$file_array['tmp_name'] = download_url( $file );

				error_log('---');
				error_log(print_r($file_array,true));

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

				$alt = isset( $desc ) ? esc_attr( $desc ) : '';
				$html = "<img src='$src' alt='$alt' />";
				return $html;
			} else {
				return new WP_Error( 'image_sideload_failed' );
			}
		}*/

		/*protected function checkRemoteFile( $url ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			// don't download content
			curl_setopt( $ch, CURLOPT_NOBODY, 1 );
			curl_setopt( $ch, CURLOPT_FAILONERROR, 1 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

			$result = curl_exec( $ch );
			curl_close( $ch );
			if ( $result !== false ) {
				return true;
			} else {
				return false;
			}
		}*/

		protected function import_store_terms( $store, $store_wp_id ) {
			if (
				! isset( $store['segmentos'] ) ||
				! is_array( $store['segmentos'] ) ||
				empty( $store['segmentos'] )
			) {
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
				'id'          => 1111
			) );
			self::import_stores_from_array( $lojas );
			//error_log( print_r( $lojas, true ) );
		}

	}
}