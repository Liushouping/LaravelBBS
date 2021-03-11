<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Topic;
use App\Models\Category;
use App\Models\User;
use App\Models\Link;

class CategoriesController extends Controller
{
    public function show(Category $category, Request $request, Topic $topic, User $user, Link $link)
    {
        // 讀取分類 ID 關聯的話題，並按每 20 條分頁
        $topics = $topic->withOrder($request->order)
                        ->where('category_id', $category->id)
                        ->with('user',  'category')  // 預加载防止 N+1 问题
                        ->paginate(20);
        // 活躍用户列表
        $active_users = $user->getActiveUsers();
        // 資源連結
        $links = $link->getAllCached();
        // 傳參變量到模板中
        return view('topics.index', compact('topics', 'category', 'active_users', 'links'));
    }
}