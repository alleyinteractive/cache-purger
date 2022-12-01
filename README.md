# Cache Collector

[![Coding Standards](https://github.com/alleyinteractive/cache-collector/actions/workflows/coding-standards.yml/badge.svg)](https://github.com/alleyinteractive/cache-collector/actions/workflows/coding-standards.yml)
[![Testing Suite](https://github.com/alleyinteractive/cache-collector/actions/workflows/unit-test.yml/badge.svg)](https://github.com/alleyinteractive/cache-collector/actions/workflows/unit-test.yml)

Dynamic cache key collector for easy purging.

One common problem with large WordPress sites that utilize Memcache is the
problems that arise when trying to purge cache keys that are dynamically
generated. For example, if a cache key is the hash of a remote request. You
would need to calculate the hashed cache key to properly purge it from the
cache. Another common problem would be trying to purge all the cache keys in a
specific group (Memcache doesn't support group purging).

Cache Collector solves for this by storing cache/transient keys in collections
in WordPress. These collections can then be purged in a single command. Here's a
real-world use case:

When viewing a post, the post's related posts are fetched from a remote source
and displayed to the user. This operation is expensive due to the remote request
and needs to be cached. When the post is updated, the related post cache needs
to be flushed as well.

Enter Cache Collector. When the post is updated, the related post cache key is
added to a collection. When the post is updated, the cache key that is connected
to the post will automatically be purged.

To flip this around, say the remote data source is having issues and you need to
flush all the related post cache keys. You can do this by purging the "related
posts" cache collection. This stores all the cache keys for all related posts.
In one command you can purge an entire cache group without having to calculate
the cache key for each.

## Installation

You can install the package via composer:

```bash
composer require alleyinteractive/cache-collector
```

## Usage

Activate the plugin in WordPress and use the below methods to interface with the
cache collector.

### Register a Key in a Cache Collection

```php
cache_collector_register_key( string $collection, string $key );
```

### Purging a Cache Collection

```php
cache_collector_purge( string $collection );
```

### Registering a Key Related to a Post

A post cache collection is a collection of cache keys related to a post. When a
post is updated, the post's cache collection is purged. This allows you to purge
all of the cache keys related to a post at once. A post will only purge the
cache related to a post if the post was recently updated (within the last week
by default).

```php
cache_collector_register_post_key( \WP_Post|int $post, string $key, string $group = '', string $type = 'cache' );
```

### Purging a Post's Cache Collection

```php
cache_collector_purge_post( int $post_id );
```

### Registering a Key Related to a Term

```php
cache_collector_register_term_key( \WP_Term|int $term, string $key, string $group = '', string $type = 'cache' );
```

### Purging a Term's Cache Collection

```php
cache_collector_purge_term( \WP_Term|int $term );
```

## Testing

Run `composer test` to run tests against PHPUnit and the PHP code in the plugin.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

This project is actively maintained by [Alley
Interactive](https://github.com/alleyinteractive). Like what you see? [Come work
with us](https://alley.co/careers/).

- [Sean Fisher](https://github.com/srtfisher)
- [All Contributors](../../contributors)

## License

The GNU General Public License (GPL) license. Please see [License File](LICENSE) for more information.
