<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class IrnantoTableRedirect extends WP_List_Table {

	private $per_page = 20;

	function __construct() {
		parent::__construct(
			array(
				'singular' => 'redirect',
				'plural'   => 'redirects',
				'ajax'     => false,
			)
		);
	}

	/** Text displayed when no 404 data is available */
	public function no_items() {
		_e( 'No redirect available.', 'irnanto_redirection' );
	}

	function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" new_url="bulk-delete[]" value="%s" />',
			$item['id']
		);
	}

	/**
	 * Method for url column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_url( $item ) {
		$delete_nonce = wp_create_nonce( 'irnanto_redirection_delete_redirect' );

		$link = add_query_arg(
			array(
				'id'     => absint( $item['id'] ),
				'action' => 'edit',
			),
			menu_page_url( 'irnanto-redirect-form', false )
		);

		$title = '<strong>' . $item['url'] . '</strong>';

		$actions = array(
			'edit'   => sprintf( '<a href="%s" class="edit">' . __( 'Edit', 'irnanto_redirection' ) . '</a>', $link ),
			'delete' => sprintf(
				'<a href="%s&action=%s&id=%s&_wpnonce=%s" class="confirm-delete">' . __( 'Delete', 'irnanto_redirection' ) . '</a>',
				menu_page_url( 'irnanto-redirect', false ),
				'delete',
				absint( $item['id'] ),
				$delete_nonce
			),
		);

		return $title . $this->row_actions( $actions );
	}

	function get_columns() {
		$columns = array(
			'cb'      => '<input type="checkbox" />',
			'url'     => __( 'Old URL', 'irnanto_redirection' ),
			'new_url' => __( 'New URL', 'irnanto_redirection' ),
			'status'  => __( 'Status', 'irnanto_redirection' ),
		);

		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'url'     => array( 'url', true ),
			'new_url' => array( 'new_url', true ),
			'status'  => array( 'status', true ),
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __( 'Delete', 'irnanto_redirection' ),
		);

		return $actions;
	}

	public function prepare_items() {
		global $irnanto_redirect_module;

		$per_page = $this->per_page;
		$where    = '';
		$paged    = isset( $_REQUEST['paged'] ) ? max( 0, intval( $_REQUEST['paged'] ) - 1 ) : 0;
		$orderby  = ( isset( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], array( 'url', 'new_url', 'status' ) ) ) ? $_REQUEST['orderby'] : 'id';
		$order    = ( isset( $_REQUEST['order'] ) && in_array( $_REQUEST['order'], array( 'asc', 'desc' ) ) ) ? $_REQUEST['order'] : 'desc';
		$offset   = $paged * $per_page;

		if ( isset( $_REQUEST['s'] ) ) {
			$search = sanitize_text_field( $_REQUEST['s'] );
			if ( '' !== $search ) {
				$where .= " ( url like '%$search%' OR new_url like '%$search%' OR status like '%$search%' ) ";
			}
		}

		/** Process bulk action */
		$this->process_bulk_action();

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		// here we configure table headers, defined in our methods
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->items = $irnanto_redirect_module->get( $offset, $per_page, $where, $order, $orderby );

		$total_items = $irnanto_redirect_module->get_total( $where );

		// [REQUIRED] configure pagination
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}

	public function process_bulk_action() {
		global $irnanto_redirect_module;
		// Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {
			if ( isset( $_REQUEST['_wpnonce'] ) ) {
				// In our file that handles the request, verify the nonce.
				$nonce = sanitize_text_field( $_REQUEST['_wpnonce'] );

				if ( ! wp_verify_nonce( $nonce, 'irnanto_redirection_delete_redirect' ) ) {
					die( __( 'Tidak ada nonce', 'irnanto_redirection' ) );
				} else {
					$id     = absint( $_REQUEST['id'] );
					$delete = $irnanto_redirect_module->delete( $id );
					if ( $delete ) {
						$success = 1;
					} else {
						$success = 0;
					}
					$link = add_query_arg(
						array( 'delete' => $success ),
						menu_page_url( 'irnanto-redirect', false )
					);
					echo '<meta http-equiv="refresh" content="0; URL=' . $link . '" />';
					exit;
				}
			} else {
				die( __( 'Tidak ada nonce', 'irnanto_redirection' ) );
			}
		}

		// If the delete bulk action is triggered.
		if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'bulk-delete' ) || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'bulk-delete' ) ) {
			$delete_ids = esc_sql( $_REQUEST['bulk-delete'] );

			// loop over the array of record IDs and delete them.
			foreach ( $delete_ids as $id ) {
				$delete = $irnanto_redirect_module->delete( $id );
				if ( $delete ) {
					$success = 1;
				} else {
					$success = 0;
				}
			}

			$link = add_query_arg(
				array( 'delete' => $success ),
				menu_page_url( 'irnanto-redirect', false )
			);
			echo '<meta http-equiv="refresh" content="0; URL=' . $link . '" />';
			exit;
		}
	}

}
