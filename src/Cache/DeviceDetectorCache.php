<?php

namespace WakeWorks\Analytics\Cache;

use DeviceDetector\Cache\CacheInterface;

class DeviceDetectorCache implements CacheInterface
{
    private $cache = null;
    
    public function __construct($cache) {
        $this->cache = $cache;
    }

    public function contains($id): bool
    {
        return $this->cache->has($id) !== false;
    }

    public function fetch($id)
    {
        return $this->cache->get($id);
    }

    public function delete($id): bool
    {
        return $this->cache->delete($id);
    }

    public function flushAll(): bool
    {
        return $this->cache->clear();
    }

    public function save($id, $data, $lifeTime = null): bool
    {
        return $this->cache->set($id, $data, $lifeTime);
    }

} 