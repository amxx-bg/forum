<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Controller\Admin;

use AMXXBG\Core\AdminUtils;
use AMXXBG\Core\Error;
use AMXXBG\Core\Interfaces\ForumSettings;
use AMXXBG\Core\Interfaces\Hooks;
use AMXXBG\Core\Interfaces\Input;
use AMXXBG\Core\Interfaces\Lang;
use AMXXBG\Core\Interfaces\User;
use AMXXBG\Core\Interfaces\View;
use AMXXBG\Core\Utils;

class Censoring
{
    public function __construct()
    {
        $this->model = new \AMXXBG\Model\Admin\Censoring();
        Lang::load('admin/censoring');
        if (!User::isAdmin()) {
            throw new Error(__('No permission'), '403');
        }
    }

    public function display($req, $res, $args)
    {
        Hooks::fire('controller.admin.censoring.display');

        // Add a censor word
        if (Input::post('add_word')) {
            return $this->model->addWord();
        }

        // Update a censor word
        elseif (Input::post('update')) {
            return $this->model->updateWord();
        }

        // Remove a censor word
        elseif (Input::post('remove')) {
            return $this->model->removeWord();
        }

        AdminUtils::generateAdminMenu('censoring');

        return View::setPageInfo([
                'title'    =>    [Utils::escape(ForumSettings::get('o_board_title')), __('Admin'), __('Censoring')],
                'active_page'    =>    'admin',
                'admin_console'    =>    true,
                'word_data'    =>    $this->model->getWords(),
            ]
        )->addTemplate('@forum/admin/censoring')->display();
    }
}
