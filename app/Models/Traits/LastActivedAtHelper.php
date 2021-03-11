<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

trait LastActivedAtHelper
{
    // 缓存相關
    protected $hash_prefix = 'larabbs_last_actived_at_';
    protected $field_prefix = 'user_';

    public function recordLastActivedAt()
    {
        // 獲取今日 Redis 哈希表名稱，如：larabbs_last_actived_at_2017-10-21
        $hash = $this->getHashFromDateString(Carbon::now()->toDateString());

        // 字段名稱，如：user_1
        $field = $this->getHashField();

        // 當前時間，如：2017-10-21 08:35:15
        $now = Carbon::now()->toDateTimeString();

        // 數據寫入 Redis ，字段已存在会被更新
        Redis::hSet($hash, $field, $now);
    }

    public function syncUserActivedAt()
    {
        // 獲取昨日的哈希表名稱，如：larabbs_last_actived_at_2017-10-21
        $hash = $this->getHashFromDateString(Carbon::yesterday()->toDateString());

        // 從 Redis 中獲取所有哈希表里的數據
        $dates = Redis::hGetAll($hash);

        // 並同步到數據庫中
        foreach ($dates as $user_id => $actived_at) {
            // 会将 `user_1` 转换为 1
            $user_id = str_replace($this->field_prefix, '', $user_id);

            // 只有當用户存在時才更新到數據庫中
            if ($user = $this->find($user_id)) {
                $user->last_actived_at = $actived_at;
                $user->save();
            }
        }

        // 以數據庫为中心的存儲，既已同步，即可删除
        Redis::del($hash);
    }

    public function getLastActivedAtAttribute($value)
    {
        // 獲取今日對應的哈希表名稱
        $hash = $this->getHashFromDateString(Carbon::now()->toDateString());

        // 字段名稱，如：user_1
        $field = $this->getHashField();

        // 三元運算符，優先选择 Redis 的數據，否則使用數據庫中
        $datetime = Redis::hGet($hash, $field) ? : $value;

        // 如果存在的话，返回時間對應的 Carbon 實體
        if ($datetime) {
            return new Carbon($datetime);
        } else {
        // 否則使用用户注册時間
            return $this->created_at;
        }
    }

    public function getHashFromDateString($date)
    {
        // Redis 哈希表的命名，如：larabbs_last_actived_at_2017-10-21
        return $this->hash_prefix . $date;
    }

    public function getHashField()
    {
        // 字段名稱，如：user_1
        return $this->field_prefix . $this->id;
    }
}