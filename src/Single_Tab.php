<?php

namespace Solution_Box\Plugin\Simple_Product_Tabs;

/**
 * Add metaboxes and handles their behavior for the singled edit tab page
 *
 * @package   Solution_Box\simple-woo-tabs
 * @author    Solution Box <solutionboxdev@gmail.com>
 */
class Single_Tab {

	public function register() {
			add_action( 'add_meta_boxes', array( $this, 'add_tab_meta_boxes' ) );
			add_action( 'save_post', array( $this, 'save_visibility_condition' ) );
			add_action( 'save_post', array( $this, 'save_category_selector' ) );
			add_action( 'save_post', array( $this, 'save_tab_priority' ) );
	}

		/**
		 * Categories selector.
		 */
	public function wta_inclusion_categories_selector( $post_id, $times_svg_icon ) {
			$swt_conditions_category = get_post_meta( $post_id, '_swt_conditions_category', true );
			$selected_categories     = $this->get_selected_terms( $swt_conditions_category, 'product_cat' );
		?>
				<div class="swt-categories-selector swt-inclusion-selector">
						<div class="swt-component-search-field">
								<input data-type="category" type="text" data-taxonomy="categories" id="swt-category-search" class="swt-component-search-field-control" placeholder="<?php _e( 'Search for categories', 'simple-woo-tabs' ); ?>">
						</div>
						<div class="swt-spinner swt-loader">
								<svg width="18" height="18" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg" stroke="#c3c4c7"> <g fill="none" fillRule="evenodd"> <g transform="translate(1 1)" strokeWidth="2"> <circle strokeOpacity="1" cx="18" cy="18" r="18"/> <path d="M36 18c0-9.94-8.06-18-18-18"> <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"/> </path> </g> </g></svg>
						</div>
						<div class="swt-component-no-results">
								<span><?php _e( 'No categories found', 'simple-woo-tabs' ); ?></span>
						</div>
						<ul class="solution-box-search-list__list">
						</ul>
						<div class="solution-box-search-list__selected <?php echo ( $selected_categories ) ? '' : 'swt-hide-selected-terms-section'; ?>">
								<div class="solution-box-search-list__selected-header">
										<strong><?php _e( 'Selected categories', 'simple-woo-tabs' ); ?></strong>
									<?php
											printf(
												'<button type="button" aria-label="%1$s" class="solution-box-search-list-clear__all solution-box-remove-inclusions">%1$s</button>',
												__( 'Clear all selected categories', 'simple-woo-tabs' ),
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
																		<input type="hidden" name="swt_category_list[]" value="<?php echo $category->term_id; ?>">
																		
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
		<div class="swt-component-search-field disabled">
						<input disabled type="text" class="swt-component-search-field-control" placeholder="<?php _e( 'Search for products', 'simple-woo-tabs' ); ?>">
			<a class="pro-version-link" target="_blank" href="https://solution-box.com/wordpress-plugins/simple-woo-tabs/?utm_source=settings&utm_medium=settings&utm_campaign=settingsinline&amp;utm_content=swtsettings">
					<?php _e( 'Pro version only', 'simple-woo-tabs' ); ?>
			</a>
		</div>
		
		<div class="swt-component-search-field disabled">
						<input disabled type="text" class="swt-component-search-field-control" placeholder="<?php _e( 'Search for tags', 'simple-woo-tabs' ); ?>">
			<a class="pro-version-link" target="_blank" href="https://solution-box.com/wordpress-plugins/simple-woo-tabs/?utm_source=settings&utm_medium=settings&utm_campaign=settingsinline&amp;utm_content=swtsettings">
				<?php _e( 'Pro version only', 'simple-woo-tabs' ); ?>
			</a>
		</div>
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
		if ( ! isset( $_POST['swt_meta_box_tab_nonce'] ) ) {
				return;
		}
					// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['swt_meta_box_tab_nonce'], 'swt_tab_meta_box' ) ) {
				return;
		}

			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
		}

		if ( Post_Type::POST_SLUG != $_POST['post_type'] ) {
				return;
		}

			$swt_conditions_category = '';
		if ( isset( $_POST['swt_category_list'] ) && ! empty( $_POST['swt_category_list'] ) ) {
				$swt_conditions_category = $_POST['swt_category_list'];
		}
		if ( ! isset( $_POST['swt_category_list'] ) ) {
				delete_post_meta( $post_id, '_swt_conditions_category' );
				update_post_meta( $post_id, '_swt_display_tab_globally', 'yes' );
					return;
		}
			update_post_meta( $post_id, '_swt_conditions_category', $swt_conditions_category );

	}

		/**
		 *  Save product tabs settings.
		 *
		 * @since 1.0.0
		 */
	public function save_visibility_condition( $post_id ) {

			// Check if our nonce is set.
		if ( ! isset( $_POST['swt_meta_box_tab_nonce'] ) ) {
				return;
		}
					// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['swt_meta_box_tab_nonce'], 'swt_tab_meta_box' ) ) {
				return;
		}
					// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
		}

		if ( Post_Type::POST_SLUG != $_POST['post_type'] ) {
				return;
		}

					// Show tabs on all products
			$display_globally = '';
		if ( isset( $_POST['_swt_display_tab_globally'] ) ) {
				$display_globally = $_POST['_swt_display_tab_globally'];
		} else {
			$display_globally = 'no';
		}

			// show each tab on the product screen by default
			update_post_meta( $post_id, '_swt_option_use_default_for_all', 'no' ); // TODO : Check the  we using this value.
			update_post_meta( $post_id, '_swt_display_tab_globally', $display_globally );

	}

	public function save_tab_priority( $post_id ) {
			// Check if our nonce is set.
		if ( ! isset( $_POST['swt_meta_box_tab_nonce'] ) ) {
				return;
		}
					// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['swt_meta_box_tab_nonce'], 'swt_tab_meta_box' ) ) {
				return;
		}
					// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
		}

		if ( Post_Type::POST_SLUG != $_POST['post_type'] ) {
				return;
		}
			// priority
			$priority = $_POST['_swt_option_priority'];
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
					'simple-woo-tabs_conditions_section',
					__( 'Conditions', 'simple-woo-tabs' ),
					array( $this, 'swt_conditions_section' ),
					$screen,
					'normal',
					'high'
				);
				add_meta_box(
					'simple-woo-tabs_icon_section',
					__( 'Select icon', 'simple-woo-tabs' ),
					array( $this, 'swt_icon_section' ),
					$screen,
					'side',
					'high'
				);
				add_meta_box(
					'simple-woo-tabs_priority_section',
					__( 'Settings', 'simple-woo-tabs' ),
					array( $this, 'swt_priority_section' ),
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
	public function swt_conditions_section( $post ) {
			$post_id = $post->ID;

			// Add an nonce field so we can check for it later.
			wp_nonce_field( 'swt_tab_meta_box', 'swt_meta_box_tab_nonce' );
			$is_tab_global = Util::is_tab_global( $post_id );

			$times_svg_icon = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="clear-icon" aria-hidden="true" focusable="false"><path d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21ZM15.5303 8.46967C15.8232 8.76256 15.8232 9.23744 15.5303 9.53033L13.0607 12L15.5303 14.4697C15.8232 14.7626 15.8232 15.2374 15.5303 15.5303C15.2374 15.8232 14.7626 15.8232 14.4697 15.5303L12 13.0607L9.53033 15.5303C9.23744 15.8232 8.76256 15.8232 8.46967 15.5303C8.17678 15.2374 8.17678 14.7626 8.46967 14.4697L10.9393 12L8.46967 9.53033C8.17678 9.23744 8.17678 8.76256 8.46967 8.46967C8.76256 8.17678 9.23744 8.17678 9.53033 8.46967L12 10.9393L14.4697 8.46967C14.7626 8.17678 15.2374 8.17678 15.5303 8.46967Z"></path></svg>';
		?>
						<table class="form-table visibility-form">
								<tbody>
										<tr>
												<th><?php _e( 'Visibility', 'simple-woo-tabs' ); ?></th>
												<td>
														<fieldset>
																<legend class="screen-reader-text"><span><?php _e( 'Visibility', 'simple-woo-tabs' ); ?></span></legend>
																<label>
																		<input type="radio" id="_swt_display_tab_globally" name="_swt_display_tab_globally" class="swt_visibility_condition" checked="checked" value="yes" <?php checked( 'yes', $is_tab_global, true ); ?>>
																			<?php _e( 'Display globally on all products', 'simple-woo-tabs' ); ?>
																</label><br>
																<label>
																		<input type="radio" id="_swt_display_tab_globally" name="_swt_display_tab_globally" class="swt_visibility_condition" value="no" <?php checked( 'no', $is_tab_global, true ); ?>>
																			<?php _e( 'Show on specific categories', 'simple-woo-tabs' ); ?>
																</label><br>
														</fieldset>
												</td>
										</tr>
								</tbody>
						</table>

						<table id="inclusions-list" class="form-table <?php echo ( $is_tab_global === 'no' ) ? '' : 'hide-section'; ?> ">
								<tbody>
										<tr>
												<th><?php _e( 'Inclusions', 'simple-woo-tabs' ); ?></th>
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

	public function swt_icon_section() {
		?>
				<div class="icon-wrap">
				<a href="#" class="tab_icon disabled button button-secondary"><?php esc_html_e( 'Select Icon', 'simple-woo-tabs' ); ?></a>
				<a href="https://solution-box.com/wordpress-plugins/simple-woo-tabs/?utm_source=settings&utm_medium=settings&utm_campaign=settingsinline&amp;utm_content=swtsettings" class="pro-version-link" target="_blank"><?php _e( 'Pro version only' ); ?></a>
				</div>
				<?php
	}

	public function swt_priority_section( $post ) {
			$priority = $post->menu_order;
		echo '<p><label for="_swt_option_priority"><strong>';
			echo __( 'Priority', 'simple-woo-tabs' );
		echo '</strong></label></p>';
			echo '<input type="number" name="_swt_option_priority" id="_swt_option_priority" value="' . $priority . '" min="0" style="max-width:70px;"/>';
	}
}
