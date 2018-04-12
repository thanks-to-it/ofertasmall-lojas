<?php
/**
 * Ofertasmall Lojas - Custom_Fields
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Pablo S G Pacheco
 */

namespace TxToIT\OML;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'TxToIT\OML\Custom_Fields' ) ) {
	class Custom_Fields {
		public $fields_args = array();

		public function __construct( $fields_args ) {
			$fields_args       = wp_parse_args( $fields_args, array(
				'db_key_prefix' => '_oml_',
				'stores_post_type'     => ''
			) );
			$this->fields_args = $fields_args;
		}

		public function create_custom_fields() {
			if ( function_exists( 'acf_add_local_field_group' ) ):
				$prefix = $this->fields_args['db_key_prefix'];

				acf_add_local_field_group( array(
					'key'      => 'oml_cmb',
					'title'    => __( 'WP Lojas', 'ofertasmall-lojas' ),
					'fields'   => array(
						array(
							'key'   => $prefix . 'id',
							'label' => 'ID',
							'name'  => $prefix . 'id',
							'type'  => 'number',
						),
						array(
							'key'   => $prefix . 'shopping_id',
							'label' => __( 'Shopping ID', 'ofertasmall-lojas' ),
							'name'  => $prefix . 'shopping_id',
							'type'  => 'number',
						),
						array(
							'key'   => $prefix . 'id_crm',
							'label' => __( 'CRM', 'ofertasmall-lojas' ),
							'name'  => $prefix . 'id_crm',
							'type'  => 'number',
						),
						array(
							'key'   => $prefix . 'luc',
							'label' => __( 'LUC', 'ofertasmall-lojas' ),
							'name'  => $prefix . 'luc',
							'type'  => 'text',
						),
						array(
							'key'   => $prefix . 'site',
							'label' => __( 'Site', 'ofertasmall-lojas' ),
							'name'  => $prefix . 'site',
							'type'  => 'text',
						),
						array(
							'key'   => $prefix . 'telefone',
							'label' => __( 'Telephone', 'ofertasmall-lojas' ),
							'name'  => $prefix . 'telefone',
							'type'  => 'text',
						),
						array(
							'key'   => $prefix . 'email',
							'label' => __( 'Email', 'ofertasmall-lojas' ),
							'name'  => $prefix . 'email',
							'type'  => 'text',
						),
						array(
							'key'   => $prefix . 'area',
							'label' => __( 'Area', 'ofertasmall-lojas' ),
							'name'  => $prefix . 'area',
							'type'  => 'number',
						),
						array(
							'key'   => $prefix . 'marca',
							'label' => __( 'Branding', 'ofertasmall-lojas' ),
							'name'  => $prefix . 'marca',
							'type'  => 'text',
						),
						array(
							'key'   => $prefix . 'piso',
							'label' => __( 'Floor', 'ofertasmall-lojas' ),
							'name'  => $prefix . 'piso',
							'type'  => 'text',
						),
						array(
							'key'   => $prefix . 'logo',
							'label' => __( 'Logo', 'ofertasmall-lojas' ),
							'name'  => $prefix . 'logo',
							'type'  => 'text',
						)
					),
					'location' => array(
						array(
							array(
								'param'    => 'post_type',
								'operator' => '==',
								'value'    => Store_CPT::$post_type,
							),
						),
					),
				) );

			endif;
		}


	}
}