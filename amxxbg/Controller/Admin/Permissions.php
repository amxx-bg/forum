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

class Permissions
{
    public function __construct()
    {
        $this->model = new \AMXXBG\Model\Admin\Permissions();
        Lang::load('admin/permissions');
        if (!User::isAdmin()) {
            throw new Error(__('No permission'), '403');
        }
    }

    public function display($req, $res, $args)
    {
        Hooks::fire('controller.admin.permissions.display');

        // Update permissions
        if (Request::isPost()) {
            return $this->model->update();
        }

        AdminUtils::generateAdminMenu('permissions');

        return View::setPageInfo([
                'title' => [Utils::escape(ForumSettings::get('o_board_title')), __('Admin'), __('Permissions')],
                'active_page' => 'admin',
                'admin_console' => true,
            ]
        )->addTemplate('@forum/admin/permissions')->display();
    }
}
