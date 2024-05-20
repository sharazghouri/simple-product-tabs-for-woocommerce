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


		add_filter( 'sptb_filter_product_tabs', [ $this, 'tab_status_check' ] );

		// Public Search by meta query
		add_filter( 'posts_join', [ $this, 'meta_query_search_join' ] );
		add_filter( 'posts_where', [ $this, 'meta_query_search_where' ], 10, 2 );
		add_filter( 'posts_distinct', [ $this, 'meta_query_search_distinct' ] );
		add_filter( 'posts_clauses', [ $this, 'meta_query_search_where' ], 11, 2 );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_files' ] );
	}

	/**
	 * Get plugin option.
	 *
	 * @since 1.0.0
	 */
	public function get_option( $key ) {
		if ( empty( $key ) ) {
			return;
		}

		$plugin_options = wp_parse_args( (array) get_option( 'sptb_options' ), [ 'description', 'hide_description', 'info', 'hide_info', 'review', 'hide_review', 'search_by_tabs', 'enable_accordion', 'disable_content_filter', 'accordion_shown_size', 'description_icon', 'info_icon', 'review_icon', 'description_priority', 'info_priority', 'review_priority' ] );

		$value = null;

		if ( isset( $plugin_options[ $key ] ) ) {
			$value = $plugin_options[ $key ];
		}

		return $value;
	}

	public function custom_woocommerce_product_tabs( $tabs ) {
		global $product;


		$check_product_review_count = $product->get_review_count();

		$sptb_tabs = [];
		if ( ! empty( $this->product_tabs_list ) ) {
			foreach ( $this->product_tabs_list as $key => $prd ) {

				$sptb_tabs[ $key ]['id']                  = $prd->post_name;
				$sptb_tabs[ $key ]['tab_id']              = esc_attr( $prd->ID );
				$sptb_tabs[ $key ]['title']               = esc_attr( $prd->post_title );
				$sptb_tabs[ $key ]['priority']            = esc_attr( $prd->menu_order );
				$sptb_tabs[ $key ]['conditions_category'] = get_post_meta( $prd->ID, '_sptb_conditions_category', true );
				$sptb_tabs[ $key ]['use_default_for_all'] = esc_attr( get_post_meta( $prd->ID, '_sptb_option_use_default_for_all', true ) );
				$sptb_tabs[ $key ]['conditions_tag']      = wp_get_post_terms( $prd->ID, 'product_tag', [ 'fields' => 'ids' ] );
				$sptb_tabs[ $key ]['conditions_product']  = get_post_meta( $prd->ID, '_sptb_conditions_product', true );
				$sptb_tabs[ $key ]['tab_icon']            = esc_attr( get_post_meta( $prd->ID, '_sptb_tab_icon', true ) );
			}
		}



		$sptb_tabs = apply_filters( 'sptb_filter_product_tabs', $sptb_tabs );

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
		$tabs_reorder = Util::get_option( 'reorder_section_5_tabs_order' );
		$woo_tabs = array( 'description', 'additional_information', 'reviews' );
		

		$tabs_reorder = explode(',', $tabs_reorder );


		$priority = 10;

		foreach( $tabs_reorder as $_tab ) {

			$tabs[ $_tab ]['priority'] = $priority;
			$priority += 10;
		}



		if ( ! empty( $tabs['description']['title'] ) ) {

			//Maybe update the title
			if ( ! empty( $tab_title = Util::get_option( 'settings_section_3_description_tab_title' ) ) ) {
				$tabs['description']['title'] = $tab_title;
			}

			$des_icon = '';
				//Maybe Add the icon
			if ( ! empty( $tab_icon = Util::get_option( 'settings_section_3_desc_tab_icon' ) ) ) {
				$des_icon = '<span class="' . $tab_icon . '"></span> ';
			}

			$tabs['description']['title'] = $des_icon . $tabs['description']['title'];
			
		}

		if ( ! empty( $tabs['additional_information']['title'] ) ) {

			if ( ! empty(  $tab_title = Util::get_option( 'settings_section_3_information_tab_title' ) ) ) {
				$tabs['additional_information']['title'] = $tab_title;
			}

			$info_icon = '';
			if ( ! empty( $tab_icon = Util::get_option( 'settings_section_3_add_info_tab_icon' ) ) ) {
				$info_icon = '<span class="' . $tab_icon . '"></span> ';
			}

			$tabs['additional_information']['title'] = $info_icon . $tabs['additional_information']['title'];
			
		}

		if ( ! empty( $tabs['reviews']['title'] ) ) {

			if ( ! empty( $tab_title = Util::get_option( 'settings_section_3_review_tab_title' ) ) ) {
				$tabs['reviews']['title'] = $tab_title;
			}

			$review_icon = '';
			if ( ! empty( $tab_icon = Util::get_option( 'settings_section_3_review_tab_icon' ) ) ) {
				$review_icon = '<span class="' . $tab_icon. '"></span>' ;
			}

			$tabs['reviews']['title'] = $review_icon . $tabs['reviews']['title'];

		
		}


		 
		// if ( ! empty( $sptb_options['hide_description'] ) && $sptb_options['hide_description'] == 1 ) {
		// 	unset( $tabs['description'] );
		// }

		// if ( ! empty( $sptb_options['hide_info'] ) && $sptb_options['hide_info'] == 1 ) {
		// 	unset( $tabs['additional_information'] );
		// }
		// if ( ! empty( $sptb_options['hide_review'] ) && $sptb_options['hide_review'] == 1 ) {
		// 	unset( $tabs['reviews'] );
		// }

		var_dump( $tabs );
// die;

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

				$tab_post = get_page_by_path( $key, OBJECT, WOOCOMMERCE_PRODUCT_TABS_POST_TYPE_TAB );

				if ( ! empty( $tab_post ) ) {

					$tab_default_value = $tab_post->post_content;

					$content_to_show = $tab_default_value;

					if ( 'yes' != $tab['use_default_for_all'] ) {
						$tab_value = get_post_meta( $product->get_id(), '_sptb_field_' . $key, true );
						if ( ! empty( $tab_value ) ) {
							$content_to_show = $tab_value;
						}
					}

					if ( empty( $content_to_show ) ) {
						unset( $tabs[ $tab_key ] );
					}

					// check category condition
					$cat_list = wp_get_post_terms( $product->get_id(), 'product_cat', [ 'fields' => 'ids' ] );
					$tag_list = wp_get_post_terms( $product->get_id(), 'product_tag', [ 'fields' => 'ids' ] );
					$show     = true;

					if ( empty( $tab['conditions_category'] ) && empty( $tab['conditions_tag'] ) && empty( $tab['conditions_product'] ) ) {
						$show = true;
					} else {

						if ( ! empty( $tab['conditions_category'] ) && is_array( $tab['conditions_category'] ) && array_intersect( $cat_list, $tab['conditions_category'] ) ) {
							$show = true;
						} elseif ( ! empty( $tab['conditions_tag'] ) && is_array( $tab['conditions_tag'] ) && array_intersect( $tag_list, $tab['conditions_tag'] ) ) {
							$show = true;
						} elseif ( ! empty( $tab['conditions_product'] ) && is_array( $tab['conditions_product'] ) && in_array( $product->get_id(), $tab['conditions_product'] ) ) {
							$show = true;
						} else {
							$show = false;
						}
					}

					if ( $show == false ) {
						unset( $tabs[ $tab_key ] );
					}
				}
			} // end foreach
		}
		return $tabs;

	}

	public function callback( $key, $tab ) {

		global $product;

		$tab_post = get_page_by_path( $key, OBJECT, WOOCOMMERCE_PRODUCT_TABS_POST_TYPE_TAB );
		if ( empty( $tab_post ) ) {
			return;
		}
		$flag_sptb_option_use_default_for_all = get_post_meta( $tab_post->ID, '_sptb_option_use_default_for_all', true );
		if ( 'yes' == $flag_sptb_option_use_default_for_all ) {
			// Default content for all
			echo $this->get_filter_content( $tab_post->post_content );
		} else {
			// no default
			$tab_value = get_post_meta( $product->get_id(), '_sptb_field_' . $key, true );
			if ( ! empty( $tab_value ) ) {
				// Value is set for Product
				echo $this->get_filter_content( $tab_value );
			} else {
				// Value is empty; show default
				echo $this->get_filter_content( $tab_post->post_content );
			}
		}
		return;

	}

	function meta_query_search_join( $join ) {
		global $wpdb;

		$search_by_tab = $this->get_option( 'search_by_tabs' );
		if ( is_search() && ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'product' ) && ( ! empty( $search_by_tab ) && $search_by_tab == 1 ) ) {
			$join .= ' LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
		}
		return $join;
	}

	function meta_query_search_where( $where, \WP_Query $query ) {
		global $wpdb;
		$search_by_tab = $this->get_option( 'search_by_tabs' );

		if ( ! is_admin() && $query->is_main_query() && $query->is_search() && ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'product' ) && ( ! empty( $search_by_tab ) && $search_by_tab == 1 ) ) {

			$tabs_args = [
				's'              => sanitize_text_field( $_GET['s'] ),
				'post_type'      => WOOCOMMERCE_PRODUCT_TABS_POST_TYPE_TAB,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			];

			$tabs = get_posts( $tabs_args );

			$selected_p_ids = [];
			if ( ! empty( $tabs ) ) {
				foreach ( $tabs as $tab ) {
					$tab_id = $tab->ID;

					$product_args = [
						'post_type'      => 'product',
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'fields'         => 'ids',
					];

					$use_default_for_all = get_post_meta( $tab_id, '_sptb_option_use_default_for_all', true );

					// check meta key tab in product
					if ( ! empty( $use_default_for_all ) && $use_default_for_all == 'no' ) {
						$product_args['meta_query'] = [
							'relation' => 'AND',
							[
								'key'     => '_sptb_field_' . $tab->post_name,
								'compare' => 'NOT EXISTS'
							]
						];
					}

					$product_ids = get_posts( $product_args );

					$terms       = get_post_meta( $tab_id, '_sptb_conditions_category', true );
					$tags        = wp_get_post_terms( $tab_id, 'product_tag', [ 'fields' => 'ids' ] );
					$tab_product = get_post_meta( $tab_id, '_sptb_conditions_product', true );

					if ( ! empty( $terms ) || ! empty( $tags ) || ! empty( $tab_product ) ) {

						// check tabs product
						if ( ! empty( $tab_product ) ) {
							$selected_p_ids = array_merge( $selected_p_ids, $tab_product );
						}

						if ( ! empty( $terms ) || ! empty( $tags ) ) {
							$tax_query = [ 'relation' => 'OR' ];

							// check tabs terms
							if ( ! empty( $terms ) ) {
								$tax_query[] = [
									'taxonomy' => 'product_cat',
									'field'    => 'term_id',
									'terms'    => $terms,
								];
							}

							// check tabs tag
							if ( ! empty( $tags ) ) {
								$tax_query[] = [
									'taxonomy' => 'product_tag',
									'field'    => 'term_id',
									'terms'    => $tags,
								];
							}

							$product_args['tax_query'] = $tax_query;
							$product_ids               = get_posts( $product_args );

						} else {
							$product_ids = [];
						}
					}

					$selected_p_ids = array_merge( $selected_p_ids, $product_ids );

				}
			}
			$selected_p_ids = array_unique( $selected_p_ids );
			$allow_products = '';
			if ( ! empty( $selected_p_ids ) ) {
				$allow_products = ' OR (' . $wpdb->posts . '.ID IN (' . implode( ',', $selected_p_ids ) . ') )';
			}

			$where = preg_replace(
				'/\(\s*' . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
				'(' . $wpdb->posts . '.post_title LIKE $1) OR (' . $wpdb->postmeta . '.meta_value LIKE $1)' . $allow_products,
				$where
			);

		}

		return $where;
	}

	function meta_query_search_distinct( $where ) {
		global $wpdb;

		$search_by_tab = $this->get_option( 'search_by_tabs' );
		if ( is_search() && ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'product' ) && ( ! empty( $search_by_tab ) && $search_by_tab == 1 ) ) {
			return 'DISTINCT';
		}

		return $where;
	}

	function enqueue_files() {

		if ( is_singular( 'product' ) ) {
			wp_enqueue_style( $this->plugin_name . '-fontawesome', WOOCOMMERCE_PRODUCT_TABS_URL . '/assets/css/font-awesome/all.min.css', [], $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name . '-tab', WOOCOMMERCE_PRODUCT_TABS_URL . '/assets/css/public.css', [], $this->version, 'all' );
			wp_enqueue_script( $this->plugin_name . '-custom', WOOCOMMERCE_PRODUCT_TABS_URL . '/assets/js/public.js', [ 'jquery' ], $this->version, true );

			wp_localize_script(
				$this->plugin_name . '-custom',
				'sptb_LOCALIZED',
				[
					'sptb_acc_enable' => $this->get_option( 'enable_accordion' ),
					'sptb_acc_size'   => $this->get_option( 'accordion_shown_size' )
				]
			);
		}

	}

	/**
	 * Filter the tab content.
	 *
	 * @since 1.0.7
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

		if ( class_exists( 'WP_Embed' ) ) {
			$embed   = new WP_Embed();
			$content = method_exists( $embed, 'autoembed' ) ? $embed->autoembed( $content ) : $content;
		}

		return $content;
	}

	/**
	 * Get filter for the content.
	 *
	 * @since 1.0.7
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
	 * @since 1.0.7
	 */
	public function enable_the_content_filter() {
		$disable_the_content_filter = Util::get_option( 'page_builder_support' , 4);
		
		$output                     = false;

		if ( empty( $disable_the_content_filter ) ) {
			$disable_the_content_filter = false;
		}
		if ( true == $disable_the_content_filter ) {
			$output = true;
		}

		return $output;
	}

	function update_tab_data( $id, $tab, $data ) {

	}

}
