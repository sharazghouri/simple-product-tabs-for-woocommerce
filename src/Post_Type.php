<?php

namespace Solution_Box\Plugin\Simple_Product_Tabs;

/**
 * Registering the post type
 *
 * @package   Solution_Box/simple-woo-tabs
 */
class Post_Type {



	const POST_SLUG = 'woo_product_tabs';
	/**
	 * Register function to hook.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'tab_post_type' ), 99 );
		add_action( 'admin_head-post.php', array( $this, 'hide_publishing_actions' ) );
		add_action( 'admin_head-post-new.php', array( $this, 'hide_publishing_actions' ) );
		add_filter( 'manage_woo_product_tab_posts_columns', array( $this, 'add_columns_in_tab_listing' ) );
		add_action( 'manage_woo_product_tab_posts_custom_column', array( $this, 'custom_columns_in_tab_listing' ), 10, 2 );
		add_filter( 'post_updated_messages', array( $this, 'tab_post_updated_messages' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'tab_post_row_actions' ), 10, 2 );
		add_filter( 'manage_edit-woo_product_tab_sortable_columns', array( $this, 'sortable_tab_columns' ) );
		add_filter( 'parent_file', array( $this, 'highlight_menu_item' ), 99 );
		add_filter( 'custom_menu_order', '__return_true', 99 );
		add_filter( 'menu_order', array( $this, 'tabs_menu_order' ) );
		add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_gutenberg_editor' ), 20, 2 );
		add_action( 'save_post', array( $this, 'woo_product_tab_override_tab_slug' ), 20, 3 );
	}

	/**
	 * Register post type
	 *
	 */
	public function tab_post_type() {

		$labels = array(
			'name'               => _x( 'Product Tabss', 'post type general name', 'simple-woo-tabs' ),
			'singular_name'      => _x( 'Tab', 'post type singular name', 'simple-woo-tabs' ),
			'menu_name'          => _x( 'WooCommerce Product Tabs', 'admin menu', 'simple-woo-tabs' ),
			'name_admin_bar'     => _x( 'Tab', 'add new on admin bar', 'simple-woo-tabs' ),
			'add_new'            => _x( 'Add New', 'simple-woo-tabs' ),
			'add_new_item'       => __( 'Add New Tab', 'simple-woo-tabs' ),
			'new_item'           => __( 'New Tab', 'simple-woo-tabs' ),
			'edit_item'          => __( 'Edit Tab', 'simple-woo-tabs' ),
			'view_item'          => __( 'View Tab', 'simple-woo-tabs' ),
			'all_items'          => __( 'Product Tabs', 'simple-woo-tabs' ),
			'search_items'       => __( 'Search Tabs', 'simple-woo-tabs' ),
			'parent_item_colon'  => __( 'Parent Tabs:', 'simple-woo-tabs' ),
			'not_found'          => __( 'No tabs found.', 'simple-woo-tabs' ),
			'not_found_in_trash' => __( 'No tabs found in Trash.', 'simple-woo-tabs' ),
		);

		$args = array(
			'labels'                => $labels,
			'public'                => false,
			'publicly_queryable'    => false,
			'show_ui'               => true,
			'query_var'             => false,
			'capability_type'       => 'post',
			'has_archive'           => false,
			'hierarchical'          => false,
			'show_in_rest'          => true,
			'rest_base'             => self::POST_SLUG,
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'show_in_menu'          => 'edit.php?post_type=product',
			'taxonomies'            => array(),
			'supports'              => array( 'title', 'editor' ),
		);

		register_post_type( self::POST_SLUG, $args );
	}

	/**
	 * Hide publishing actions.
	 *
	 * @since 1.0.0
	 */
	function hide_publishing_actions() {
		global $post;
		if ( self::POST_SLUG !== $post->post_type ) {
				return;
		}
		?>
		<style type="text/css">
		#misc-publishing-actions,#minor-publishing-actions{
				display:none;
		}
		</style>
		<?php
		return;
	}

	/**
	 * Add column is tabs post list.
	 *
	 * @param array $columns
	 */
	public function add_columns_in_tab_listing( $columns ) {

		unset( $columns['date'] );
		$columns['priority']         = __( 'Priority', 'simple-woo-tabs' );
		$columns['display-globally'] = __( 'Display globally', 'simple-woo-tabs' );
		$columns['tab-key']          = __( 'Tab Key', 'simple-woo-tabs' );

		return $columns;
	}

	/**
	 * Add custom columns data.
	 *
	 * @param string $column column key.
	 * @param int    $post_id  post ID.
	 */
	public function custom_columns_in_tab_listing( $column, $post_id ) {

		$post = get_post( $post_id );
		switch ( $column ) {
			case 'priority':
				echo $post->menu_order;
				break;
			case 'tab-key':
				echo '<code>' . $post->post_name . '</code>';
				break;
			case 'display-globally':
				$flag_default_for_all = Util::is_tab_global( $post_id );
				$tab_categories       = get_post_meta( $post_id, '_wpt_conditions_category', true );
				if ( 'no' === $flag_default_for_all && $tab_categories ) {
					echo '<span class="dashicons dashicons-no-alt"></span>';
				} else {
					echo '<span class="dashicons dashicons-yes"></span>';
				}
				break;
			default:
				break;
		}

	}

	/**
	 * Tabs actions message.
	 *
	 * @param array $messages tabs actions message.
	 * @return array
	 */
	public function tab_post_updated_messages( $messages ) {

		$post = get_post();

		$messages[self::POST_SLUG] = array(

			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Tab updated.', 'simple-woo-tabs' ),
			2  => __( 'Custom field updated.', 'simple-woo-tabs' ),
			3  => __( 'Custom field deleted.', 'simple-woo-tabs' ),
			4  => __( 'Tab updated.', 'simple-woo-tabs' ),
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Tab restored to revision from %s', 'simple-woo-tabs' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Tab published.', 'simple-woo-tabs' ),
			7  => __( 'Tab saved.', 'simple-woo-tabs' ),
			8  => __( 'Tab submitted.', 'simple-woo-tabs' ),
			9  => sprintf(
				__( 'Tab scheduled for: <strong>%1$s</strong>.', 'simple-woo-tabs' ),
				date_i18n( __( 'M j, Y @ G:i', 'simple-woo-tabs' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Tab draft updated.', 'simple-woo-tabs' ),
		);
		return $messages;

	}


	public function tab_post_row_actions( $actions, $post ) {
		if ( self::POST_SLUG == $post->post_type && isset( $actions['inline hide-if-no-js'] ) ) {
			unset( $actions['inline hide-if-no-js'] );
		}
		return $actions;

	}

	/**
	 * Highlight menu in sub pages.
	 *
	 * @param mixed $file file object.
	 * @return mixed
	 */
	public function highlight_menu_item( $file ) {
		global $plugin_page, $submenu_file;

		if ( 'wta_settings' == $plugin_page ) {
			$plugin_page  = 'edit.php?post_type=product';
			$submenu_file = 'edit.php?post_type=' . SELF::POST_SLUG;
		}
		return $file;
	}

	/**
	 * Priority the tab menu
	 *
	 * @param array $menu_order menu array.
	 * @return array
	 */
	public function tabs_menu_order( $menu_order ) {
		global $submenu;

		if ( $submenu['edit.php?post_type=product'] ) {
			$index = 0;
			foreach ( $submenu['edit.php?post_type=product'] as $i => $item ) {
				if ( $item[2] === 'edit.php?post_type='. SELF::POST_SLUG ) {
					$index = $i;
					break;
				}
			}
			if ( $index ) {
				$temp = $submenu['edit.php?post_type=product'][ $index ];
				unset( $submenu['edit.php?post_type=product'][ $index ] );
				$submenu['edit.php?post_type=product'][] = $temp;
			}
		}

		return $menu_order;
	}

	/**
	 * Disable block editor for tabs post type.
	 *
	 * @param boolean $is_enabled post enable.
	 * @param string  $post_type post slug.
	 * @return boolean
	 */
	public function disable_gutenberg_editor( $is_enabled, $post_type ) {
		if ( self::POST_SLUG === $post_type ) {
			return false;
		}
		return $is_enabled;
	}

	/**
	 * Change the tab slug and start it with wpt prefix
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post WP_Post object.
	 * @param bool    $update Whether this is update or not.
	 */
	function woo_product_tab_override_tab_slug( $post_id, $post, $update ) {
		// Only want to set if this is a new post.
		if ( $update ) {
			return;
		}

		// Bail out if this is an autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Bail out if this is not an event item.
		if ( self::POST_SLUG !== $post->post_type ) {
			return;
		}

		// Bail out if no permission.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		remove_action( 'save_post', array( $this, 'woo_product_tab_override_tab_slug' ), 20 );

		$unique_slug = 'swt-' . $post_id;

		$new_data = array(
			'ID'        => $post_id,
			'post_name' => $unique_slug,
		);

		wp_update_post( $new_data );

		add_action( 'save_post', array( $this, 'woo_product_tab_override_tab_slug' ), 20, 3 );
	}

	/**
	 * Sort by menu order
	 *
	 * @param array $columns columns array.
	 * @return array
	 */
	public function sortable_tab_columns( $columns ) {

		$columns['priority'] = 'menu_order';
		return $columns;

	}
}