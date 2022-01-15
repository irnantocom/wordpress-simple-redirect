<?php

/**
 * Check class IrnantoRedirectionPage if exist
 */
if ( ! class_exists( 'IrnantoRedirectionPage' ) ) {
	/**
	 * Class IrnantoRedirectionPage
	 */
	class IrnantoRedirectionPage {
		/**
		 * Instance Class
		 *
		 * @var The single instance of the class
		 */
		protected static $instance = null;

		/**
		 * Main IrnantoRedirectionPage Instance
		 *
		 * Ensures only one instance of IrnantoRedirectionPage is loaded or can be loaded.
		 *
		 * @since 1.0
		 * @static
		 * @return IrnantoRedirectionPage - Main instance
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * First load in __construct
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'menu' ) );
			add_action( 'admin_notices', array( $this, 'notice' ) );
		}

		/**
		 * Register menu
		 */
		public function menu() {
			add_menu_page( 'Redirect', 'Redirect', 'manage_options', 'irnanto-redirect', array( $this, 'redirection_page' ), 'dashicons-controls-repeat', 15 );
			add_submenu_page( 'irnanto-redirect', 'Redirect', 'Add Redirect', 'manage_options', 'irnanto-redirect-form', array( $this, 'redirection_page_form' ) );
		}

		public function notice() {
			$screen = get_current_screen();
			if ( in_array( $screen->id, array( 'toplevel_page_irnanto-redirect' ), true ) ) {
				global $irnanto_redirect_module;
				$message = '';
				$type    = 'error';

				if ( isset( $_GET['delete'] ) ) { // belum handle bulk delete.
					$delete = absint( $_GET['delete'] );
					if ( 1 === $delete ) {
						$message = __( 'Data redirect berhasil di hapus', 'irnanto_redirection' );
						$type    = 'updated';
					} elseif ( 0 === $delete ) {
						$message = __( 'Gagal hapus data redirect', 'irnanto_redirection' );
						$type    = 'error';
					}
					?>
					<div class="notice is-dismissible <?php echo esc_attr( $type ); ?>">
						<p><?php echo esc_html( $message ); ?></p>
					</div>
					<?php
				}

				if ( isset( $_POST['nonce'] ) ) {
					$nonce = $_POST['nonce'];
					if ( wp_verify_nonce( $nonce, 'irnanto_redirect_nonce' ) ) {
						$url     = sanitize_text_field( $_POST['url'] );
						$new_url = sanitize_text_field( $_POST['new_url'] );
						$status  = sanitize_text_field( $_POST['status'] );

						$data = array(
							'url'     => $url,
							'new_url' => $new_url,
							'status'  => $status,
						);

						if ( isset( $_POST['id'] ) ) {
							$id     = absint( $_POST['id'] );
							$update = $irnanto_redirect_module->update( $id, $data );
							if ( $update ) {
								$message = __( 'Data redirect terupdate', 'irnanto_redirection' );
								$type    = 'updated';
							} else {
								$message = __( 'Gagal update data redirect', 'irnanto_redirection' );
								$type    = 'error';
							}
						} else {
							$insert = $irnanto_redirect_module->save( $data );
							if ( $insert ) {
								$message = __( 'Data redirect berhasil disimpan', 'irnanto_redirection' );
								$type    = 'updated';
							} else {
								$message = __( 'Gagal simpan data redirect', 'irnanto_redirection' );
								$type    = 'error';
							}
						}
						?>
						<div class="notice is-dismissible <?php echo esc_attr( $type ); ?>">
							<p><?php echo esc_html( $message ); ?></p>
						</div>
						<?php
					}
				}
			}
		}

		/**
		 * Page redirect
		 */
		public function redirection_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'irnanto_redirection' ) );
			}

			$table = new IrnantoTableRedirect();
			$table->prepare_items();
			?>
			<div class="wrap">
				<h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<a href="<?php echo esc_url( menu_page_url( 'irnanto-redirect-form', false ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'irnanto_redirection' ); ?></a>
				<hr class="wp-header-end">
				<form method="get" id="form-redirect">
					<input type="hidden" name="page" value="irnanto-redirect" />
					<?php
					$table->search_box( 'Search', 'search_id' );
					$table->display();
					?>
				</form>
			</div>
			<?php
		}

		public function redirection_page_form() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'irnanto_redirection' ) );
			}
			?>
			<div class="wrap">
				<h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<a href="<?php echo esc_url( menu_page_url( 'irnanto-redirect-form', false ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'irnanto_redirection' ); ?></a>
				<hr class="wp-header-end">
				<form action="<?php echo menu_page_url( 'irnanto-redirect', false ); ?>" method="post" id="form-redirect">
					<input type="hidden" name="page" value="irnanto-redirect-form" />
					<?php wp_nonce_field( 'irnanto_redirect_nonce', 'nonce' ); ?>
			<?php
			if ( isset( $_REQUEST['action'] ) ) {
				$action = sanitize_text_field( $_REQUEST['action'] );
				if ( in_array( $action, array( 'edit' ), true ) ) {
					$id_redirect = absint( $_REQUEST['id'] );
					echo '<input type="hidden" name="id" value="' . esc_attr( $id_redirect ) . '" />';
					$this->form( $id_redirect );
				}
			} else {
				$this->form();
			}
			?>
				</form>
			</div>
			<?php
		}

		/**
		 * Form redirect
		 *
		 * @param  integer $id id redirect.
		 */
		public function form( $id = 0 ) {
			$url     = '';
			$new_url = '';
			$status  = '';
			if ( 0 !== $id ) {
				global $irnanto_redirect_module;
				$id           = absint( $id );
				$get_redirect = $irnanto_redirect_module->get( 0, 1, ' id = ' . $id );
				if ( isset( $get_redirect[0] ) ) {
					$url     = $get_redirect[0]['url'];
					$new_url = $get_redirect[0]['new_url'];
					$status  = $get_redirect[0]['status'];
				}
			}
			?>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<label><?php esc_html_e( 'URL', 'irnanto_redirection' ); ?></label>
						</th>
						<td>
							<input type="url" name="url" class="regular-text" value="<?php echo esc_attr( $url ); ?>" required>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label><?php esc_html_e( 'New URL', 'irnanto_redirection' ); ?></label>
						</th>
						<td>
							<input type="url" name="new_url" class="regular-text" value="<?php echo esc_attr( $new_url ); ?>" required>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label><?php esc_html_e( 'Status', 'irnanto_redirection' ); ?></label>
						</th>
						<td>
							<input type="number" name="status" class="regular-text" value="<?php echo esc_attr( $status ); ?>" required>
						</td>
					</tr>
				</tbody>
			</table>
			<?php
			submit_button( __( 'Save Data', 'irnanto_redirection' ) );
		}

	}
}

/**
 * Returns the main instance of class to prevent the need to use globals.
 *
 * @since  1.0
 * @return IrnantoRedirectionPage
 */
if ( ! function_exists( 'irnanto_redirection_page' ) ) {
	/**
	 * Function irnanto_redirection_page
	 */
	function irnanto_redirection_page() {
		return IrnantoRedirectionPage::get_instance();
	}
}

irnanto_redirection_page();
