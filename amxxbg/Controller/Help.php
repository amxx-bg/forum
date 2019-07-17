<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Controller;

use AMXXBG\Core\Interfaces\ForumSettings;
use AMXXBG\Core\Interfaces\Hooks;
use AMXXBG\Core\Interfaces\Lang;
use AMXXBG\Core\Interfaces\View;
use AMXXBG\Core\Utils;

class Help
{
    public function __construct()
    {
        Lang::load('help');
    }

    public function display($req, $res, $args)
    {
        Hooks::fire('controller.help.start');

        View::setPageInfo([
            'title' => [Utils::escape(ForumSettings::get('o_board_title')), __('Help')],
            'active_page' => 'help',
        ])->addTemplate('@forum/help')->display();
    }
}
