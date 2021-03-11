<?php

namespace App\Observers;

use App\Models\Link;
use Cache;

class LinkObserver
{
    // 在保存時清空 cache_key 對應的缓存
    public function saved(Link $link)
    {
        Cache::forget($link->cache_key);
    }
}