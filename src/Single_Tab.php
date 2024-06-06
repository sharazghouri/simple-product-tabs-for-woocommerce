<?php

namespace Solution_Box\Plugin\Simple_Product_Tabs;

/**
 * Add metaboxes and handles their behavior for the singled edit tab page
 *
 * @package   Solution_Box\simple-product-tabs
 * @author    Solution Box <solutionboxdev@gmail.com>
 */
class Single_Tab {

	public function register() {
			add_action( 'add_meta_boxes', array( $this, 'add_tab_meta_boxes' ) );
			add_action( 'save_post', array( $this, 'save_visibility_condition' ) );
			add_action( 'save_post', array( $this, 'save_category_selector' ) );
			add_action( 'save_post', array( $this, 'save_tab_priority' ) );
			add_action( 'save_post', array( $this, 'save_tab_icon') );
	}

		/**
		 * Categories selector.
		 */
	public function wta_inclusion_categories_selector( $post_id, $times_svg_icon ) {
			$sptb_conditions_category = get_post_meta( $post_id, '_sptb_conditions_category', true );
			$selected_categories     = $this->get_selected_terms( $sptb_conditions_category, 'product_cat' );
		?>
				<div class="swt-categories-selector swt-inclusion-selector">
						<div class="swt-component-search-field">
								<input data-type="category" type="text" data-taxonomy="categories" id="swt-category-search" class="swt-component-search-field-control" placeholder="<?php _e( 'Search for categories', 'simple-product-tabs' ); ?>">
						</div>
						<div class="swt-spinner swt-loader">
								<svg width="18" height="18" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg" stroke="#c3c4c7"> <g fill="none" fillRule="evenodd"> <g transform="translate(1 1)" strokeWidth="2"> <circle strokeOpacity="1" cx="18" cy="18" r="18"/> <path d="M36 18c0-9.94-8.06-18-18-18"> <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"/> </path> </g> </g></svg>
						</div>
						<div class="swt-component-no-results">
								<span><?php _e( 'No categories found', 'simple-product-tabs' ); ?></span>
						</div>
						<ul class="solution-box-search-list__list">
						</ul>
						<div class="solution-box-search-list__selected <?php echo ( $selected_categories ) ? '' : 'swt-hide-selected-terms-section'; ?>">
								<div class="solution-box-search-list__selected-header">
										<strong><?php _e( 'Selected categories', 'simple-product-tabs' ); ?></strong>
									<?php
											printf(
												'<button type="button" aria-label="%1$s" class="solution-box-search-list-clear__all solution-box-remove-inclusions">%1$s</button>',
												__( 'Clear all selected categories', 'simple-product-tabs' ),
											);
									?>
								</div>
								<ul class="solution-box-search-list__selected_terms">
										<?php
										if ( $selected_categories ) {
											foreach ( $selected_categories as $category ) :
												?>
														<li data-term-id="<?php echo $category->term_id; ?>">
																<span class="solution-box-selected-list__tag">
																		<?php
																			printf(
																				'<span class="solution-box-tag__text" id="solution-box-tag__label-%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></span>',
																				$category->term_id,
																				$category->name,
																				$category->name
																			);
																		?>
																		<input type="hidden" name="sptb_category_list[]" value="<?php echo $category->term_id; ?>">
																		
																		<?php
																			printf(
																				'<button type="button" aria-describedby="solution-box-tag__label-%s" class="components-button solution-box-tag__remove" id="solution-box-remove-term" aria-label="%s">',
																				$category->term_id,
																				$category->name
																			);
																			echo $times_svg_icon;
																			echo '</button>';
																		?>
																</span>
														</li>
												<?php
												endforeach;
										}
										?>
								</ul>
						</div>
				</div>

				<?php if( ! Util::is_pro_active() ) { ?>
					<div class="swt-component-search-field disabled">
									<input disabled type="text" class="swt-component-search-field-control" placeholder="<?php _e( 'Search for products', 'simple-product-tabs' ); ?>">
						<a class="pro-version-link" target="_blank" href="<?php echo esc_url( Util::PRO_LINK );?>">
								<?php _e( 'Pro version only', 'simple-product-tabs' ); ?>
						</a>
					</div>
					<div class="swt-component-search-field disabled">
									<input disabled type="text" class="swt-component-search-field-control" placeholder="<?php _e( 'Search for tags', 'simple-product-tabs' ); ?>">
						<a class="pro-version-link" target="_blank" href="<?php echo esc_url( Util::PRO_LINK );?>">
							<?php _e( 'Pro version only', 'simple-product-tabs' ); ?>
						</a>
					</div>
				<?php } else {
					do_action( 'sptb_inclusion_categories_options', $post_id, $times_svg_icon  );
				} ?>
				<?php
	}

		/**
		 * Get inclusion section selected taxonomy terms
		 */
	public function get_selected_terms( $terms_ids, $taxonomy ) {
		if ( empty( $terms_ids ) ) {
				 return;
		}

			$term_args  = array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'fields'     => 'all',
				'count'      => true,
				'include'    => $terms_ids,
			);
			$term_query = new \WP_Term_Query( $term_args );

			return $term_query->terms;
	}

		/**
		 *  Save post category tab.
		 *
		 * @since 1.0.0
		 */
	public function save_category_selector( $post_id ) {
			// Check if our nonce is set.
		if ( ! isset( $_POST['sptb_meta_box_tab_nonce'] ) ) {
				return;
		}
					// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['sptb_meta_box_tab_nonce'], 'sptb_tab_meta_box' ) ) {
				return;
		}

			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
		}

		if ( Post_Type::POST_SLUG != $_POST['post_type'] ) {
				return;
		}

			$sptb_conditions_category = '';
		if ( isset( $_POST['sptb_category_list'] ) && ! empty( $_POST['sptb_category_list'] ) ) {
				$sptb_conditions_category = $_POST['sptb_category_list'];
		}
		if ( ! isset( $_POST['sptb_category_list'] ) ) {
				delete_post_meta( $post_id, '_sptb_conditions_category' );
				update_post_meta( $post_id, '_sptb_display_tab_globally', 'yes' );
					return;
		}
			update_post_meta( $post_id, '_sptb_conditions_category', $sptb_conditions_category );

	}

	/**
	 * Check meta box nonce
	 *
	 * @return boolean
	 */
	public function check_meta_box_nonce(){

		// Check if our nonce is set.
		if ( ! isset( $_POST['sptb_meta_box_tab_nonce'] ) ) {
				return false;
		}
					// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['sptb_meta_box_tab_nonce'], 'sptb_tab_meta_box' ) ) {
				return false;
		}
					// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return false;
		}

		
		if ( Post_Type::POST_SLUG != $_POST['post_type'] ) {
				return false;
		}
		return true;
	}

		/**
		 *  Save product tabs settings.
		 *
		 * @since 1.0.0
		 */
	public function save_visibility_condition( $post_id ) {

		if( ! $this->check_meta_box_nonce() ){
			return false;
		}
		// Show tabs on all products
		$display_globally = '';
		if ( isset( $_POST['_sptb_display_tab_globally'] ) ) {
				$display_globally = $_POST['_sptb_display_tab_globally'];
		} else {
			$display_globally = 'no';
		}


			update_post_meta( $post_id, '_sptb_display_tab_globally', $display_globally );

	}

	/**
	 *  Save product tabs icon.
	 *
	 * @since 1.0.0
	 */
public function save_tab_icon( $post_id ) {



	if ( ! $this->check_meta_box_nonce() ) {
			return;
	}

	$tab_icon = '';
	if ( isset( $_POST['_sptb_tab_icon'] ) ) {
		$tab_icon = sanitize_text_field( wp_unslash( $_POST['_sptb_tab_icon'] ?? '' ) );
			
	}


	update_post_meta( $post_id, '_sptb_tab_icon', $tab_icon );

}

	public function save_tab_priority( $post_id ) {

		if ( ! $this->check_meta_box_nonce() ) {
				return;
		}
			// priority
			$priority = $_POST['_sptb_option_priority'];
			$priority = absint( $priority );

			global $wpdb;
			$sql = $wpdb->prepare(
				'UPDATE ' . $wpdb->posts . ' SET `menu_order`=%d WHERE ID=%d',
				$priority,
				$post_id
			);
		$wpdb->query( $sql );
	}

		/**
		 * Add meta box in product tabs.
		 *
		 * @since 1.0.0
		 */
	public function add_tab_meta_boxes() {
			$screens = array( 'woo_product_tabs' );

		foreach ( $screens as $screen ) {
				// Settings Metabox
				add_meta_box(
					'simple-product-tabs_conditions_section',
					__( 'Conditions', 'simple-product-tabs' ),
					array( $this, 'sptb_conditions_section' ),
					$screen,
					'normal',
					'high'
				);
				add_meta_box(
					'simple-product-tabs_icon_section',
					__( 'Select icon', 'simple-product-tabs' ),
					array( $this, 'sptb_icon_section' ),
					$screen,
					'side',
					'high'
				);
				add_meta_box(
					'simple-product-tabs_priority_section',
					__( 'Settings', 'simple-product-tabs' ),
					array( $this, 'sptb_priority_section' ),
					$screen,
					'side',
				);
		}
	}

		/**
		 * Wether it is global.
		 *
		 * @param mixed $post post object.
		 */
	public function sptb_conditions_section( $post ) {
			$post_id = $post->ID;

			// Add an nonce field so we can check for it later.
			wp_nonce_field( 'sptb_tab_meta_box', 'sptb_meta_box_tab_nonce' );
			$is_tab_global = Util::is_tab_global( $post_id );

			$times_svg_icon = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="clear-icon" aria-hidden="true" focusable="false"><path d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21ZM15.5303 8.46967C15.8232 8.76256 15.8232 9.23744 15.5303 9.53033L13.0607 12L15.5303 14.4697C15.8232 14.7626 15.8232 15.2374 15.5303 15.5303C15.2374 15.8232 14.7626 15.8232 14.4697 15.5303L12 13.0607L9.53033 15.5303C9.23744 15.8232 8.76256 15.8232 8.46967 15.5303C8.17678 15.2374 8.17678 14.7626 8.46967 14.4697L10.9393 12L8.46967 9.53033C8.17678 9.23744 8.17678 8.76256 8.46967 8.46967C8.76256 8.17678 9.23744 8.17678 9.53033 8.46967L12 10.9393L14.4697 8.46967C14.7626 8.17678 15.2374 8.17678 15.5303 8.46967Z"></path></svg>';
		?>
						<table class="form-table visibility-form">
								<tbody>
										<tr>
												<th><?php _e( 'Visibility', 'simple-product-tabs' ); ?></th>
												<td>
														<fieldset>
																<legend class="screen-reader-text"><span><?php _e( 'Visibility', 'simple-product-tabs' ); ?></span></legend>
																<label>
																		<input type="radio" id="_sptb_display_tab_globally" name="_sptb_display_tab_globally" class="sptb_visibility_condition" checked="checked" value="yes" <?php checked( true, $is_tab_global, true ); ?>>
																			<?php _e( 'Display globally on all products', 'simple-product-tabs' ); ?>
																</label><br>
																<label>
																		<input type="radio" id="_sptb_display_tab_globally" name="_sptb_display_tab_globally" class="sptb_visibility_condition" value="no" <?php checked( false, $is_tab_global, true ); ?>>
																			<?php _e( 'Show on specific categories', 'simple-product-tabs' ); ?>
																</label><br>
														</fieldset>
												</td>
										</tr>
								</tbody>
						</table>

						<table id="inclusions-list" class="form-table <?php echo (! $is_tab_global  ) ? '' : 'hide-section'; ?> ">
								<tbody>
										<tr>
												<th><?php _e( 'Inclusions', 'simple-product-tabs' ); ?></th>
												<td class="swt-term-inclusions-section">
													<?php
													$this->wta_inclusion_categories_selector( $post_id, $times_svg_icon );
													?>
												</td>
										</tr>
								</tbody>
						</table>
				<?php
	}

	public function sptb_icon_section( $post ) {
			
		?>	
				<div class="icon-wrap">
				<a href="#" class="tab_icon  button button-secondary  sbsa-browse-icon <?php echo !Util::is_pro_active() ? 'disabled': ''; ?>" data-model="sbsa-modal-icon-product-tab-screen"><?php esc_html_e( 'Select Icon', 'simple-product-tabs' ); ?></a>
				<input type="text" name="_sptb_tab_icon" id="sbsa-browse-icon" value="" class="regular-text hidden sbsa-icon-input ">
				<?php  

				$icon_output = '';
				if( $icon_value = get_post_meta( $post->ID, '_sptb_tab_icon', true ) ) {
					$icon_output = sprintf('<i class="%s"></i><a href="#" class="sbsa-icon-remove">Remove</a>', esc_attr( $icon_value  ), __( 'Remove', 'simple-product-tabs-pro' ) );
				}

				echo sprintf('<div class="sbsa-icon-output">%s</div>', $icon_output );

				$args['id' ] = 'product-tab-screen';
				include_once plugin_dir_path( __FILE__ ) . '../vendor/solutionbox/wordpress-settings-framework/src/includes/icons.php'; ?>
				<?php if( ! Util::is_pro_active() ){ ?>
				<a href="<?php echo esc_url( Util::PRO_LINK ) ;?>" class="pro-version-link" target="_blank"><?php _e( 'Pro version only' ); ?></a>
				<?php } ?>
				</div>
				<?php
	}

	public function sptb_priority_section( $post ) {
			$priority = $post->menu_order;
		echo '<p><label for="_sptb_option_priority"><strong>';
			echo __( 'Priority', 'simple-product-tabs' );
		echo '</strong></label></p>';
			echo '<input type="number" name="_sptb_option_priority" id="_sptb_option_priority" value="' . $priority . '" min="0" style="max-width:70px;"/>';
	}
}
