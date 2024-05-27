<?php

namespace Solution_Box\Plugin\Simple_Product_Tabs;

/**
 * Registering the post type
 *
 * @package   Solution_Box/simple-product-tabs
 */
class Post_Type {



	const POST_SLUG = 'sptb_product_tabs';
	/**
	 * Register function to hook.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'tab_post_type' ), 99 );
		add_action( 'admin_head-post.php', array( $this, 'hide_publishing_actions' ) );
		add_action( 'admin_head-post-new.php', array( $this, 'hide_publishing_actions' ) );
		add_filter( 'manage_' . self::POST_SLUG . '_posts_columns', array( $this, 'add_columns_in_tab_listing' ) );
		add_action( 'manage_' . self::POST_SLUG . '_posts_custom_column', array( $this, 'custom_columns_in_tab_listing' ), 10, 2 );
		add_filter( 'post_updated_messages', array( $this, 'tab_post_updated_messages' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'tab_post_row_actions' ), 10, 2 );
		add_filter( 'custom_menu_order', '__return_true', 99 );
		add_filter( 'menu_order', array( $this, 'tabs_menu_order' ) );
		add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_gutenberg_editor' ), 20, 2 );
		add_action( 'save_post', array( $this, 'sptb_product_tabs_override_tab_slug' ), 20, 3 );
	}

	/**
	 * Register post type
	 */
	public function tab_post_type() {

		$labels = array(
			'name'               => _x( 'Product Tabs', 'post type general name', 'simple-product-tabs-for-woocommerce' ),
			'singular_name'      => _x( 'Tab', 'post type singular name', 'simple-product-tabs-for-woocommerce' ),
			'menu_name'          => _x( 'Simple Product Tabs', 'admin menu', 'simple-product-tabs-for-woocommerce' ),
			'name_admin_bar'     => _x( 'Tab', 'add new on admin bar', 'simple-product-tabs-for-woocommerce' ),
			'add_new'            => _x( 'Add New', 'add new item', 'simple-product-tabs-for-woocommerce' ),
			'add_new_item'       => __( 'Add New Tab', 'simple-product-tabs-for-woocommerce' ),
			'new_item'           => __( 'New Tab', 'simple-product-tabs-for-woocommerce' ),
			'edit_item'          => __( 'Edit Tab', 'simple-product-tabs-for-woocommerce' ),
			'view_item'          => __( 'View Tab', 'simple-product-tabs-for-woocommerce' ),
			'all_items'          => __( 'Product Tabs', 'simple-product-tabs-for-woocommerce' ),
			'search_items'       => __( 'Search Tabs', 'simple-product-tabs-for-woocommerce' ),
			'parent_item_colon'  => __( 'Parent Tabs:', 'simple-product-tabs-for-woocommerce' ),
			'not_found'          => __( 'No tabs found.', 'simple-product-tabs-for-woocommerce' ),
			'not_found_in_trash' => __( 'No tabs found in Trash.', 'simple-product-tabs-for-woocommerce' ),
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

		$columns['tab-key']          = __( 'Tab Key', 'simple-product-tabs-for-woocommerce' );

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
			case 'tab-key':
				echo '<code>' . esc_html( $post->post_name ) . '</code>';
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

		$messages[ self::POST_SLUG ] = array(

			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Tab updated.', 'simple-product-tabs-for-woocommerce' ),
			2  => __( 'Custom field updated.', 'simple-product-tabs-for-woocommerce' ),
			3  => __( 'Custom field deleted.', 'simple-product-tabs-for-woocommerce' ),
			4  => __( 'Tab updated.', 'simple-product-tabs-for-woocommerce' ),
			 // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Already type casting in else
			5  => isset( $_GET['revision'] ) ? 
			// translators: %s: the title of the post revision being restored
			sprintf( __( 'Tab restored to revision from %s', 'simple-product-tabs-for-woocommerce' ),
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Already type casting
			wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Tab published.', 'simple-product-tabs-for-woocommerce' ),
			7  => __( 'Tab saved.', 'simple-product-tabs-for-woocommerce' ),
			8  => __( 'Tab submitted.', 'simple-product-tabs-for-woocommerce' ),
			9  => sprintf( 
				    // translators: %%1$s: the data format place holder
				__( 'Tab scheduled for: <strong>%1$s</strong>.', 'simple-product-tabs-for-woocommerce' ),
				date_i18n( __( 'M j, Y @ G:i', 'simple-product-tabs-for-woocommerce' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Tab draft updated.', 'simple-product-tabs-for-woocommerce' ),
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
				// var_dump($submenu ,$item[2] );
				// die('ssss');
				if ( $item[2] === 'edit.php?post_type=' . self::POST_SLUG ) {
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
	function sptb_product_tabs_override_tab_slug( $post_id, $post, $update ) {
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

		remove_action( 'save_post', array( $this, 'sptb_product_tabs_override_tab_slug' ), 20 );

		$unique_slug = 'swt-' . $post_id;

		$new_data = array(
			'ID'        => $post_id,
			'post_name' => $unique_slug,
		);

		wp_update_post( $new_data );

		add_action( 'save_post', array( $this, 'sptb_product_tabs_override_tab_slug' ), 20, 3 );
	}

}
