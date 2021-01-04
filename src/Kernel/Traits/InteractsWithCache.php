<?php

namespace EasyQQ\Kernel\Traits;

use EasyQQ\Kernel\Exceptions\InvalidArgumentException;
use EasyQQ\Kernel\ServiceContainer;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Cache\Simple\FilesystemCache;
use function array_intersect;
use function class_exists;
use function class_implements;
use function sprintf;

/**
 * Trait InteractsWithCache
 *
 * @author JimChen <imjimchen@163.com>
 */
trait InteractsWithCache
{
    /**
     * @var SimpleCacheInterface
     */
    protected $cache;

    /**
     * Get cache instance.
     *
     * @return SimpleCacheInterface
     *
     * @throws InvalidArgumentException
     */
    public function getCache()
    {
        if ($this->cache) {
            return $this->cache;
        }

        if (property_exists($this, 'app') && $this->app instanceof ServiceContainer && isset($this->app['cache'])) {
            $this->setCache($this->app['cache']);

            // Fix PHPStan error
            assert($this->cache instanceof SimpleCacheInterface);

            return $this->cache;
        }

        return $this->cache = $this->createDefaultCache();
    }

    /**
     * Set cache instance.
     *
     * @param SimpleCacheInterface|CacheItemPoolInterface $cache
     *
     * @return InteractsWithCache
     *
     * @throws InvalidArgumentException
     */
    public function setCache($cache)
    {
        if (empty(array_intersect(
            [SimpleCacheInterface::class, CacheItemPoolInterface::class],
            class_implements($cache)
        ))) {
            throw new InvalidArgumentException(sprintf(
                'The cache instance must implements %s or %s interface.',
                SimpleCacheInterface::class,
                CacheItemPoolInterface::class
            ));
        }

        if ($cache instanceof CacheItemPoolInterface) {
            if (!$this->isSymfony43OrHigher()) {
                throw new InvalidArgumentException(sprintf(
                    'The cache instance must implements %s',
                    SimpleCacheInterface::class
                ));
            }
            $cache = new Psr16Cache($cache);
        }

        $this->cache = $cache;

        return $this;
    }

    /**
     * @return SimpleCacheInterface
     */
    protected function createDefaultCache()
    {
        if ($this->isSymfony43OrHigher()) {
            return new Psr16Cache(new FilesystemAdapter('easywechat', 1500));
        }

        return new FilesystemCache();
    }

    protected function isSymfony43OrHigher(): bool
    {
        return class_exists('Symfony\Component\Cache\Psr16Cache');
    }
}
