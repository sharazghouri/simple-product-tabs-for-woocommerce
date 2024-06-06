<?php
/**
 * WordPress Settings Framework
 *
 * @link https://github.com/gilbitron/WordPress-Settings-Framework
 * @package sbsa
 */
use Solution_Box\Plugin\Simple_Product_Tabs\Admin\Admin_Controller;
use Solution_Box\Plugin\Simple_Product_Tabs\Util;


/**
 * Define your settings
 *
 * The first parameter of this filter should be sbsa_register_settings_[options_group],
 * in this case "my_example_settings".
 *
 * Your "options_group" is the second param you use when running new WordPressSettingsFramework()
 * from your init function. It's important as it differentiates your options from others.
 *
 * To use the tabbed example, simply change the second param in the filter below to 'sbsa_tabbed_settings'
 * and check out the tabbed settings function on line 156.
 */

 add_filter( 'sbsa_register_settings_' . Admin_Controller::SETTING_SLUG, 'sptb_tabbed_settings' );


/**
 * Tabbed example.
 *
 * @param array $sbsa_settings settings.
 */
function sptb_tabbed_settings( $sbsa_settings ) {

	$pro_link = array(
		'url'      => esc_url( Util::PRO_LINK ),
		'type'     => 'pro-link', // Can be 'tooltip', 'pro-link'or 'link'. Default is 'tooltip'.
		'text'     => 'Pro', // Default is 'Learn More'.
		'external' => true, // Default is `true`.
	);

	$plugin_factory      = '\Solution_Box\Plugin\Simple_Product_Tabs\Plugin_Factory';
	$simple_product_tabs = array();

	if ( ! empty( $_product_tabs = $plugin_factory::$plugin->admin->product_tabs_list ) ) {
		foreach ( $_product_tabs as $k => $item ) {
			$simple_product_tabs[ $item->post_name ] = $item->post_title;

		}
	}
	// Define a function to create a tab object for better reusability
	function create_tab( $id, $title ) {
		return array( $id => $title );
	}

	// Create tab objects with localization and priority settings
	$description = create_tab( 'description', 'Description' );
	$info        = create_tab( 'additional_information', 'Additional Information' );
	$review      = create_tab( 'reviews', 'Reviews' );

	// Add tabs to the $tab_posts array using array_merge
	$simple_product_tabs = array_merge( $simple_product_tabs, $description, $info, $review );

	// Tabs.
	$sbsa_settings['tabs'] = array(
		array(
			'id'    => 'product_tabs',
			'title' => esc_html__( 'Product Tabs', 'simple-product-tabs' ),
			'link'  => admin_url( 'edit.php?post_type=woo_product_tabs' ),
		),
		array(
			'id'    => 'settings',
			'title' => esc_html__( 'Settings', 'simple-product-tabs' ),
		),
		array(
			'id'    => 'reorder',
			'title' => esc_html__( 'Reorder', 'simple-product-tabs' ),

		),
	);

	// Settings.
	$sbsa_settings['sections'] = array(
		array(
			'tab_id'        => 'product_tabs',
			'section_id'    => 'section_1',
			'section_title' => 'Section 1',
			'section_order' => 10,
			'fields'        => array(),
		),
		array(
			'tab_id'        => 'settings',
			'section_id'    => 'section_3',
			'section_title' => 'Core WooCommerce Tabs Settings',
			'section_order' => 10,
			'fields'        => array(
				array(
					'id'          => 'description_tab_title',
					'name'        => 'description_tab_title',
					'title'       => 'Description',
					'type'        => 'text',
					'placeholder' => 'Description',
				),
				array(
					'id'      => 'hide_description',
					'name'    => 'hide_description',
					'title'   => __( 'Hide Description', 'simple-woo-tabs' ),
					'type'    => 'toggle',
					'default' => false,
					'link'    => $pro_link,
				),
				array(
					'id'          => 'information_tab_title',
					'name'        => 'information_tab_title',
					'title'       => 'Additional Information	',
					'type'        => 'text',
					'placeholder' => 'Additional Information',
				),
				array(
					'id'      => 'hide_information',
					'name'    => 'hide_information',
					'title'   => __( 'Hide Additional Information', 'simple-woo-tabs' ),
					'type'    => 'toggle',
					'default' => false,
					'link'    => $pro_link,
				),
				array(
					'id'          => 'review_tab_title',
					'name'        => 'review_tab_title',
					'title'       => __( 'Review', 'simple-woo-tabs' ),
					'type'        => 'text',
					'placeholder' => 'Review',
				),
				array(
					'id'      => 'hide_reviews',
					'name'    => 'hide_reviews',
					'title'   => __( 'Hide Reviews', 'simple-woo-tabs' ),
					'type'    => 'toggle',
					'default' => false,
					'link'    => $pro_link,
				),
				array(
					'id'      => 'desc_tab_icon',
					'name'    => 'desc_tab_icon',
					'title'   => __( 'Description Tab Icon', 'simple-woo-tabs' ),
					'type'    => 'icon',
					'default' => false,
					'link'    => $pro_link,
				),
				array(
					'id'      => 'add_info_tab_icon',
					'name'    => 'add_info_tab_icon',
					'title'   => __( 'Additional information Tab Icon', 'simple-woo-tabs' ),
					'type'    => 'icon',
					'default' => false,
					'link'    => $pro_link,
				),
				array(
					'id'      => 'desc_tab_icon',
					'name'    => 'desc_tab_icon',
					'title'   => __( 'Description Tab Icon', 'simple-woo-tabs' ),
					'type'    => 'icon',
					'default' => false,
					'link'    => $pro_link,
				),
				array(
					'id'      => 'review_tab_icon',
					'name'    => 'review_tab_icon',
					'title'   => __( 'Reviews Tab Icon', 'simple-woo-tabs' ),
					'type'    => 'icon',
					'default' => false,
					'link'    => $pro_link,
				),
			),
		),
		array(
			'tab_id'        => 'settings',
			'section_id'    => 'section_4',
			'section_title' => __( 'Other Options', 'simple-woo-tabs' ),
			'section_order' => 11,
			'fields'        => array(
				array(
					'id'       => 'search_by_tabs',
					'name'     => 'search_by_tabs',
					'title'    => 'Search By Tabs Content',
					'type'     => 'toggle',
					'subtitle' => __( 'Enhance the product search by adding product tabs title and content.', 'simple-woo-tabs' ),
					'link'     => $pro_link,
				),
				array(
					'id'       => 'page_builder_support',
					'name'     => 'page_builder_support',
					'title'    => 'Enable Page Builder Support',
					'type'     => 'toggle',
					'subtitle' => __( 'Disable <pre style="display:inline-block;margin:0">the_content</pre> filter if you are having issue in tab content while using page builders.', 'simple-woo-tabs' ),
					'link'     => $pro_link,
				),
			),
		),
		array(
			'tab_id'        => 'reorder',
			'section_id'    => 'section_5',
			'section_title' => __( 'Reorder The Tabs', 'simple-woo-tabs' ),
			'section_order' => 11,
			'fields'        => array(
				array(
					'id'      => 'tabs_order',
					'name'    => 'tabs_order',
					'title'   => __( 'Reorder', 'simple-woo-tabs' ),
					'type'    => 'sortable_list',
					'choices' => $simple_product_tabs,
					'link'    => $pro_link,
				),
			),
		),
	);

	return apply_filters( Admin_Controller::SETTING_SLUG . '_settings', $sbsa_settings, );
}
