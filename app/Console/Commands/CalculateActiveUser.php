<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CalculateActiveUser extends Command
{
    // 供我们調用命令
    protected $signature = 'larabbs:calculate-active-user';

    // 命令的描述
    protected $description = '生成活躍用戶';

    // 最终執行的方法
    public function handle(User $user)
    {
        // 在命令行打印一行信息
        $this->info("開始計算...");

        $user->calculateAndCacheActiveUsers();

        $this->info("成功生成！");
    }
}