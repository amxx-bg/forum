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
use AMXXBG\Core\Interfaces\View;
use AMXXBG\Core\Utils;

class Statistics
{
    public function __construct()
    {
        $this->model = new \AMXXBG\Model\Admin\Statistics();
        Lang::load('admin/index');
    }

    public function display($req, $res, $args)
    {
        Hooks::fire('controller.admin.statistics.display');

        AdminUtils::generateAdminMenu('index');

        $total = $this->model->totalSize();

        return View::setPageInfo([
                'title' => [Utils::escape(ForumSettings::get('o_board_title')), __('Admin'), __('Server statistics')],
                'active_page' => 'admin',
                'admin_console' => true,
                'server_load'    =>    $this->model->serverLoad(),
                'num_online'    =>    $this->model->numOnline(),
                'total_size'    =>    $total['size'],
                'total_records'    =>    $total['records'],
                'php_accelerator'    =>    $this->model->phpAccelerator(),
                'php_os'        =>      PHP_OS,
                'php_version'        =>      phpversion(),
            ]
        )->addTemplate('@forum/admin/statistics')->display();
    }


    public function phpinfo($req, $res, $args)
    {
        Hooks::fire('controller.admin.statistics.phpinfo');

        // Show phpinfo() output
        // Is phpinfo() a disabled function?
        if (strpos(strtolower((string) ini_get('disable_functions')), 'phpinfo') !== false) {
            throw new Error(__('PHPinfo disabled message'), 404);
        }

        phpinfo();
    }
}
