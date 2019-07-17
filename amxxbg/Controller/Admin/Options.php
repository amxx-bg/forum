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
use AMXXBG\Core\Interfaces\Lang;
use AMXXBG\Core\Interfaces\Request;
use AMXXBG\Core\Interfaces\User;
use AMXXBG\Core\Interfaces\View;
use AMXXBG\Core\Utils;

class Options
{
    public function __construct()
    {
        $this->model = new \AMXXBG\Model\Admin\Options();
        Lang::load('admin/options');
        if (!User::isAdmin()) {
            throw new Error(__('No permission'), '403');
        }
    }

    public function display($req, $res, $args)
    {
        Hooks::fire('controller.admin.options.display');

        if (Request::isPost()) {
            return $this->model->update();
        }

        AdminUtils::generateAdminMenu('admin options');

        $diff = (User::getPref('timezone') + User::getPref('dst')) * 3600;
        $timestamp = time() + $diff;

        View::setPageInfo([
                'title' => [Utils::escape(ForumSettings::get('o_board_title')), __('Admin'), __('Admin options')],
                'active_page' => 'admin',
                'admin_console' => true,
                'languages' => $this->model->languages(),
                'styles' => $this->model->styles(),
                'times' => $this->model->times(),
                'timestamp' => $timestamp
            ]
        )->addTemplate('@forum/admin/options')->display();
    }
}
