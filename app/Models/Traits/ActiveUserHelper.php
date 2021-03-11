<?php

namespace App\Models\Traits;

use App\Models\Topic;
use App\Models\Reply;
use Carbon\Carbon;
use Cache;
use DB;
use Arr;

trait ActiveUserHelper
{
    // 用於存放臨時用户數據
    protected $users = [];       

    // 配置信息
    protected $topic_weight = 4; // 話題權重
    protected $reply_weight = 1; // 回覆權重
    protected $pass_days = 7;    // 多少天内發表過内容
    protected $user_number = 6; // 取出来多少用户

    // 缓存相關配置
    protected $cache_key = 'larabbs_active_users';
    protected $cache_expire_in_seconds = 65 * 60;

    public function getActiveUsers()
    {
        // 嘗試從缓存中取出 cache_key 對應的數據。如果能取到，便直接返回數據。
        // 否則運行匿名函數中的代碼来取出活躍用户數據，返回的同時做了缓存。
        return Cache::remember($this->cache_key, $this->cache_expire_in_seconds, function(){
            return $this->calculateActiveUsers();
        });
    }

    public function calculateAndCacheActiveUsers()
    {
        // 取得活躍用户列表
        $active_users = $this->calculateActiveUsers();
        // 並加以缓存
        $this->cacheActiveUsers($active_users);
    }

    private function calculateActiveUsers()
    {
        $this->calculateTopicScore();
        $this->calculateReplyScore();

        // 數組按照得分排序
        $users = Arr::sort($this->users, function ($user) {
            return $user['score'];
        });

        // 我们需要的是倒序，高分靠前，第二个參數为保持數組的 KEY 不變
        $users = array_reverse($users, true);

        // 只獲取我们想要的數量
        $users = array_slice($users, 0, $this->user_number, true);

        // 新建一个空集合
        $active_users = collect();

        foreach ($users as $user_id => $user) {
            // 找寻下是否可以找到用户
            $user = $this->find($user_id);

            // 如果數據庫里有该用户的话
            if ($user) {

                // 将此用户實體放入集合的末尾
                $active_users->push($user);
            }
        }

        // 返回數據
        return $active_users;
    }

    private function calculateTopicScore()
    {
        // 從話題數據表里取出限定時間范围（$pass_days）内，有發表過話題的用户
        // 並且同時取出用户此段時間内發佈話題的數量
        $topic_users = Topic::query()->select(DB::raw('user_id, count(*) as topic_count'))
                                     ->where('created_at', '>=', Carbon::now()->subDays($this->pass_days))
                                     ->groupBy('user_id')
                                     ->get();
        // 根據話題數量計算得分
        foreach ($topic_users as $value) {
            $this->users[$value->user_id]['score'] = $value->topic_count * $this->topic_weight;
        }
    }

    private function calculateReplyScore()
    {
        // 從回覆數據表里取出限定時間范围（$pass_days）内，有發表過回覆的用户
        // 並且同時取出用户此段時間内發佈回覆的數量
        $reply_users = Reply::query()->select(DB::raw('user_id, count(*) as reply_count'))
                                     ->where('created_at', '>=', Carbon::now()->subDays($this->pass_days))
                                     ->groupBy('user_id')
                                     ->get();
        // 根據回覆數量計算得分
        foreach ($reply_users as $value) {
            $reply_score = $value->reply_count * $this->reply_weight;
            if (isset($this->users[$value->user_id])) {
                $this->users[$value->user_id]['score'] += $reply_score;
            } else {
                $this->users[$value->user_id]['score'] = $reply_score;
            }
        }
    }

    private function cacheActiveUsers($active_users)
    {
        // 将數據放入缓存中
        Cache::put($this->cache_key, $active_users, $this->cache_expire_in_seconds);
    }
}