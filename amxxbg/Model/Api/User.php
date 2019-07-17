<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Model\Api;

use AMXXBG\Core\Error;
use AMXXBG\Core\Interfaces\Hooks;

class User extends Api
{
    public function display($id)
    {
        $user = new \AMXXBG\Model\Profile();

        // Remove sensitive fields for regular users
        if (!$this->isAdMod) {
            Hooks::bind('model.profile.get_user_info', function ($user) {
                $user = $user->selectDeleteMany(['u.email', 'u.registration_ip', 'u.auto_notify', 'u.admin_note', 'u.last_visit']);
                return $user;
            });
        }

        try {
            $data = $user->getUserInfo($id);
        } catch (Error $e) {
            return $this->errorMessage;
        }

        $data = $data->asArray();

        if (!$this->isAdMod) {
            unset($data['prefs']);
        }


        return $data;
    }
}
