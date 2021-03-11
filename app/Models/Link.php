<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Cache;

class Link extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'link'];

    public $cache_key = 'larabbs_links';

    protected $cache_expire_in_seconds = 1440 * 60;

    public function getAllCached()
    {
        // 嘗試從缓存中取出 cache_key 對應的數據。如果能取到，便直接返回數據。
        // 否則運行匿名函數中的代碼来取出 links 表中所有的數據，返回的同時做了缓存。
        return Cache::remember($this->cache_key, $this->cache_expire_in_seconds, function(){
            return $this->all();
        });
    }
}