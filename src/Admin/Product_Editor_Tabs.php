<?php

namespace Solution_Box\Plugin\Simple_Product_Tabs\Admin;

use Solution_Box\Plugin\Simple_Product_Tabs\Post_Type;

/**
 * Add metaboxes and handles their behavior for the singled edit tab page
 *
 * @package   Solution_Box/simple-product-tabs
 */
class Product_Editor_Tabs {

	private $plugin_dir_path;

	/**
	 * List of the tabs related to the current product
	 */
	private $product_tabs_list;

	public function __construct( $plugin_file ) {
		$this->plugin_dir_path   = plugin_dir_path( $plugin_file );
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
	}

	public function register() {
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'simple_woo_tabs_data_tab' ), 99, 1 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'add_simple_woo_tabs_data_fields' ) );
		add_action( 'save_post', array( $this, 'save_product_tab_data' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'insert_tab_menu_order' ), 99, 2 );
		add_action( 'admin_head', array( $this, 'post_type_menu_active' ) );
	}

	/**
	 * Add Product Tabs in Product Page.
	 *
	 * @since 1.0.0
	 */
	function simple_woo_tabs_data_tab( $product_data_tabs ) {
		$product_data_tabs['swtp-product-tab'] = array(
			'label'  => __( 'Product Tabs', 'simple-product-tabs' ),
			'target' => 'simple-product-tabs',
		);
		return $product_data_tabs;
	}

	/**
	 * View product tabs in product page.
	 *
	 * @since 1.0.0
	 */
	function add_simple_woo_tabs_data_fields() {

		include_once $this->plugin_dir_path . 'templates/product-tab-html.php';
	}

	/**
	 *  Save product tabs data form product page.
	 *
	 * @since 1.0.0
	 */
	function save_product_tab_data( $post_id ) {
		$nonce = filter_input( INPUT_POST, '_sptb_product_data_nonce', FILTER_SANITIZE_SPECIAL_CHARS );

		// Verify that the nonce is valid.
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'sptb_product_data' ) ) {
			return;
		}

		if ( 'product' !== filter_input( INPUT_POST, 'post_type', FILTER_SANITIZE_SPECIAL_CHARS ) ) {
			return;
		}

		if ( empty( $this->product_tabs_list ) ) {
			return;
		}

		$posted_tab_data = array_filter(
			$_POST,
			function ( $key ) {
				return '_sptb_field_' === substr( $key, 0, 11 );
			},
			ARRAY_FILTER_USE_KEY
		);

		foreach ( $posted_tab_data as $post_key => $tab_content ) {
			$tab_slug       = substr( $post_key, 11 );
			$override_value = filter_input( INPUT_POST, '_sptb_override_' . $tab_slug, FILTER_SANITIZE_SPECIAL_CHARS );

			if ( 'yes' !== $override_value ) {
				$override_value = 'no';
			}

			update_post_meta( $post_id, '_sptb_override_' . $tab_slug, $override_value );

			if ( 'yes' === $override_value ) {
				// Update the tab content.
				update_post_meta( $post_id, $post_key, wp_kses_post( $tab_content ) );
			} else {
				// If the checkbox is not enabled, delete the tab content post meta.
				delete_post_meta( $post_id, $post_key, '' );
			}
		}
	}

	function insert_tab_menu_order( $data, $postarr ) {
		if ( $data['post_type'] == 'woo_product_tab' && $data['post_status'] == 'auto-draft' ) {
			global $wpdb;
			if ( $wpdb->get_var( "SELECT menu_order FROM {$wpdb->posts} WHERE post_type='woo_product_tab'" ) ) {
				$data['menu_order'] = $wpdb->get_var( "SELECT MAX(menu_order)+1 AS menu_order FROM {$wpdb->posts} WHERE post_type='woo_product_tab'" );
			}
		}
		return $data;
	}

	/**
	 * Add active in menu product tabs
	 *
	 * @since 1.0.0
	 */
	function post_type_menu_active() {
		$screen = get_current_screen();
		if ( $screen->post_type === 'woo_product_tab' ) {
			?>
			<script type="text/javascript">
				jQuery( document ).ready( function() {
					jQuery( 'ul.wp-submenu li a[href*="edit.php?post_type=woo_product_tab"]' ).parent().addClass( 'current' );
				} );
			</script>
			<?php
		}
	}


}
