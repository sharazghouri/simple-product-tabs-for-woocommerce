<?php
/**
 * WordPress Settings Framework
 *
 * @link https://github.com/gilbitron/WordPress-Settings-Framework
 * @package sbsa
 */
use Solution_Box\Plugin\Simple_Product_Tabs\Admin\Admin_Controller;

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

add_filter( 'sbsa_register_settings_' . Admin_Controller::SETTING_SLUG, 'swt_tabbed_settings' );


/**
 * Tabbed example.
 *
 * @param array $sbsa_settings settings.
 */
function swt_tabbed_settings( $sbsa_settings ) {

	$Plugin_Factory = '\Solution_Box\Plugin\Simple_Product_Tabs\Plugin_Factory';
	$tab_list = array();

	if ( ! empty( $t_list =  $Plugin_Factory::$plugin->admin->product_tabs_list ) ) {
		foreach ( $t_list as $key => $t ) {
			var_dump( $t );
			die;
		}
	}

	// Tabs.
	$sbsa_settings['tabs'] = array(
		array(
			'id'    => 'product_tabs',
			'title' => esc_html__( 'Product Tabs', 'text-domain' ),
		),
		array(
			'id'    => 'settings',
			'title' => esc_html__( 'Settings', 'text-domain' ),
		),
		array(
			'id'    => 'reorder',
			'title' => esc_html__( 'Reorder', 'text-domain' ),
		),
		array(
			'id'                => 'license',
			'title'             => esc_html__( 'License', 'text-domain' ),
			'tab_control_group' => 'tab-control',
			'show_if'           => array( // Field will only show if the control `settings_section_2_tab-control` is set to true.
				array(
					'field' => 'settings_section_3_tab-control',
					'value' => array( '1' ),
				),
			),
		),
	);

	// Settings.
	$sbsa_settings['sections'] = array(
		array(
			'tab_id'        => 'product_tabs',
			'section_id'    => 'section_1',
			'section_title' => 'Section 1',
			'section_order' => 10,
			'fields'        => array(
				array(
					'id'      => 'text-1',
					'title'   => 'Text',
					'desc'    => 'This is a description.',
					'type'    => 'text',
					'default' => 'This is default',
				),
			),
		),
		array(
			'tab_id'        => 'product_tabs',
			'section_id'    => 'section_2',
			'section_title' => 'Section 2',
			'section_order' => 10,
			'fields'        => array(
				array(
					'id'      => 'text-2',
					'title'   => 'Text',
					// Format of href is #tab-id|field-id. You can choose to skip the field id.
					'desc'    => 'This is a description. This is a <a href="#tab-settings|settings_section_3_text-3" class="wsf-internal-link">link</a> to a setting in a different tab.',
					'type'    => 'text',
					'default' => 'This is default',
				),
			),
		),
		array(
			'tab_id'        => 'settings',
			'section_id'    => 'section_3',
			'section_title' => 'Core WooCommerce Tabs Settings',
			'section_order' => 10,
			'fields'        => array(
				array(
					'id'          => 'description_tab_title',
					'title'       => 'Description',
					'type'        => 'text',
					'placeholder' => 'Description',
				),
				array(
					'id'      => 'display_description',
					'title'   => __( 'Hide Description', 'simple-product-tabs' ),
					'type'    => 'toggle',
					'default' => false,
				),
				array(
					'id'          => 'information_tab_title',
					'title'       => 'Additional Information	',
					'type'        => 'text',
					'placeholder' => 'Additional Information',
				),
				array(
					'id'      => 'display_information',
					'title'   => __( 'Hide Additional Information', 'simple-product-tabs' ),
					'type'    => 'toggle',
					'default' => false,
				),
				array(
					'id'          => 'review_tab_title',
					'title'       => __( 'Review', 'simple-product-tabs' ),
					'type'        => 'text',
					'placeholder' => 'Review',
				),
				array(
					'id'      => 'Description Tab Icon',
					'title'   => __( 'Description Tab Icon', 'simple-product-tabs' ),
					'type'    => 'toggle',
					'default' => false,
				),
				array(
					'id'      => 'desc_tab_icon',
					'title'   => __( 'Description Tab Icon', 'simple-product-tabs' ),
					'type'    => 'icon',
					'default' => false,
				),
				array(
					'id'      => 'add_info_tab_icon',
					'title'   => __( 'Additional information Tab Icon', 'simple-product-tabs' ),
					'type'    => 'icon',
					'default' => false,
				),
				array(
					'id'      => 'desc_tab_icon',
					'title'   => __( 'Description Tab Icon', 'simple-product-tabs' ),
					'type'    => 'icon',
					'default' => false,
				),
				array(
					'id'      => 'review_tab_icon',
					'title'   => __( 'Description Tab Icon', 'simple-product-tabs' ),
					'type'    => 'icon',
					'default' => false,
				),
			),
		),
		array(
			'tab_id'        => 'settings',
			'section_id'    => 'section_4',
			'section_title' => __( 'Other Options', 'simple-product-tabs' ),
			'section_order' => 11,
			'fields'        => array(
				array(
					'id'       => 'search_by_tabs',
					'title'    => 'Search By Tabs Content',
					'type'     => 'toggle',
					'subtitle' => __( 'Enhance the product search by adding product tabs title and content.', 'simple-product-tabs' ),
				),
				array(
					'id'       => 'page_builder_support',
					'title'    => 'Enable Page Builder Support',
					'type'     => 'toggle',
					'subtitle' => __( 'Disable <pre style="display:inline-block;margin:0">the_content</pre> filter if you are having issue in tab content while using page builders.', 'simple-product-tabs' ),
				),
			),
		),
		array(
			'tab_id'        => 'reorder',
			'section_id'    => 'section_5',
			'section_title' => __( 'Reorder ', 'simple-product-tabs' ),
			'section_order' => 11,
			'fields'        => array(
				array(
					'id'      => 'tabs_order',
					'title'   => 'Products Tabs Order',
					'desc'    => __( 'Reorder the tabs on product page', 'simple-product-tabs' ),
					'type'    => 'sortable_list',
					'default' => '',
					'choices' => array(
						'w1as' => 'One',
						'e2as' => 'Two',
						's3as' => 'Three',
						'd4as' => 'Four',
						'a5as' => 'Five',
					),
				),
			),
		),

	);

	return $sbsa_settings;
}
