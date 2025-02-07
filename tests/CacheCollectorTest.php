<?php
namespace Cache_Collector\Tests;

use Cache_Collector\Cache_Collector;
use Mantle\Testkit\Test_Case;

/**
 * Visit {@see https://mantle.alley.co/testing/test-framework.html} to learn more.
 */
class CacheCollectorTest extends Test_Case {
	public function test_register_key() {
		$instance = new Cache_Collector( __FUNCTION__ );

		$this->assertEmpty( $instance->keys() );

		$instance->register( 'example-key' );

		$this->assertEmpty( $instance->keys() );

		$instance->save();

		$this->assertNotEmpty( $instance->keys() );
		$this->assertContains( [ 'example-key', '' ], $instance->keys()['cache'] );
		$this->assertCount( 1, $instance->keys()['cache'] ?? [] );
	}

	public function test_register_key_helper() {
		$instance = cache_collector_register_key( __FUNCTION__, 'example-key' );

		$instance->save();

		$this->assertNotEmpty( $instance->keys()['cache'] ?? [] );
		$this->assertContains( [ 'example-key', '' ], $instance->keys()['cache'] );
		$this->assertCount( 1, $instance->keys()['cache'] );
	}

	public function test_register_multiple_keys() {
		$instance = new Cache_Collector( __FUNCTION__ );

		$this->assertEmpty( $instance->keys() );

		$instance
			->register( 'example-key' )
			->register( 'example-key-2' )
			->register( 'example-key-3', 'cache-group' );

		$this->assertEmpty( $instance->keys() );

		$instance->save();

		$this->assertNotEmpty( $instance->keys()['cache'] );
		$this->assertContains( [ 'example-key', '' ], $instance->keys()['cache'] );
		$this->assertContains( [ 'example-key-2', '' ], $instance->keys()['cache'] );
		$this->assertContains( [ 'example-key-3', 'cache-group' ], $instance->keys()['cache'] );
		$this->assertCount( 3, $instance->keys()['cache'] );
		$this->assertCount( 1, $instance->keys() );
	}

	public function test_register_duplicates() {
		$instance = new Cache_Collector( __FUNCTION__ );

		$this->assertEmpty( $instance->keys() );

		$instance
			->register( 'example-key' )
			->register( 'example-key' )
			->register( 'example-key-2' )
			->register( 'example-key-2' )
			->register( 'example-key-3', 'cache-group' )
			->register( 'example-key-3', 'cache-group' )
			->register( 'example_transient', '', 0, 'transient' );

		$this->assertEmpty( $instance->keys() );

		$instance->save();

		$this->assertNotEmpty( $instance->keys() );
		$this->assertContains( [ 'example-key', '' ], $instance->keys()['cache'] );
		$this->assertContains( [ 'example-key-2', '' ], $instance->keys()['cache'] );
		$this->assertContains( [ 'example-key-3', 'cache-group' ], $instance->keys()['cache'] );
		$this->assertCount( 3, $instance->keys()['cache'] );
		$this->assertContains( [ 'example_transient', '' ], $instance->keys()['transient'] );
		$this->assertCount( 2, $instance->keys() );
	}

	public function test_expiration_removal_on_save() {
		$instance = new Cache_Collector( __FUNCTION__ );

		$parent = $instance->get_parent_object();

		update_post_meta(
			$parent->ID,
			Cache_Collector::META_KEY,
			[
				'cache' => [
					'example-key_:_' => time() - 100,
				],
			],
		);

		$this->assertNotEmpty( $instance->keys() );

		$instance->save();

		$this->assertEmpty( $instance->keys() );
	}

	public function test_expiration_removal_on_new_registration() {
		$instance = new Cache_Collector( __FUNCTION__ );

		$parent = $instance->get_parent_object();

		update_post_meta(
			$parent->ID,
			Cache_Collector::META_KEY,
			[
				'cache' => [
					'example-key_:_' => time() - 1000,
				],
			],
		);

		$this->assertNotEmpty( $instance->keys() );

		$instance->register( 'another-key', '', 100 );

		$this->assertNotEmpty( $instance->keys() );

		$instance->save();

		$this->assertNotEmpty( $instance->keys() );
		$this->assertContains( [ 'another-key', '' ], $instance->keys()['cache'] );
		$this->assertCount( 1, $instance->keys()['cache'] );
	}

	public function test_expiration_bumped_when_saved() {
		$instance = new Cache_Collector( __FUNCTION__ );

		$start_time = time() + 100;
		$parent     = $instance->get_parent_object();

		update_post_meta(
			$parent->ID,
			Cache_Collector::META_KEY,
			[
				'cache' => [
					'example-key_:_' => $start_time,
				],
			],
		);

		$this->assertNotEmpty( $instance->keys() );

		$instance->register( 'example-key' );

		$instance->save();

		$this->assertNotEmpty( $instance->keys() );
		$this->assertContains( [ 'example-key', '' ], $instance->keys()['cache'] );
		$this->assertGreaterThan( $start_time, $instance->keys()['cache'][0] );
		$this->assertCount( 1, $instance->keys()['cache'] );
	}

	public function test_item_preserved_when_saved() {
		$instance = new Cache_Collector( __FUNCTION__ );

		$parent = $instance->get_parent_object();

		update_post_meta(
			$parent->ID,
			Cache_Collector::META_KEY,
			[
				'cache' => [
					'example-key_:_' => time() + 1000,
				],
			],
		);

		$this->assertNotEmpty( $instance->keys() );

		$instance->save();

		$this->assertNotEmpty( $instance->keys()['cache'] );
		$this->assertContains( [ 'example-key', '' ], $instance->keys()['cache'] );
	}

	public function test_key_saved_on_destruct() {
		$instance = new Cache_Collector( __FUNCTION__ );

		$this->assertEmpty( $instance->keys() );

		$instance->register( 'example-key' );

		$this->assertEmpty( $instance->keys() );

		$parent = $instance->get_parent_object();

		$this->assertEmpty( $instance->keys() );

		unset( $instance );

		$this->assertNotEmpty( get_post_meta( $parent->ID, Cache_Collector::META_KEY, true ) );
	}

	public function test_purge() {
		wp_cache_set( 'example-key', 'value', 'cache-group' );

		$this->assertNotEmpty( wp_cache_get( 'example-key', 'cache-group' ) );

		$instance = new Cache_Collector( __FUNCTION__ );

		$instance->register( 'example-key', 'cache-group' );

		$instance->save();

		$this->assertNotEmpty( $instance->keys() );

		$instance->purge();

		$this->assertEmpty( wp_cache_get( 'example-key', 'cache-group' ) );
	}

	public function test_for_post() {
		$post_id = static::factory()->post->create();

		wp_cache_set( 'post-example-key', 'value', 'cache-group' );

		$this->assertNotEmpty( wp_cache_get( 'post-example-key', 'cache-group' ) );

		$instance = Cache_Collector::for_post( $post_id );

		$instance->register( 'post-example-key', 'cache-group' );

		$instance->save();

		$this->assertNotEmpty( $instance->keys() );

		$instance->purge();

		$this->assertEmpty( wp_cache_get( 'post-example-key', 'cache-group' ) );
	}

	public function test_for_post_with_invalid_post_id() {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid post ID: -100' );

		Cache_Collector::for_post( -100 );
	}

	public function test_for_post_on_post_update() {
		$post_id = static::factory()->post->create();

		wp_cache_set( 'post-update-key', 'value', 'cache-group' );

		$this->assertNotEmpty( wp_cache_get( 'post-update-key', 'cache-group' ) );

		$instance = Cache_Collector::for_post( $post_id );

		$instance->register( 'post-update-key', 'cache-group' );

		$instance->save();

		$this->assertNotEmpty( $instance->keys() );

		wp_update_post( [ 'ID' => $post_id ] );

		$this->assertEmpty( wp_cache_get( 'post-update-key', 'cache-group' ) );
	}

	public function test_for_term() {
		$term_id = static::factory()->category->create();

		wp_cache_set( 'term-example-key', 'value', 'cache-group' );

		$this->assertNotEmpty( wp_cache_get( 'term-example-key', 'cache-group' ) );

		$instance = Cache_Collector::for_term( $term_id );

		$instance->register( 'term-example-key', 'cache-group' );

		$instance->save();

		$this->assertNotEmpty( $instance->keys() );

		$instance->purge();

		$this->assertEmpty( wp_cache_get( 'term-example-key', 'cache-group' ) );
	}

	public function test_for_term_invalid() {
		$this->expectException( \InvalidArgumentException::class );

		Cache_Collector::for_term( -100 );
	}

	public function test_for_term_on_term_update() {
		$term_id = static::factory()->category->create();

		wp_cache_set( 'term-update-key', 'value', 'cache-group' );

		$this->assertNotEmpty( wp_cache_get( 'term-update-key', 'cache-group' ) );

		$instance = Cache_Collector::for_term( $term_id );

		$instance->register( 'term-update-key', 'cache-group' );

		$instance->save();

		$this->assertNotEmpty( $instance->keys() );

		wp_update_term( $term_id, 'category', [ 'name' => 'Updated Name' ] );

		$this->assertEmpty( wp_cache_get( 'term-update-key', 'cache-group' ) );
	}

	public function test_cron_job_registered() {
		$this->assertInCronQueue( 'cache_collector_cleanup' );
	}

	public function test_cleanup_old_keys() {
		$instance = new Cache_Collector( __FUNCTION__ );

		$parent = $instance->get_parent_object();

		$this->update_post_modified( $parent->ID, gmdate( 'Y-m-d H:i:s', time() - YEAR_IN_SECONDS ) );

		update_post_meta(
			$parent->ID,
			Cache_Collector::META_KEY,
			[
				'cache' => [
					// This key should be removed because it is expired.
					'example-key_:_' => time() - 1000,
				],
			],
		);

		$instance->cleanup();

		$parent = get_post( $parent->ID );

		$this->assertEmpty( $parent );
	}

	public function test_cleanup_preserve_valid_keys() {
		$instance = new Cache_Collector( __FUNCTION__ );

		$parent = $instance->get_parent_object();

		$this->update_post_modified( $parent->ID, gmdate( 'Y-m-d H:i:s', time() - YEAR_IN_SECONDS ) );

		update_post_meta(
			$parent->ID,
			Cache_Collector::META_KEY,
			[
				'cache' => [
					// One expired key to purge and one valid to key to preserve.
					'expired_:_'   => time() - 1000,
					'valid-key_:_' => time() + 1000,
				],
			]
		);

		$this->assertCount( 2, $instance->keys()['cache'] );

		$instance->save();

		$this->assertCount( 1, $instance->keys()['cache'] );
		$this->assertContains( [ 'valid-key', '' ], $instance->keys()['cache'] );
	}
}
