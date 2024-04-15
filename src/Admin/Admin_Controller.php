<?php

namespace Solution_Box\Plugin\Simple_Product_Tabs\Admin;

use Solution_Box\Plugin\Simple_Product_Tabs\Plugin;

use  SolutionBoxSettings as Settings_API_Helper;

use const Solution_Box\Plugin\Simple_Product_Tabs\SWT_PLUGIN_FILE;

/**
 * Handles the admin functions.
 *
 * @package   Solution_Box\simple-woo-tabs
 */
class Admin_Controller {


	private $plugin;
	private $plugin_name;
	private $version;
	private $settings_page;

	const SETTING_SLUG = 'simple_woo_tabs';


	public function __construct( Plugin $plugin ) {
		$this->plugin      = $plugin;
		$this->plugin_name = $plugin->get_slug();
		$this->version     = $plugin->get_version();
		$this->add_services();
	}

	public function register() {

		// Extra links on Plugins page
		add_filter( 'plugin_action_links_' . $this->plugin->get_basename(), array( $this, 'add_settings_link' ) );
		add_filter( 'plugin_row_meta', array( $this, 'add_meta_links' ), 10, 2 );

		// Admin scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'settings_page_scripts' ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function add_services() {

		$this->settings_api = new Settings_API_Helper\SettingsAPI( plugin_dir_path( $this->plugin->get_data( 'file' ) ) . 'src/Admin/Settings/example.php', self::SETTING_SLUG );
		// Add admin menu
		add_action( 'admin_menu', array( $this, 'add_settings_page' ), 20 );

	}

	/**
	 * Add WooCommerce sub settings page.
	 */
	public function add_settings_page() {
		$this->settings_api->add_settings_page(
			array(
				'parent_slug' => 'woocommerce',
				'page_title'  => __( 'Woocommerce Product Tabs', 'simple-woo-tabs' ),
				'menu_title'  => __( 'Woocommerce Product Tabs', 'simple-woo-tabs' ),
				'capability'  => 'manage_woocommerce',
			)
		);
	}

	/**
	 * Adds a setting link on the Plugins list.
	 *
	 * @param array $links
	 * @return array
	 */
	public function add_settings_link( $links ) {
		array_unshift(
			$links,
			sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $this->plugin->get_settings_page_url() ),
				esc_html__( 'Settings', 'simple-woo-tabs' )
			)
		);
		return $links;
	}

	/**
	 * Adds a Pro version link on the Plugins list.
	 *
	 * @param array  $links
	 * @param string $file
	 * @return array
	 */
	public function add_meta_links( $links, $file ) {
		if ( $file === $this->plugin->get_basename() ) {
			$links[] = sprintf(
				'<a href="%1$s" target="_blank">%2$s</a>',
				esc_url( 'https://barn2.com/kb/simple-woo-tabs-free-documentation/' ),
				esc_html__( 'Docs', 'simple-woo-tabs' )
			);

			$links[] = sprintf(
				'<a href="%1$s" target="_blank"><strong>%2$s</strong></a>',
				esc_url( 'https://barn2.com/wordpress-plugins/simple-woo-tabs/?utm_source=settings&utm_medium=settings&utm_campaign=pluginsadmin&utm_content=wta-plugins' ),
				esc_html__( 'Pro version', 'simple-woo-tabs' )
			);
		}

		return $links;
	}

	/**
	 * Enqueue the admin scripts and styles.
	 *
	 * @param string $hook
	 */
	public function settings_page_scripts( $hook ) {
		$screen = get_current_screen();

		$screen_ids = array( 'edit-woo_product_tab', 'admin_page_wta_settings', 'woo_product_tab' );
		if ( in_array( $screen->id, $screen_ids ) ) {
			wp_enqueue_script( $this->plugin_name . '-settings', plugin_dir_url( __DIR__ ) . '../assets/js/admin/settings.js', array( 'jquery', 'wp-element', 'wp-api-fetch' ), $this->version, true );
		}

		if ( in_array( $screen->id, $screen_ids ) || ( $screen->id === 'product' && ! isset( $_GET['page'] ) ) ) {
			wp_enqueue_style( $this->plugin_name . '-tab', plugin_dir_url( __DIR__ ) . '../assets/css/admin/tab.css', array(), $this->version, 'all' );

		}
		if ( $screen->id === 'product' && ! isset( $_GET['page'] ) ) {
			wp_enqueue_script( $this->plugin_name . '-product', plugin_dir_url( __DIR__ ) . '../assets/js/admin/product.js', array( 'jquery' ), $this->version, true );
		}

		if ( $screen->id === 'toplevel_page_simple-woo-tabs-setup-wizard' ) {
			wp_enqueue_style( $this->plugin_name . '-tab', plugin_dir_url( __DIR__ ) . '../assets/css/admin/wizard.css', array(), $this->version, 'all' );
			wp_enqueue_editor();
		}

		// Manually enqueue the promo style for the settings page
		if ( $screen->id === 'admin_page_wta_settings' ) {
			wp_enqueue_style( 'barn2-plugins-promo', \plugins_url( 'dependencies/barn2/barn2-lib/build/css/plugin-promo-styles.css', $this->plugin->get_file() ), array(), $this->plugin->get_version(), 'all' );
		}

	}

}
