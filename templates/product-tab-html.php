<?php
use Solution_Box\Plugin\Simple_Product_Tabs\Util;

defined( 'ABSPATH' ) || exit;
?>
<div id="simple-product-tabs-for-woocommerce" class="panel woocommerce_options_panel">
	<?php
	$post_id  = get_the_ID();
	$cat_list = wp_get_post_terms( $post_id, 'product_cat', array( 'fields' => 'ids' ) );

	$required_tabs = $this->product_tabs_list;

	if ( ! empty( $required_tabs ) ) {
		echo '<div class="tab-content-wrap">';
		foreach ( $required_tabs as $key => $tab ) {

			if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
				$lang     = ICL_LANGUAGE_CODE;
				$tab_lang = wpml_get_language_information( '', $tab->ID );
			}
			$show = true;
			if ( 'yes' === $tab->_sptb_display_tab_globally ) {
				$show = true;
			} else {
				if ( empty( $tab->_sptb_conditions_category ) ) {
					$show = true;
				} else {

					if ( ! empty( $tab->_sptb_conditions_category ) && is_array( $tab->_sptb_conditions_category ) && array_intersect( $cat_list, Util::get_all_categories( $tab->_sptb_conditions_category ) ) ) {
						$show = true;
					} else {
						$show = false;
					}
				}
			}

			if ( $show === false ) {
				unset( $tab );
			} elseif ( defined( 'ICL_SITEPRESS_VERSION' ) && $lang !== $tab_lang['language_code'] ) {
				unset( $tab );
			} else {

				echo '<h4 class="sptb_accordion">' . esc_html( $tab->post_title ) . '</h4>';
				$tab_value = get_post_meta( $post_id, '_sptb_field_' . $tab->post_name, true );

				if ( empty( $tab_value ) ) {
					$tab_value = $tab->post_content;
				}

				$settings = array(
					'textarea_name' => '_sptb_field_' . $tab->post_name,
					'editor_height' => '150px',
					'editor_class'  => 'test-class',
				);
				echo '<div class="tab-container hidden">';

				$override_value = Util::is_tab_overridden( $tab->post_name, $post_id ) ? 'yes' : 'no';

				// Checking this option would enable the content
				$args = array(
					'label'         => __( 'Override the default tab content for this product', 'simple-product-tabs-for-woocommerce' ),
					'id'            => '_sptb_override_' . $tab->post_name,
					'name'          => '_sptb_override_' . $tab->post_name,
					'class'         => 'override-tab-content',
					'wrapper_class' => 'override-tab-content-label',
					'value'         => $override_value,
				);
				woocommerce_wp_checkbox( $args );

				wp_editor( $tab_value, '_sptb_field_' . esc_attr( $tab->post_name ), $settings );
				echo '<div class="edit-tab-product edit-tab-footer">';
				echo '<a class="edit-global-tab" target="_blank" href="' . get_edit_post_link( $tab->ID ) . '"><span class="dashicons dashicons-edit"></span> ' . __( 'Manage global tab', 'simple-product-tabs-for-woocommerce' ) . '</a>';
				echo '</div></div><br />';
			}
		}
		echo '</div>';

	}
	?>

	<input type="hidden" name="count" value="0" id="count">
	<?php wp_nonce_field( 'sptb_product_data', '_sptb_product_data_nonce' ); ?>
	<div class="tabs-layout hidden">
		<?php
		woocommerce_wp_text_input(
			array(
				'label'       => '',
				'id'          => 'hidden_duplicate_title',
				'placeholder' => 'Title',
				'class'       => 'tab_title_field',
			)
		);

		woocommerce_wp_textarea_input(
			array(
				'label' => '',
				'id'    => 'hidden_duplicate_content',
				'class' => 'tabs_content_field',
			)
		);

		echo '<div class="tab-divider"></div>';
		?>
	</div>

</div>
