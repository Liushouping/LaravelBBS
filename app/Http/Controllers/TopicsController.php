<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TopicRequest;
use App\Models\Category;
use Auth;
use App\Handlers\ImageUploadHandler;
use App\Models\User;

class TopicsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

	// public function index()
    // {
    //     $topics = Topic::paginate(30);
    //     return view('topics.index', compact('topics'));
    // }

	public function index(Request $request, Topic $topic, User $user)
    {
        $topics = $topic->withOrder($request->order)
                        ->with('user', 'category')  // 预加载防止 N+1 问题
                        ->paginate(20);
        $active_users = $user->getActiveUsers();
        
        return view('topics.index', compact('topics', 'active_users'));
    }

    public function show(Request $request, Topic $topic)
    {
        // URL 矯正
        if ( ! empty($topic->slug) && $topic->slug != $request->slug) {
            return redirect($topic->link(), 301);
        }

        return view('topics.show', compact('topic'));
    }

	public function create(Topic $topic)
    {
        $categories = Category::all();
        return view('topics.create_and_edit', compact('topic', 'categories'));
    }

	public function store(TopicRequest $request, Topic $topic)
    {
        $topic->fill($request->all());
        $topic->user_id = Auth::id();
        $topic->save();

        return redirect()->to($topic->link())->with('success', '成功創建話題！');
    }

	public function edit(Topic $topic)
    {
        $this->authorize('update', $topic);
        $categories = Category::all();
        return view('topics.create_and_edit', compact('topic', 'categories'));
    }

	public function update(TopicRequest $request, Topic $topic)
	{
		$this->authorize('update', $topic);
		$topic->update($request->all());

		return redirect()->route('topics.show', $topic->id)->with('message', 'Updated successfully.');
	}

	public function destroy(Topic $topic)
    {
        $this->authorize('destroy', $topic);
        $topic->delete();

        return redirect()->route('topics.index')->with('success', '成功刪除！');
    }

	public function uploadImage(Request $request, ImageUploadHandler $uploader)
    {
        // 初始化返回數據，默認是失敗的
        $data = [
            'success'   => false,
            'msg'       => '上傳失敗!',
            'file_path' => ''
        ];
        // 判斷是否有上傳文件，並賦值给 $file
        if ($file = $request->upload_file) {
            // 儲存圖片到本地
            $result = $uploader->save($file, 'topics', \Auth::id(), 1024);
            // 圖片儲存成功的話
            if ($result) {
                $data['file_path'] = $result['path'];
                $data['msg']       = "上傳成功!";
                $data['success']   = true;
            }
        }
        return $data;
    }

    
}