<?php
namespace Solution_Box\Plugin\Simple_Product_Tabs;

/**
 * Utility functions for Simple Product Tabs.
 *
 * @package   Solution_Box\simple-product-tabs
 */
final class Util {


	/**
	 * Get plugin option.
	 *
	 * @since 1.0.0
	 */
	public static function get_option( $key , $section = '' ) {
		if ( empty( $key ) ) {
			return;
		}

		if( ! empty( $section ) ) {
			$key = "settings_section_{$section}_{$key}";
		}

		$plugin_options =  get_option( 'simple_product_tabs_settings' );

		$value = null;

		if ( isset( $plugin_options[ $key ] ) ) {
			$value = $plugin_options[ $key ];
		}

		return $value;
	}
}
