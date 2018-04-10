<?php
/**
 * Ofertasmall Lojas - Admin Tools
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Pablo S G Pacheco
 */

namespace TxToIT\OML;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'TxToIT\OML\Admin_Tools' ) ) {
	class Admin_Tools {

		public static $settings_api;

		public static function admin_menu() {
			add_management_page( __( 'Import Stores', 'ofertasmall-lojas' ), __( 'Import Stores', 'ofertasmall-lojas' ), 'delete_posts', 'ofertasmall-lojas-import', array( __CLASS__, 'plugin_page' ) );
		}

		public static function import_stores() {
			if (
				! isset( $_POST['oml_import_stores_form'] )
				|| ! wp_verify_nonce( $_POST['oml_import_stores_form'], 'oml_import_stores_action' )
			) {
				return;
			}

			Functions::import_stores();
		}

		public static function plugin_page() {
			echo '
			<style>
				.oml-progress-wrapper{
					width:100%;
					display:inline-block;
					height:33px;					
					position:relative;
					border:1px solid #b3b3b3;
				}
				.oml-progress-bar{
					position:absolute;
					left:0;
					top:0;
					background:#cecece;
					width:0%;
					height:100%;
				}
				.oml-progress-value{
					position:absolute;
					width:100%;
					text-align: center;
					line-height:32px;
					color:#676767;
					font-size:18px;
					text-transform: uppercase;	
					z-index:2;				
				}
			</style>
			';

			echo '<form method="post">';
			wp_nonce_field( 'oml_import_stores_action', 'oml_import_stores_form' );
			echo '<div class="wrap">';
			echo '<h1>' . __( 'Import Stores', 'ofertasmall-lojas' ) . '</h1>';
			echo '<table class="form-table">';
			echo '
			<tr>
				<th scope="row"><label for="blogname">' . __( 'Progress', 'ofertasmall-lojas' ) . '</label></th>
				<td>
					<div class="oml-progress-wrapper">
						<span class="oml-progress-value">0%</span>
						<span class="oml-progress-bar"></span>
					</div>
				</td>
			</tr>';
			echo '</table>';
			echo '<p class="submit">';
			echo '<input type="submit" name="oml_import_stores" id="oml_import_stores" class="button button-primary" value="' . __( 'Import Stores', 'ofertasmall-lojas' ) . '"/>';
			echo '</p>';
			echo '</form>';
			echo '</div>';
		}


	}
}