<?php

/**
 * Copyright (C) 2019 AMXXBG
 * based on code by (C) 2008-2012 FluxBB
 * and Rickard Andersson (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Plugins;

use AMXXBG\Core\Interfaces\Hooks;
use AMXXBG\Core\Plugin as BasePlugin;
use AMXXBG\Core\Url;

class Test extends BasePlugin
{
    public function run()
    {
        Hooks::bind('model.index.get_forum_actions', [$this, 'addMarkRead']);
    }

    public function addMarkRead($forum_actions)
    {
        $forum_actions[] = '<a href="' . Url::get('mark-read/') . '">Test1</a>';
        $forum_actions[] = '<a href="' . Url::get('mark-read/') . '">Test2</a>';
        return $forum_actions;
    }
}
