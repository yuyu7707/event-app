<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * イベント（とその券種）を編集・削除できるか。作成者だけが操作できる。
     */
    public function update(User $user, Event $event): bool
    {
        return $event->isOwnedBy($user);
    }
}
