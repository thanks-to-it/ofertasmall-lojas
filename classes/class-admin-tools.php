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

			$token      = Admin_Settings::get_general_option( 'token' );
			$stores_api = new Ofertasmall_Stores_API( array(
				'token' => $token,
			) );

			$import = new Import( array(
				'stores_post_type' => Store_CPT::$post_type,
				'stores_tax'       => Store_Tax::$taxonomy
			) );
			$import->import_stores_from_stores_api( $stores_api );
		}

		public static function echo_style() {
			?>
            <style>
                .oml-progress-wrapper {
                    width: 100%;
                    display: inline-block;
                    height: 33px;
                    position: relative;
                    border: 1px solid #b3b3b3;
                }

                .oml-progress-bar {
                    position: absolute;
                    left: 0;
                    top: 0;
                    background: #cecece;
                    width: '.$percentage_pretty.'%;
                    height: 100%;
                    transition:all 1s ease-in-out;
                }

                .oml-progress-value {
                    position: absolute;
                    width: 100%;
                    text-align: center;
                    line-height: 32px;
                    color: #676767;
                    font-size: 18px;
                    text-transform: uppercase;
                    z-index: 2;
                }

                .oml-progress-label:after{
                    opacity: 0;
                    content:url('https://media.giphy.com/media/EMspSu9w0djAA/giphy.gif');
                    display: inline-block;
                    margin-left: 13px;
                    vertical-align: middle;
                    transition:all 1s ease-in-out;
                }

                .oml-progress-label.progress:after{
                    opacity: 1;
                }
            </style>
			<?php
		}

		public static function show_background_process_progress() {
			?>
            <script>
                jQuery(document).ready(function ($) {
                    var oml_interval;
                    var percent = 0;
                    var count = 0;
                    var no_queue = false;

                    function oml_call_ajax() {
                        var data = {
                            'action': 'show_bkg_process_percentage'
                        };
                        jQuery.post(ajaxurl, data, function (response) {
                            count++;
                            percent = Math.round(response.data.percent*100);
                            if(percent>0 && percent<100){
                                jQuery('.oml-progress-label').addClass('progress');
                            }
                            $('.oml-progress-bar').css('width', percent + '%');
                            $('.oml-progress-value').html(percent + '%');
                        });
                    }

                    oml_interval = setInterval(handle_interval, 3000);

                    function handle_interval() {
                        if (percent < 100 && !no_queue && percent > 0 || count ==0) {
                            oml_call_ajax();
                        } else {
                            jQuery('.oml-progress-label').removeClass('progress');
                            clearInterval(oml_interval);
                        }
                    }
                });
            </script>
			<?php
		}

		public static function plugin_page() {
			$import            = new Import( array(
				'stores_post_type' => Store_CPT::$post_type,
				'stores_tax'       => Store_Tax::$taxonomy
			) );
			$percentage        = $import->get_bkg_process_percentage();
			$percentage_pretty = 100 * $percentage;

			self::echo_style();
			self::show_background_process_progress();

			echo '<form method="post">';
			wp_nonce_field( 'oml_import_stores_action', 'oml_import_stores_form' );
			echo '<div class="wrap">';
			echo '<h1>' . __( 'Import Stores', 'ofertasmall-lojas' ) . '</h1>';
			echo '<table class="form-table">';
			echo '
			<tr>
				<th scope="row"><label class="oml-progress-label" for="blogname">' . __( 'Progress', 'ofertasmall-lojas' ) . '</label></th>
				<td>
					<div class="oml-progress-wrapper">
						<span class="oml-progress-value">' . $percentage_pretty . '%</span>
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