<?php

namespace Solution_Box\Plugin\Simple_Product_Tabs;

/**
 * The main plugin class.
 *
 * @package   Solution_Box\simple-woo-tabs
 */
class Plugin {

	/**
	 * Plugin meta data.
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Setup Plugin.
	 *
	 * @var mixed
	 */
	public $setup_plugin;

	/**
	 * Tabs post type.
	 *
	 * @var Post_Type
	 */
	public $tab_post_type;

	/**
	 * Admin interface
	 *
	 * @var Admin_Controller
	 */
	public $admin;

	/**
	 * Product tabs.
	 *
	 * @var Product_tabs
	 */
	public $product_tabs;



	/**
	 * Constructs and initializes the WooCommerce Product Tabs plugin instance.
	 *
	 * @param string $file    The main plugin __FILE__ .
	 * @param string $version The current plugin version.
	 */
	public function __construct( $file = null, $version = '1.0' ) {

		$this->data = array(
			'version'        => $version,
			'file'           => $file,
			'is_woocommerce' => true,
		);

	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {

		add_action( 'plugins_loaded', array( $this, 'init' ) );

		add_action( 'init', array( $this, 'load_textdomain' ), 5 );
	}

	/**
	 * Plugin initialize.
	 *
	 * @return void
	 */
	public function init() {
		// $this->setup_plugin();
		$this->post_type = new Post_Type();
		$this->post_type->register();
		// Admin only services.
		if ( is_admin() ) {
			$this->admin = new Admin\Admin_Controller( $this );
			// $this->admin->register();
		}

		// $this->product_tabs =  new Product_Tabs();
	}

	/**
	 * Load the textdomain.
	 */
	public function load_textdomain() {

		load_plugin_textdomain( 'simple-woo-tabs', false, $this->get_slug() . '/languages' );
	}


	/**
	 * Setup plugin.
	 *
	 * @return void
	 */
	public function setup_plugin() {
		$this->setup_plugin = new Plugin_Setup( $this->get_data( 'file' ) );
	}



	/**
	 * Get plugin data by key.
	 *
	 * @param string $key data key.
	 * @return mixed
	 */
	public function get_data( $key = 'version' ) {

		return $this->data[ $key ] ?? '';
	}

	/**
	 * Get plugin slug.
	 *
	 * @return string
	 */
	public function get_slug() {
		$dir_path = $this->get_data( 'file' );

		return ! empty( $dir_path ) ? \basename( $dir_path, '.php' ) : '';
	}

	/**
	 * Get plugin version.
	 *
	 * @return mixed
	 */
	public function get_version() {
		$this->get_data( 'version' );
	}
}
