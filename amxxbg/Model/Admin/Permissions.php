<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Model\Admin;

use AMXXBG\Core\Database as DB;
use AMXXBG\Core\Interfaces\Cache as CacheInterface;
use AMXXBG\Core\Interfaces\Container;
use AMXXBG\Core\Interfaces\ForumSettings;
use AMXXBG\Core\Interfaces\Hooks;
use AMXXBG\Core\Interfaces\Input;
use AMXXBG\Core\Interfaces\Router;
use AMXXBG\Model\Cache;

class Permissions
{
    public function update()
    {
        $form = array_map('intval', Input::post('form'));
        $form = Hooks::fire('model.admin.permissions.update_permissions.form', $form);

        foreach ($form as $key => $input) {
            // Make sure the input is never a negative value
            if ($input < 0) {
                $input = 0;
            }

            // Only update values that have changed
            if (array_key_exists('p_'.$key, Container::get('forum_settings')) && ForumSettings::get('p_'.$key) != $input) {
                DB::table('config')->where('conf_name', 'p_'.$key)
                                                           ->updateMany('conf_value', $input);
            }
        }

        // Regenerate the config cache
        $config = array_merge(Cache::getConfig(), Cache::getPreferences());
        CacheInterface::store('config', $config);

        return Router::redirect(Router::pathFor('adminPermissions'), __('Perms updated redirect'));
    }
}
