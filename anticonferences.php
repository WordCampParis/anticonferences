<?php
/**
 * Plugin Name: AntiConférences
 * Plugin URI: https://github.com/wordcampparis/anticonferences
 * Description: WordPress plugin to help you prepare Unconferences
 * Version: 1.0.3-beta
 * Requires at least: 4.8
 * Tested up to: 4.9
 * License: GPLv2 or later
 * Author: WordCampParis
 * Author URI: https://github.com/wordcampparis/
 * Text Domain: anticonferences
 * Domain Path: /languages/
 * GitHub Plugin URI: https://github.com/wordcampparis/anticonferences
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'AntiConferences' ) ) :

/**
 * Main Plugin Class
 *
 * @since  1.0.0
 */
final class AntiConferences {
	/**
	 * Array to store all globals.
	 *
	 * @var array
	 */
	private $data;

	/**
	 * Plugin's main instance
	 *
	 * @var object
	 */
	protected static $instance;

	/**
	 * Check whether a global is set.
	 *
	 * @since  1.0.0
	 *
	 * @param  string  $key The name of the global.
	 * @return bool         True if the global is set. False otherwise.
	 */
	public function __isset( $key ) {
		return isset( $this->data[ $key ] );
	}

	/**
	 * Get a specific global.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $key The name of the global.
	 * @return mixed       The value for the requested global.
	 */
	public function __get( $key ) {
		$value = null;

		if ( isset( $this->data[ $key ] ) ) {
			$value =  $this->data[ $key ];
		}

		return $value;
	}

	/**
	 * Set the value of a specific global.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $key   The name of the global.
	 * @param  mixed  $value The value of the global.
	 */
	public function __set( $key, $value ) {
		$this->data[ $key ] = $value;
	}

	/**
	 * Unset a specific ClusterPress global.
	 *
	 * @since  1.0.0
	 *
	 * @param  string  $key The name of the global.
	 */
	public function __unset( $key ) {
		if ( isset( $this->data[ $key ] ) ) {
			unset( $this->data[ $key ] );
		}
	}

	/**
	 * Initialize the plugin
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->globals();
		$this->inc();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return object A single instance of this class.
	 */
	public static function start() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Setups plugin's globals
	 *
	 * @since 1.0.0
	 */
	private function globals() {
		// Version
		$this->version = '1.0.3-beta';

		// Domain
		$this->domain = 'anticonferences';

		// Base name
		$this->file      = __FILE__;
		$this->basename  = plugin_basename( $this->file );

		// Path and URL
		$this->dir     = plugin_dir_path( $this->file );
		$this->url     = plugin_dir_url ( $this->file );
		$this->inc_dir = trailingslashit( $this->dir . 'inc' );
		$this->tpl_dir = trailingslashit( $this->dir . 'templates' );
		$this->seed    = 0;
	}

	/**
	 * Includes plugin's needed files
	 *
	 * @since 1.0.0
	 */
	private function inc() {
		spl_autoload_register( array( $this, 'autoload' ) );

		require $this->inc_dir . 'functions.php';
		require $this->inc_dir . 'tags.php';

		if ( is_admin() ) {
			require $this->inc_dir . 'admin.php';
		}
	}

	/**
	 * Class Autoload function
	 *
	 * @since  1.0.0
	 *
	 * @param  string $class The class name.
	 */
	public function autoload( $class ) {
		$name = str_replace( '_', '-', strtolower( $class ) );

		if ( 0 !== strpos( $name, 'ac' ) ) {
			return;
		}

		$path = $this->inc_dir . "classes/class-{$name}.php";

		// Sanity check.
		if ( ! file_exists( $path ) ) {
			return;
		}

		require $path;
	}
}

endif;

/**
 * Boot the plugin.
 *
 * @since 1.0.0
 */
function anticonferences() {
	return AntiConferences::start();
}
add_action( 'plugins_loaded', 'anticonferences', 5 );
