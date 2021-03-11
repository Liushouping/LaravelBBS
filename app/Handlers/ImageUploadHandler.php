<?php

namespace App\Handlers;

use Image;
use Str;

class ImageUploadHandler
{
    protected $allowed_ext = ["png", "jpg", "gif", 'jpeg'];

    public function save($file, $folder, $file_prefix, $max_width = false)
    {
        // 構建存储的文件夹规则，值如：uploads/images/avatars/201709/21/
        // 文件夹切割能让查找效率更高。
        $folder_name = "uploads/images/$folder/" . date("Ym/d", time());

        // 文件具体存储的物理路径，`public_path()` 获取的是 `public` 文件夹的物理路径。
        // 值如：/home/vagrant/Code/larabbs/public/uploads/images/avatars/201709/21/
        $upload_path = public_path() . '/' . $folder_name;

        // 获取文件的后缀名，因圖片从剪贴板裡黏贴时后缀名為空，所以此处确保后缀一直存在
        $extension = strtolower($file->getClientOriginalExtension()) ?: 'png';

        // 拼接文件名，加前缀是為了增加辨析度，前缀可以是相关數據模型的 ID
        // 值如：1_1493521050_7BVc9v9ujP.png
        $filename = $file_prefix . '_' . time() . '_' . Str::random(10) . '.' . $extension;

        // 如果上傳的不是圖片將终止操作
        if ( ! in_array($extension, $this->allowed_ext)) {
            return false;
        }

        // 將圖片移动到我們的目标存储路径中
        $file->move($upload_path, $filename);

        // 如果限制了圖片寬度，就進行裁剪
        if ($max_width && $extension != 'gif') {

            // 此類中封装的函数，用于裁剪圖片
            $this->reduceSize($upload_path . '/' . $filename, $max_width);
        }

        return [
            'path' => config('app.url') . "/$folder_name/$filename"
        ];
    }

    public function reduceSize($file_path, $max_width)
    {
        // 先实例化，傳參是文件的磁盘物理路径
        $image = Image::make($file_path);

        // 進行大小调整的操作
        $image->resize($max_width, null, function ($constraint) {

            // 设定寬度是 $max_width，高度等比例缩放
            $constraint->aspectRatio();

            // 防止裁图时圖片尺寸变大
            $constraint->upsize();
        });

        // 對圖片修改后進行儲存
        $image->save();
    }
}