<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Topic;
use App\Handlers\SlugTranslateHandler;

class TranslateSlug implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $topic;

    public function __construct(Topic $topic)
    {
        // 隊列任務構造器中接收了 Eloquent 模型，將會只序列化模型的 ID
        $this->topic = $topic;
    }

    public function handle()
    {
        // 請求百度 API 接口進行翻譯
        $slug = app(SlugTranslateHandler::class)->translate($this->topic->title);

        // 為了避免模型监控器死循環调用，我們使用 DB 類直接對數據庫進行操作
        \DB::table('topics')->where('id', $this->topic->id)->update(['slug' => $slug]);
    }
}