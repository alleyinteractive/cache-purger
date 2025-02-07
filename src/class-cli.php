<?php
/**
 * CLI class file
 *
 * @package cache-collector
 */

namespace Cache_Collector;

use Throwable;

/**
 * CLI Command for the plugin.
 */
class CLI {
	/**
	 * Purge a cache for a specific collection.
	 *
	 * ## OPTIONS
	 *
	 * <collection>
	 * : The name of the collection to purge.
	 *
	 * @param array<string> $args Positional arguments.
	 */
	public function purge( $args ): void {
		[ $collection ] = $args;

		$instance = new Cache_Collector( $collection, function_exists( 'ai_logger' ) ? ai_logger() : null );

		$instance->purge();
	}

	/**
	 * Purge a cache for a specific post.
	 *
	 * ## OPTIONS
	 *
	 * <post>
	 * : The ID of the post to purge.
	 *
	 * @param array<string> $args Positional arguments.
	 */
	public function purge_post( $args ): void {
		[ $post ] = $args;

		try {
			Cache_Collector::for_post( (int) $post )->purge();
		} catch ( Throwable $e ) {
			\WP_CLI::error( 'Error purging: ' . $e->getMessage() );
		}
	}

	/**
	 * Purge a cache for a specific term.
	 *
	 * ## OPTIONS
	 *
	 * <term>
	 * : The ID of the term to purge.
	 *
	 * @param array<string> $args Positional arguments.
	 */
	public function purge_term( $args ): void {
		[ $term ] = $args;

		try {
			Cache_Collector::for_term( (int) $term )->purge();
		} catch ( Throwable $e ) {
			\WP_CLI::error( 'Error purging: ' . $e->getMessage() );
		}
	}
}
