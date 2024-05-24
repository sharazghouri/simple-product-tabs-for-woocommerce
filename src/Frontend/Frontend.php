<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Woocommerce_Product_Tabs
 */

namespace Solution_Box\Plugin\Simple_Product_Tabs\Frontend;

use Solution_Box\Plugin\Simple_Product_Tabs\Post_Type;
use Solution_Box\Plugin\Simple_Product_Tabs\Util;

/**
 * The public-facing functionality of the plugin.
 */
class Frontend {

	/**
	 * The main plugin instance.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $plugin    The main plugin instance.
	 */
	private $plugin;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	private $product_tabs_list;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name       The name of the plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin ) {


		$this->plugin            = $plugin;
		$this->plugin_name       = $plugin->get_slug();
		$this->version           = $plugin->get_version();
		$this->product_tabs_list = get_posts(
			[
				'post_type'        => Post_Type::POST_SLUG,
				'posts_per_page'   => -1,
				'orderby'          => 'menu_order',
				'order'            => 'asc',
				'suppress_filters' => 0,
			]
		);


		if ( ! empty( $this->product_tabs_list ) ) {
			foreach ( $this->product_tabs_list as $key => $t ) {
				$this->product_tabs_list[ $key ]->post_meta = get_post_meta( $t->ID );
			}
		}


		if ( $this->enable_the_content_filter() ) {
			add_filter( 'sptb_use_the_content_filter', '__return_false' );
			add_filter( 'sptb_filter_tab_content', [ $this, 'product_tabs_filter_content' ], 10, 1 );
		}

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function register() {
		// Public custom hooks
		add_filter( 'woocommerce_product_tabs', [ $this, 'custom_woocommerce_product_tabs' ], 60 );


		// Public Search by meta query
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_files' ] );
	}



	public function custom_woocommerce_product_tabs( $tabs ) {
		global $product;


		$sptb_tabs = [];
		if ( ! empty( $this->product_tabs_list ) ) {
			foreach ( $this->product_tabs_list as $key => $prd ) {

				$sptb_tabs[ $key ]['id']                  = $prd->post_name;
				$sptb_tabs[ $key ]['tab_id']              = esc_attr( $prd->ID );
				$sptb_tabs[ $key ]['title']               = esc_attr( $prd->post_title );
				$sptb_tabs[ $key ]['priority']            = esc_attr( $prd->menu_order );
				$sptb_tabs[ $key ]['conditions_category'] = get_post_meta( $prd->ID, '_sptb_conditions_category', true );
				$sptb_tabs[ $key ]['display_globally']    = esc_attr( Util::is_tab_global( $prd->ID ) );
				$sptb_tabs[ $key ] = apply_filters( 'sptb_filter_product_tab', $sptb_tabs[ $key ] );
			}
		}


		$sptb_tabs = $this->tab_status_check( $sptb_tabs );

		if ( ! empty( $sptb_tabs ) ) {

			foreach ( $sptb_tabs as $key => $tab ) {

				$span_icon = '';
				if ( ! empty( $tab['tab_icon'] ) ) {
					$span_icon = '<span class="' . $tab['tab_icon'] . '"></span> ';
				}

				$tab_temp             = [];
				$tab_temp['title']    = $span_icon . $tab['title'];
				$tab_temp['priority'] = $tab['priority'];
				$tab_temp['callback'] = [ $this, 'callback' ];
				$tabs[ $tab['id'] ]   = $tab_temp;
			}
		}
		$tabs_reorder = Util::get_option( 'tabs_order' );

		$tabs_reorder = explode(',', $tabs_reorder );


		$priority = 10;

		foreach( $tabs_reorder as $_tab ) {

			$tabs[ $_tab ]['priority'] = $priority;
			$priority += 10;
		}



		if ( ! empty( $tabs['description']['title'] ) ) {

			//Maybe update the title
			if ( ! empty( $tab_title = Util::get_option( 'description_tab_title' ) ) ) {
				$tabs['description']['title'] = $tab_title;
			}

			$des_icon = '';
				//Maybe Add the icon
			if ( ! empty( $tab_icon = Util::get_option( 'desc_tab_icon' ) ) ) {
				$des_icon = '<span class="' . $tab_icon . '"></span> ';
			}

			$tabs['description']['title'] = $des_icon . $tabs['description']['title'];
			
		}

		if ( ! empty( $tabs['additional_information']['title'] ) ) {

			if ( ! empty(  $tab_title = Util::get_option( 'information_tab_title' ) ) ) {
				$tabs['additional_information']['title'] = $tab_title;
			}

			$info_icon = '';
			if ( ! empty( $tab_icon = Util::get_option( 'add_info_tab_icon' ) ) ) {
				$info_icon = '<span class="' . $tab_icon . '"></span> ';
			}

			$tabs['additional_information']['title'] = $info_icon . $tabs['additional_information']['title'];
			
		}

		if ( ! empty( $tabs['reviews']['title'] ) ) {

			if ( ! empty( $tab_title = Util::get_option( 'review_tab_title' ) ) ) {
				$tabs['reviews']['title'] = $tab_title;
			}

			$review_icon = '';
			if ( ! empty( $tab_icon = Util::get_option( 'review_tab_icon' ) ) ) {
				$review_icon = '<span class="' . $tab_icon. '"></span>' ;
			}

			$tabs['reviews']['title'] = $review_icon . $tabs['reviews']['title'];

		
		}


		 
		if (Util::get_option( 'hide_description' ) ) {
			unset( $tabs['description'] );
		}

		if (Util::get_option( 'hide_information' ) ) {
			unset( $tabs['additional_information'] );
		}

		if (Util::get_option( 'hide_reviews' ) ) {
			unset( $tabs['reviews'] );
		}



		return $tabs;

	}

	public function tab_status_check( $tabs ) {

		global $product;

		if ( ! empty( $tabs ) && is_array( $tabs ) ) {

			foreach ( $tabs as $tab_key => $tab ) {
				$key = $tab['id'];
				if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {

					$tab_lang = wpml_get_language_information( '', $tab['tab_id'] );
					$lang     = ICL_LANGUAGE_CODE;

					if ( $lang !== $tab_lang['language_code'] ) {
						unset( $tabs[ $tab_key ] );
					}
				}

				$tab_post = get_page_by_path( $key, OBJECT, Post_Type::POST_SLUG );

				if ( ! empty( $tab_post ) ) {

					$tab_default_value = $tab_post->post_content;

					$content_to_show = $tab_default_value;

					if ( Util::is_tab_overridden( $tab_post->name , $product->get_id() ) ) {
						$tab_value = get_post_meta( $product->get_id(), '_sptb_field_' . $key, true );
						if ( ! empty( $tab_value ) ) {
							$content_to_show = $tab_value;
						}
					}

					if ( empty( $content_to_show ) ) {
						unset( $tabs[ $tab_key ] );
					}

					

					if ( ! $tab[ 'display_globally' ] ) {
						
						// check category condition
						$cat_list = wp_get_post_terms( $product->get_id(), 'product_cat', [ 'fields' => 'ids' ] );

						$show = false;

						// It means the tab has to show globally. 
						if ( ! empty( $tab['conditions_category'] ) && is_array( $tab['conditions_category'] ) &&  array_intersect( $cat_list, $tab['conditions_category'] ) ) {
							$show = true;
						}
						$show = apply_filters( 'sptb_visibility_check', $show , $tab ) ;

						if( ! $show ) {
							
							unset( $tabs[ $tab_key ] );
						}
						
					}
				}
			} // end foreach
		}
		return $tabs;

	}

	public function callback( $key, $tab ) {

		global $product;

		$tab_post = get_page_by_path( $key, OBJECT, Post_Type::POST_SLUG );
		if ( empty( $tab_post ) ) {
			return;
		}

		$override_content = Util::is_tab_overridden( $key, $product->get_id() );

		if ( ! $override_content ) {
			// Display default tab content.
			echo $this->get_filter_content( $tab_post->post_content );
		} else {
			$tab_value = get_post_meta( $product->get_id(), '_sptb_field_' . $key, true );
			echo $this->get_filter_content( $tab_value );
		}

		return;

	}


	function enqueue_files() {

		if ( is_singular( 'product' ) ) {
			wp_enqueue_style( $this->plugin_name . '-fontawesome', plugin_dir_url( __DIR__ ) . '../vendor/solutionbox/wordpress-settings-framework/src/assets/vendor/fontawesome/css/all.min.css' , [], 	'6.5.2', 'all' );

			wp_enqueue_style( $this->plugin_name . '-public', plugin_dir_url( __DIR__ ) . '../assets/css/public.css' , [  $this->plugin_name . '-fontawesome' ], $this->version, 'all' );
		}

	}

	/**
	 * Filter the tab content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content Content for the current tab.
	 * @return string Tab content.
	 */
	public function product_tabs_filter_content( $content ) {
		$content = function_exists( 'capital_P_dangit' ) ? capital_P_dangit( $content ) : $content;
		$content = function_exists( 'wptexturize' ) ? wptexturize( $content ) : $content;
		$content = function_exists( 'convert_smilies' ) ? convert_smilies( $content ) : $content;
		$content = function_exists( 'wpautop' ) ? wpautop( $content ) : $content;
		$content = function_exists( 'shortcode_unautop' ) ? shortcode_unautop( $content ) : $content;
		$content = function_exists( 'prepend_attachment' ) ? prepend_attachment( $content ) : $content;
		$content = function_exists( 'wp_filter_content_tags' ) ? wp_filter_content_tags( $content ) : $content;
		$content = function_exists( 'do_shortcode' ) ? do_shortcode( $content ) : $content;

		if ( class_exists( '\WP_Embed' ) ) {
			$embed   = new \WP_Embed();
			$content = method_exists( $embed, 'autoembed' ) ? $embed->autoembed( $content ) : $content;
		}

		return $content;
	}

	/**
	 * Get filter for the content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content Content to apply filter.
	 * @return string Tab content.
	 */
	public function get_filter_content( $content ) {
		$use_the_content_filter = apply_filters( 'sptb_use_the_content_filter', true );

		if ( $use_the_content_filter === true ) {
			$content = apply_filters( 'the_content', $content );
		} else {
			$content = apply_filters( 'sptb_filter_tab_content', $content );
		}
		return $content;
	}

	/**
	 * Check to enable custom filter for the content.
	 *
	 * @since 1.0.0
	 */
	public function enable_the_content_filter() {
		$disable_the_content_filter = Util::get_option( 'page_builder_support' );
		
		$output                     = false;

		if ( empty( $disable_the_content_filter ) ) {
			$disable_the_content_filter = false;
		}
		if ( true == $disable_the_content_filter ) {
			$output = true;
		}

		return $output;
	}


}
