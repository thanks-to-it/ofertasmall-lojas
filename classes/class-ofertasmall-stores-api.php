<?php
/**
 * Ofertasmall Lojas - Ofertasmall Lojas
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Pablo S G Pacheco
 */

namespace TxToIT\OML;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'TxToIT\OML\Ofertasmall_Stores_API' ) ) {
	class Ofertasmall_Stores_API {
		protected $args;
		protected $url_get_lojas = 'http://www.ofertasmall.com.br/api/lojas';

		public function __construct( $args = array() ) {
			$args       = wp_parse_args( $args, array(
				'token' => ''
			) );
			$this->args = $args;
		}

		public function get_lojas( $args = array() ) {
			$args = wp_parse_args( $args, array(
				'id'          => null,
				'nome'        => '',
				'ativo'       => null,
				'hasSegmento' => 0,
				'pagina'      => null,
				'quantidade'  => null,
				'campo'       => 'id',
				'ordem'       => 'asc',
			) );

			$token = $this->args['token'];

			$ch = curl_init( add_query_arg( $args, $this->url_get_lojas ) );

			curl_setopt_array( $ch, array(
				CURLOPT_FOLLOWLOCATION =>true,
				CURLOPT_CUSTOMREQUEST  => 'GET',
				CURLOPT_HTTPHEADER     => array(
					"authorization: {$token}"
				),
				CURLOPT_RETURNTRANSFER => 1,
			) );

			$response = json_decode( curl_exec( $ch ), true );
			curl_close( $ch );

			return $response;
		}

		/**
		 * @return mixed
		 */
		public function getArgs() {
			return $this->args;
		}


	}
}