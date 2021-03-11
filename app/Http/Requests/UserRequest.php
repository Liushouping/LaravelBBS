<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|between:3,25|regex:/^[A-Za-z0-9\-\_]+$/|unique:users,name,' . Auth::id(),
            'email' => 'required|email',
            'introduction' => 'max:80',
            'avatar' => 'mimes:png,jpg,gif,jpeg|dimensions:min_width=208,min_height=208',
        ];
    }

    public function messages()
    {
        return [
            'avatar.mimes' =>'頭像必須是 png, jpg, gif, jpeg 格式的圖片',
            'avatar.dimensions' => '圖片的清晰度不夠，寬和高需要 208px 以上',
            'name.unique' => '姓名已被占用，請重新填寫',
            'name.regex' => '姓名只支持英文、數字、横杠和下划线。',
            'name.between' => '姓名必須介於 3 - 25 個字符之间。',
            'name.required' => '姓名不能為空。',
        ];
    }
}