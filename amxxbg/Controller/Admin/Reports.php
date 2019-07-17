<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Controller\Admin;

use AMXXBG\Core\AdminUtils;
use AMXXBG\Core\Interfaces\ForumSettings;
use AMXXBG\Core\Interfaces\Hooks;
use AMXXBG\Core\Interfaces\Input;
use AMXXBG\Core\Interfaces\Lang;
use AMXXBG\Core\Interfaces\Request;
use AMXXBG\Core\Interfaces\Router;
use AMXXBG\Core\Interfaces\View;
use AMXXBG\Core\Utils;

class Reports
{
    public function __construct()
    {
        $this->model = new \AMXXBG\Model\Admin\Reports();
        Lang::load('admin/reports');
    }

    public function display($req, $res, $args)
    {
        Hooks::fire('controller.admin.reports.display');

        // Zap a report
        if (Request::isPost()) {
            $zapId = intval(key(Input::post('zap_id')));
            $this->model->zap($zapId);
            return Router::redirect(Router::pathFor('adminReports'), __('Report zapped redirect'));
        }

        AdminUtils::generateAdminMenu('reports');

        return View::setPageInfo([
                'title' => [Utils::escape(ForumSettings::get('o_board_title')), __('Admin'), __('Reports')],
                'active_page' => 'admin',
                'admin_console' => true,
                'report_data'   =>  $this->model->reports(),
                'report_zapped_data'   =>  $this->model->zappedReports(),
            ]
        )->addTemplate('@forum/admin/reports')->display();
    }
}
