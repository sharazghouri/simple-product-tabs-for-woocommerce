<?php

namespace Solution_Box\Plugin\Simple_Product_Tabs\Admin;

use Solution_Box\Plugin\Simple_Product_Tabs\Plugin;
use Solution_Box\Plugin\Simple_Product_Tabs\Post_Type;
use Solution_Box\Plugin\Simple_Product_Tabs\Util;
use  Solution_Box_Settings as Settings_API_Helper;

use const Solution_Box\Plugin\Simple_Product_Tabs\SPTB_PLUGIN_FILE;

/**
 * Handles the admin functions.
 *
 * @package   Solution_Box\simple-product-tabs
 */
class Admin_Controller {


	private $plugin;
	private $plugin_name;
	private $version;
	private $settings_page;
	public $product_tabs_list;

	const SETTING_SLUG = 'simple_product_tabs';

	public function __construct( Plugin $plugin ) {
		$this->plugin      = $plugin;
		$this->plugin_name = $plugin->get_slug();
		$this->version     = $plugin->get_version();

		$this->set_tab_list();
	}

	public function register() {

		// Extra links on Plugins page
		add_filter( 'plugin_action_links_' . $this->plugin->get_basename(), array( $this, 'add_settings_link' ) );
		add_filter( 'plugin_row_meta', array( $this, 'add_meta_links' ), 10, 2 );
		add_filter( 'in_admin_header', array( $this, 'in_admin_header' ) );

		// Admin scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'settings_page_scripts' ) );

		$this->settings_page = new Settings_API_Helper\SettingsAPI( plugin_dir_path( $this->plugin->get_data( 'file' ) ) . 'src/Admin/Settings/settings.php', self::SETTING_SLUG );
		add_action( 'admin_menu', array( $this, 'add_settings_page' ), 20 );

		$this->product_editor_tabs = new Product_Editor_Tabs( $this->plugin->get_data( 'file' ) );
		$this->product_editor_tabs->register();
	}


	/**
	 * Set Tab list.
	 */
	function set_tab_list() {
		$this->product_tabs_list = get_posts(
			array(
				'post_type'      => Post_Type::POST_SLUG,
				'posts_per_page' => -1,
				'order'          => 'asc',
			)
		);

	}
	/**
	 * Add WooCommerce sub settings page.
	 */
	public function add_settings_page() {
		$this->settings_page->add_settings_page(
			array(
				'parent_slug' => 'woocommerce',
				'page_title'  => __( 'Woocommerce Product Tabs', 'simple-product-tabs' ),
				'menu_title'  => __( 'Woocommerce Product Tabs', 'simple-product-tabs' ),
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
				esc_url( $this->plugin->get_data( 'settings_path' ) ),
				esc_html__( 'Settings', 'simple-product-tabs' )
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
				esc_url( 'https://solutionbox.com/kb/simple-product-tabs-free-documentation/' ),
				esc_html__( 'Docs', 'simple-product-tabs' )
			);

			if ( ! Util::is_pro_active() ) {
				$links[] = sprintf(
					'<a href="%1$s" target="_blank"><strong>%2$s</strong></a>',
					esc_url( 'https://solutionbox.com/wordpress-plugins/simple-product-tabs/?utm_source=settings&utm_medium=settings&utm_campaign=pluginsadmin&utm_content=swtplugins' ),
					esc_html__( 'Pro version', 'simple-product-tabs' )
				);
			}
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

		$screen_ids = array( 'edit-woo_product_tabs', 'admin_page_wta_settings', 'woo_product_tabs', 'product' );

		if ( in_array( $screen->id, $screen_ids ) ) {

			wp_enqueue_script( $this->plugin_name . '-settings', plugin_dir_url( __DIR__ ) . '../assets/js/admin.js', array( 'jquery', 'wp-element', 'wp-api-fetch' ), $this->version, true );
			wp_enqueue_style( $this->plugin_name . '-settings', plugin_dir_url( __DIR__ ) . '../assets/css/admin.css', array(), $this->plugin->get_version(), 'all' );

			if ( 'woo_product_tabs' == $screen->id ) {
				$this->settings_page->admin_enqueue_scripts(); // Get from settings framework
				wp_enqueue_style( 'sbsa-fontawesome' ); // Get from settings framework
			}
		}

	}


	public function in_admin_header( $actions ) {
		$current_screen = get_current_screen();

		if ( $current_screen->id !== 'edit-' . Post_Type::POST_SLUG ) {
			return;
		}

		echo $this->get_sptb_admin_header_html();
	}


	public function get_sptb_admin_header_html() {
		?>
		<ul class="sbsa-nav">
			<li class="sbsa-nav__item sbsa-nav__item--active">
				<a class="sbsa-nav__item-link " href="javascript:void(0)"><?php echo __( 'Product Tabs', 'simple-product-tabs' ); ?></a>
			</li>
			<li class="sbsa-nav__item">
				<a class="sbsa-nav__item-link " href="<?php echo admin_url( 'admin.php?page=simple-product-tabs-settings' ); ?>"><?php echo __( 'Settings', 'simple-product-tabs' ); ?></a>
			</li>
		</ul>
		<style>

			.sbsa-nav {
					margin: 0 -20px;
					padding: 0 12px;
					list-style: none none outside;
					background: #fff;
					border-bottom: 1px solid #e2e4e7;
					display: flex;
					flex-wrap: nowrap;
					position: sticky;
					top: 32px;
					z-index: 100;
					align-items: center;
			}

			.sbsa-nav__item {
					display: inline-block;
					margin: 0 8px;
					padding: 0 4px 4px;
					position: relative;
			}
			.sbsa-nav__item-link {
					padding: 15px 0 13px;
					display: block;
					text-decoration: none;
					color: #000;
					white-space: nowrap;
			}
			.sbsa-nav__item:after {
					content: '';
					height: 0;
					transition: height 150ms ease-in-out;
					position: absolute;
					bottom: 0;
					left: 0;
					right: 0;
					backface-visibility: hidden;
					transform: translateZ(0);
			}
			.sbsa-nav__item--active:after {
					height: 4px;
					background: #2271b6;
					border-radius: 4px 4px 0 0;
			}

		</style>
		<?php
	}

}
