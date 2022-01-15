<?php

/**
 * Class IrnantoRedirectModule
 */
class IrnantoRedirectModule {

	/**
	 * Instance Class
	 *
	 * @var The single instance of the class
	 */
	protected static $instance = null;

	/**
	 * Table name
	 *
	 * @var Table name
	 */
	public $table_name = 'irnanto_rediction';

	/**
	 * Main IrnantoRedirectModule Instance
	 *
	 * Ensures only one instance of IrnantoRedirectModule is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @return IrnantoRedirectModule - Main instance
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

	}

	/**
	 * Save data
	 *
	 * @param  array $data data.
	 * @return int       id insert or error.
	 */
	public function save( $data ) {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_name;

		$wpdb->insert(
			$table_name,
			array(
				'url'     => $data['url'],
				'status'  => $data['status'],
				'new_url' => $data['new_url'],
			),
			array(
				'%s', // url.
				'%s', // status.
				'%s', // new_url.
			)
		);
		return $wpdb->insert_id;
	}

	/**
	 * Update data degree
	 *
	 * @param  integer $id   id degree
	 * @param  array   $data data update
	 * @return int        number row / false.
	 */
	public function update( $id = 0, $data = array() ) {
		global $wpdb;
		$table_name   = $wpdb->prefix . $this->table_name;
		$where        = array( 'id' => $id );
		$format       = array(
			'%s', // url.
			'%s', // status.
			'%s', // new_url.
		);
		$where_format = array( '%d' );
		return $wpdb->update( $table_name, $data, $where, $format, $where_format );
	}

	/**
	 * Get Data degree
	 *
	 * @param  integer $offset offset.
	 * @param  integer $limit  limit.
	 * @param  string  $where  where
	 * @return array          data.
	 */
	public function get( $offset = 0, $limit = 100, $where = '', $order = 'asc', $orderby = 'id' ) {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_name;
		$offset     = absint( $offset );
		$limit      = absint( $limit );
		$add_where  = '';
		if ( '' !== $where ) {
			$add_where = ' WHERE ' . $where . ' ';
		}
		if ( ! in_array( $order, array( 'asc', 'desc' ), true ) ) {
			$order = 'asc';
		}

		$results = $wpdb->get_results(
			"SELECT * FROM {$table_name} {$add_where} ORDER BY {$orderby} {$order} LIMIT {$offset}, {$limit}",
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get total Data degree
	 *
	 * @param  string $where  where
	 * @return array          data.
	 */
	public function get_total( $where = '' ) {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_name;
		$add_where  = '';
		if ( '' !== $where ) {
			$add_where = ' WHERE ' . $where . ' ';
		}
		$total_items = $wpdb->get_var( "SELECT COUNT(*) as total FROM {$table_name} {$add_where}" );
		return $total_items;
	}

	/**
	 * Delete data
	 *
	 * @param  integer $id id degree
	 * @return int      number row or false
	 */
	public function delete( $id = 0 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_name;

		return $wpdb->delete(
			$table_name,
			array( 'id' => $id ),
			array( '%d' )
		);
	}

}

/**
 * Returns the main instance of class to prevent the need to use globals.
 *
 * @since  1.0
 * @return IrnantoRedirectModule
 */
if ( ! function_exists( 'irnanto_redirect_module' ) ) {
	/**
	 * Function irnanto_redirect_module
	 */
	function irnanto_redirect_module() {
		return IrnantoRedirectModule::get_instance();
	}
}

/**
 * Global variable `irnanto_redirect_module`
 */
$GLOBALS['irnanto_redirect_module'] = irnanto_redirect_module();
