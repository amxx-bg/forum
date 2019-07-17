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
use AMXXBG\Core\Interfaces\Router;
use AMXXBG\Core\Interfaces\View;
use AMXXBG\Core\Utils;

class Index
{
    public function __construct()
    {
        Lang::load('admin/index');
    }

    public function display($req, $res, $args)
    {
        if (!isset($args['action'])) {
            $args['action'] = null;
        }

        Hooks::fire('controller.admin.index.display');

        // Check for upgrade
        if ($args['action'] == 'check_upgrade') {
            if (!ini_get('allow_url_fopen')) {
                throw new Error(__('fopen disabled message'), 500);
            }

            $latestVersion = trim(@file_get_contents('https://amxx-bg.info/latest_version.html'));
            if (empty($latestVersion)) {
                throw new Error(__('Upgrade check failed message'), 500);
            }

            if (version_compare(ForumSettings::get('o_cur_version'), $latestVersion, '>=')) {
                return Router::redirect(Router::pathFor('adminIndex'), __('Running latest version message'));
            } else {
                return Router::redirect(Router::pathFor('adminIndex'), sprintf(__('New version available message'), '<a href="https://amxxbg.org/">AMXXBG.org</a>'));
            }
        }

        AdminUtils::generateAdminMenu('index');

        return View::setPageInfo([
                'title' => [Utils::escape(ForumSettings::get('o_board_title')), __('Admin'), __('Index')],
                'active_page' => 'admin',
                'admin_console' => true
            ]
        )->addTemplate('@forum/admin/index')->display();
    }
}
