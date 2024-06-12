<?php
/**
 * Plugin Name: Simple Product Tabs for WooCommerce
 * Plugin URI: https://solbox.dev/plugins/simple-product-tabs-for-woocommerce/
 * Description: Boost your sale by adding custom tabs containing extra information.
 * Version: 1.1.0
 * Author: Solution Box
 * Author URI: https://solbox.dev/
 * Text Domain: simple-product-tabs-for-woocommerce
 * Domain Path: /languages
 * Requires PHP: 7.4
 * Requires at least: 6.0
 * Tested up to: 6.4.3
 * Requires Plugins: woocommerce
 * WC requires at least: 6.5
 * WC tested up to: 8.7.0
 *
 * Copyright:       Solution Box
 * License:         GNU General Public License v3.0
 * License URI:     https://www.gnu.org/licenses/gpl.html
 *
 * @package Solution_Box\simple-product-tabs-for-woocommerce
 */

namespace Solution_Box\Plugin\Simple_Product_Tabs;

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const SPTB_PLUGIN_VERSION = '1.1.0';
const SPTB_PLUGIN_FILE    = __FILE__;
const SPTB_PLUGIN_DIR     = __DIR__;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Helper function to access the shared plugin instance.
 *
 * @return Plugin
 */
function simple_woo_tabs() {
	return Plugin_Factory::create( SPTB_PLUGIN_FILE, SPTB_PLUGIN_VERSION );
}

simple_woo_tabs()->register();
