<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Controller\Admin;

use AMXXBG\Core\AdminUtils;
use AMXXBG\Core\Error;
use AMXXBG\Core\Interfaces\Cache;
use AMXXBG\Core\Interfaces\Container;
use AMXXBG\Core\Interfaces\ForumEnv;
use AMXXBG\Core\Interfaces\ForumSettings;
use AMXXBG\Core\Interfaces\Hooks;
use AMXXBG\Core\Interfaces\Lang;
use AMXXBG\Core\Interfaces\Request;
use AMXXBG\Core\Interfaces\Router;
use AMXXBG\Core\Interfaces\User;
use AMXXBG\Core\Interfaces\View;
use AMXXBG\Core\Lister;
use AMXXBG\Core\Utils;

class Plugins
{
    public function __construct()
    {
        $this->model = new \AMXXBG\Model\Admin\Plugins();
        Lang::load('admin/plugins');
        if (!User::isAdmin()) {
            throw new Error(__('No permission'), '403');
        }
    }

    /**
     * Download a plugin, unzip it and rename it
     */
    public function download($req, $res, $args)
    {
        Hooks::fire('controller.admin.plugins.download');

        return $this->model->download($args);
    }

    public function index($req, $res, $args)
    {
        Hooks::fire('controller.admin.plugins.index');

        if (Request::isPost()) {
            return $this->model->uploadPlugin($_fILES);
        }

        View::addAsset('js', 'style/imports/common.js', ['type' => 'text/javascript']);

        $availablePlugins = Lister::getPlugins();
        $activePlugins = Cache::isCached('activePlugins') ? Cache::retrieve('activePlugins') : [];

        $officialPlugins = Lister::getOfficialPlugins();

        AdminUtils::generateAdminMenu('plugins');

        View::setPageInfo([
            'admin_console' => true,
            'active_page' => 'admin',
            'availablePlugins'    =>    $availablePlugins,
            'activePlugins'    =>    $activePlugins,
            'officialPlugins'    =>    $officialPlugins,
            'title' => [Utils::escape(ForumSettings::get('o_board_title')), __('Admin'), __('Extension')],
            ]
        )->addTemplate('@forum/admin/plugins')->display();
    }

    public function activate($req, $res, $args)
    {
        Hooks::fire('controller.admin.plugins.activate');

        if (!$args['name'] || !is_dir(ForumEnv::get('AMXXBG_ROOT').'plugins'.DIRECTORY_SEPARATOR.$args['name'])) {
            throw new Error(__('Bad request'), 400);
        }

        $this->model->activate($args['name']);
        // Plugin has been activated, confirm and redirect
        return Router::redirect(Router::pathFor('adminPlugins'), sprintf(__('Plugin activated'), $args['name']));
    }

    public function deactivate($req, $res, $args)
    {
        Hooks::fire('controller.admin.plugins.deactivate');

        if (!$args['name'] || !is_dir(ForumEnv::get('AMXXBG_ROOT').'plugins'.DIRECTORY_SEPARATOR.$args['name'])) {
            throw new Error(__('Bad request'), 400);
        }

        $this->model->deactivate($args['name']);
        // Plugin has been deactivated, confirm and redirect
        return Router::redirect(Router::pathFor('adminPlugins'), ['warning', sprintf(__('Plugin deactivated'), $args['name'])]);
    }

    public function uninstall($req, $res, $args)
    {
        Hooks::fire('controller.admin.plugins.uninstall');

        if (!$args['name'] || !is_dir(ForumEnv::get('AMXXBG_ROOT').'plugins'.DIRECTORY_SEPARATOR.$args['name'])) {
            throw new Error(__('Bad request'), 400);
        }

        $this->model->uninstall($args['name']);
        // Plugin has been uninstalled, confirm and redirect
        return Router::redirect(Router::pathFor('adminPlugins'), ['warning', sprintf(__('Plugin uninstalled'), $args['name'])]);
    }

    /**
     * Load plugin info if it exists
     * @param null $pluginName
     * @throws Error
     */
    public function info($req, $res, $args)
    {
        $formattedPluginName = str_replace(' ', '', ucwords(str_replace('-', ' ', $args['name'])));
        $new = '\AMXXBG\Plugins\Controller\\'.$formattedPluginName;
        if (class_exists($new)) {
            $plugin = new $new;
            if (method_exists($plugin, 'info')) {
                AdminUtils::generateAdminMenu($args['name']);
                return $plugin->info($req, $res, $args);
            } else {
                throw new Error(__('Bad request'), 400);
            }
        } else {
            throw new Error(__('Bad request'), 400);
        }
    }
}
