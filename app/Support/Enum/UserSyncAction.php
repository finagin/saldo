<?php

namespace App\Support\Enum;

use App\Models\User;

enum UserSyncAction: string
{
    case MUST_CREATE = 'mustCreate';
    case MUST_UPDATE = 'mustUpdated';
    case MUST_RESTORE = 'mustRestore';
    case UNKNOWN = 'unknown';

    public static function get(User $user): UserSyncAction
    {
        if (! $user->exists() && ! $user->trashed()) {
            return self::MUST_CREATE;
        }

        if (! $user->exists() && $user->trashed()) {
            return self::MUST_RESTORE;
        }

        if ($user->exists() && ! $user->trashed() && $user->isDirty()) {
            return self::MUST_UPDATE;
        }

        return self::UNKNOWN;
    }
}
