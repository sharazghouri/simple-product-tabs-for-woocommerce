<?php

namespace Barn2\Plugin\WC_Product_Tabs_Free\Lib;

abstract class DirectoryManager {
	protected $directory;

	public function __construct( $directory ) {
		$this->directory = $directory;
	}

	abstract public function scanAndRegister();

	protected function scanDirectory( $directory ) {
		$files = scandir( $directory );

		foreach ( $files as $file ) {
			if ( $file == '.' || $file == '..' ) {
				continue;
			}

			$path = $directory . DIRECTORY_SEPARATOR . $file;

			if ( is_dir( $path ) ) {
				$this->scanDirectory( $path );
			} elseif ( is_file( $path ) && pathinfo( $path, PATHINFO_EXTENSION ) === 'php' ) {
				require_once $path;

				$className = pathinfo( $path, PATHINFO_FILENAME );

				if ( class_exists( $className ) && method_exists( $className, 'register' ) ) {
					$instance = new $className();
					$instance->register();
				}
			}
		}
	}
}

// Example usage:
class ConcreteDirectoryManager extends DirectoryManager {
	public function scanAndRegister() {
		$this->scanDirectory( $this->directory );
	}
}

$manager = new ConcreteDirectoryManager( __DIR__ );
$manager->scanAndRegister();



