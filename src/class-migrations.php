<?php
/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */
namespace KokoAnalytics;

use Exception;

class Migrations {

	/**
	 * @var string
	 */
	protected $version_from = 0;

	/**
	 * @var string
	 */
	protected $version_to = 0;

	/**
	 * @var string
	 */
	protected $migrations_dir = '';

	/**
	 * @param string $from
	 * @param string $to
	 * @param string $migrations_dir
	 */
	public function __construct( $from, $to, $migrations_dir ) {
		$this->version_from   = $from;
		$this->version_to     = $to;
		$this->migrations_dir = $migrations_dir;
	}

	/**
	 * Run the various upgrade routines, all the way up to the latest version
	 * @throws Exception
	 * @return bool
	 */
	public function run() {
		$migrations = $this->find_migrations();

		foreach ( $migrations as $migration_file ) {
			$this->run_migration( $migration_file );
		}

		return count( $migrations ) > 0;
	}

	/**
	 * @return array
	 */
	public function find_migrations() {
		$files = glob( rtrim( $this->migrations_dir, '/' ) . '/*.php' );

		// return empty array when glob returns non-array value.
		if ( ! is_array( $files ) ) {
			return array();
		}

		$migrations = array();
		foreach ( $files as $file ) {
			$migration = basename( $file );
			$parts     = explode( '-', $migration );
			$version   = $parts[0];

			// check if migration file is not for an even higher version
			if ( version_compare( $version, $this->version_to, '>' ) ) {
				continue;
			}

			// check if we ran migration file before.
			if ( version_compare( $this->version_from, $version, '>=' ) ) {
				continue;
			}

			// schedule migration file for running
			$migrations[] = $file;
		}

		return $migrations;
	}

	/**
	 * Include a migration file and runs it.
	 *
	 * @param string $file
	 * @throws Exception
	 */
	protected function run_migration( $file ) {
		if ( ! file_exists( $file ) ) {
			throw new Exception( "Migration file $file does not exist." );
		}

		include $file;
	}
}
