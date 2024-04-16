<?php
namespace Solution_Box\Plugin\Simple_Product_Tabs;

use WP_Embed;

/**
 * Show the tabs on the single product page
 *
 * @package   Solution_Box/simple-woo-tabs
 */
class Product_Tabs {

	public function register() {
		// Public custom hooks
		add_filter( 'woocommerce_product_tabs', array( $this, 'custom_woocommerce_product_tabs' ), 20 );

		if ( ! apply_filters( 'swt_enable_the_content_filters', false ) ) {
			add_filter( 'swt_use_the_content_filter', '__return_false' );
			add_filter( 'swt_filter_tab_content', array( $this, 'product_tabs_filter_content' ), 10, 1 );
		}
	}

	public function custom_woocommerce_product_tabs( $tabs ) {
		global $product;

		$this->product_tabs_list = get_posts(
			array(
				'post_type'      => Post_Type::POST_SLUG,
				'posts_per_page' => -1,
				'orderby'        => 'menu_order',
				'order'          => 'asc',
			)
		);

		if ( ! empty( $this->product_tabs_list ) ) {
			foreach ( $this->product_tabs_list as $key => $t ) {
				$this->product_tabs_list[ $key ]->post_meta = get_post_meta( $this->product_tabs_list[ $key ]->ID );
			}
		}

		if ( empty( $this->product_tabs_list ) ) {
			return $tabs;
		}

		$swt_tabs = array();
		foreach ( $this->product_tabs_list as $key => $prd ) {
			$swt_tabs[ $key ]['id']       = $prd->post_name;
			$swt_tabs[ $key ]['title']    = esc_attr( $prd->post_title );
			$swt_tabs[ $key ]['priority'] = esc_attr( $prd->menu_order );
		}

		$swt_tabs = apply_filters( 'swt_filter_product_tabs', $swt_tabs );

		if ( ! empty( $swt_tabs ) ) {

			foreach ( $swt_tabs as $key => $tab ) {
				$tab_temp             = array();
				$tab_temp['title']    = $tab['title'];
				$tab_temp['priority'] = $tab['priority'];
				$tab_temp['callback'] = array( $this, 'callback' );
				$tabs[ $tab['id'] ]   = $tab_temp;
			}
		}

		return $tabs;

	}



	public function callback( $key, $tab ) {
		global $product;

		$tab_post = get_page_by_path( $key, OBJECT, 'woo_product_tabs' );
		if ( empty( $tab_post ) ) {
			return;
		}

			// Display default tab content.
			echo $this->get_filter_content( $tab_post->post_content );

	}

	/**
	 * Filter the tab content.
	 *
	 * @param string $content Content for the current tab.
	 * @return string Tab content.
	 * @since 1.0.0
	 */
	public function product_tabs_filter_content( $content ) {
		$content = function_exists( 'capital_P_dangit' ) ? capital_P_dangit( $content ) : $content;
		$content = function_exists( 'swtexturize' ) ? swtexturize( $content ) : $content;
		$content = function_exists( 'convert_smilies' ) ? convert_smilies( $content ) : $content;
		$content = function_exists( 'wpautop' ) ? wpautop( $content ) : $content;
		$content = function_exists( 'shortcode_unautop' ) ? shortcode_unautop( $content ) : $content;
		$content = function_exists( 'prepend_attachment' ) ? prepend_attachment( $content ) : $content;
		$content = function_exists( 'wp_filter_content_tags' ) ? wp_filter_content_tags( $content ) : $content;
		$content = function_exists( 'do_shortcode' ) ? do_shortcode( $content ) : $content;

		if ( class_exists( 'WP_Embed' ) ) {
			$embed   = new \WP_Embed();
			$content = method_exists( $embed, 'autoembed' ) ? $embed->autoembed( $content ) : $content;
		}

		return $content;
	}

	/**
	 * Get filter for the content.
	 *
	 * @param string $content Content to apply filter.
	 * @return string $content Tab content.
	 * @since 1.0.0
	 */
	public function get_filter_content( $content ) {
		$use_the_content_filter = apply_filters( 'swt_use_the_content_filter', true );

		if ( $use_the_content_filter === true ) {
			$content = apply_filters( 'the_content', $content );
		} else {
			$content = apply_filters( 'swt_filter_tab_content', $content );
		}
		return $content;
	}

	/**
	 * Check to enable custom filter for the content.
	 *
	 * @since 1.0.0
	 */
	public function enable_the_content_filter() {
		$disable_the_content_filter = Util::get_option( 'disable_content_filter' );
		$output                     = false;

		if ( empty( $disable_the_content_filter ) || $disable_the_content_filter !== '1' ) {
			$output = false;
		} else {
			$output = true;
		}

		return $output;
	}

}
