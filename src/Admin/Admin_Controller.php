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
	
    // Add an action link pointing to the options page.
		$base_file = basename( dirname( $this->plugin->get_data( 'file' ) ) ) . '/' . $this->plugin_name . '.php';
    
		add_filter( 'plugin_action_links_' . $base_file, [ $this, 'add_plugin_action_links' ] );
	}


	public function add_plugin_action_links( $links ) {
		$output = array_merge(
			[
				'product-tabs'    => '<a href="' . esc_url( admin_url( 'edit.php?post_type=woo_product_tabs' ) ) . '">' . esc_html__( 'Product Tabs', 'woocommerce-product-tabs' ) . '</a>',
				
			],
			$links
		);

		return $output;
	}

}