<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function saving(User $user)
    {
        // 這樣寫擴展性更高，只有空的時候才指定默認頭貼
        if (empty($user->avatar)) {
            $user->avatar = 'https://cdn.learnku.com/uploads/images/201710/30/1/TrJS40Ey5k.png';
        }
    }
}