<?php
namespace Solution_Box\Plugin\Simple_Product_Tabs;

/**
 * Utility functions for WooCommerce Product Tabs.
 *
 * @package   Solution_Box\simple-product-tabs-for-woocommerce
 */
final class Util {



	const PRO_LINK = 'https://solutionbox.com/plugins/simple-product-tabs-for-woocommerce/?utm_source=freemium&utm_medium=plugin-page&utm_campaign=go_pro_tabs';


	/**
	 * Checks the tab against the old version to see if it's global or not.
	 *
	 * @param int $tab_id
	 *
	 * @return string
	 */
	public static function is_tab_global( $tab_id ) {

			return 'yes' == get_post_meta( $tab_id, '_sptb_display_tab_globally', true );

	}

	/**
	 * Combines the list of the categories with their child categories
	 *
	 * @param array $conditions_categories
	 *
	 * @return array
	 */
	public static function get_all_categories( $conditions_categories ) {

		if ( ! $conditions_categories || ! is_array( $conditions_categories ) ) {
			return array();
		}

		if ( is_array( $conditions_categories ) && empty( $conditions_categories ) ) {
			return array();
		}

		$child_categories = array();
		foreach ( $conditions_categories as $category ) {
			$child_terms = get_terms(
				array(
					'child_of'   => $category,
					'hide_empty' => true,
					'taxonomy'   => 'product_cat',
					'fields'     => 'ids',
				)
			);

			if ( is_array( $child_terms ) ) {
				$child_categories = array_unique( array_merge( $child_categories, $child_terms ) );
			}
		}
		return array_unique( array_merge( $conditions_categories, $child_categories ) );
	}

	/**
	 * Determines if a tab has a custom content
	 *
	 * @param string $tab_key
	 * @param int    $product_id
	 *
	 * @return boolean
	 */
	public static function is_tab_overridden( $tab_key, $product_id ) {

		if ( ! $tab_key || ! $product_id ) {
			return false;
		}

		$override_meta = get_post_meta( $product_id, '_sptb_override_' . $tab_key, true );

		return 'yes' === $override_meta;
	}

	/**
	 * Get plugin option.
	 *
	 * @since 1.0.0
	 */
	public static function get_option( $key, $section = '' ) {
		if ( empty( $key ) ) {
			return;
		}

		if ( ! empty( $section ) ) {
			$key = "settings_section_{$section}_{$key}";
		}

		$plugin_options = get_option( 'simple_product_tabs_settings' );

		$value = null;

		if ( isset( $plugin_options[ $key ] ) ) {
			$value = $plugin_options[ $key ];
		}

		return $value;
	}


	public static function tabs_frontend_callback( $key, $tab ) {

		global $product;

		$tab_post = get_page_by_path( $key, OBJECT, Post_Type::POST_SLUG );
		if ( empty( $tab_post ) ) {
			return;
		}

		$override_content = self::is_tab_overridden( $key, $product->get_id() );

		if ( ! $override_content ) {
			// Display default tab content.
			echo $this->get_filter_content( $tab_post->post_content );
		} else {
			$tab_value = get_post_meta( $product->get_id(), '_sptb_field_' . $key, true );
			echo $this->get_filter_content( $tab_value );
		}

		return;

	}


	/**
	 * Whether the pro plugin is active
	 *
	 * @return boolean
	 */
	public static function is_pro_active() {

		return class_exists( '\Solution_Box\Plugin\Simple_Product_Tabs_Pro\Plugin_Pro' );
	}
}
