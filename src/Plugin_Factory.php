<?php
namespace Solution_Box\Plugin\Simple_Product_Tabs;

/**
 * Plugin Setup
 *
 * @package   Solution_Box/simple-woo-tabs
 */
/**
 * Plugin factory
 */
class Plugin_Factory {

	/**
	 * Main plugin instance.
	 *
	 * @var Plugin
	 */
	private static $plugin = null;

	/**
	 * Return the shared instance of the plugin.
	 *
	 * @param string $file plugin directory path.
	 * @param float  $version  plugin version.
	 * @return Plugin
	 */
	public static function create( $file, $version ) {
		if ( null === self::$plugin ) {
			self::$plugin = new Plugin( $file, $version );
		}
		return self::$plugin;
	}

}