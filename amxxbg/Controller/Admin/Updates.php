<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Controller\Admin;

use AMXXBG\Core\AdminUtils;
use AMXXBG\Core\CoreAutoUpdater;
use AMXXBG\Core\Database;
use AMXXBG\Core\Error;
use AMXXBG\Core\Interfaces\Cache;
use AMXXBG\Core\Interfaces\ForumEnv;
use AMXXBG\Core\Interfaces\ForumSettings;
use AMXXBG\Core\Interfaces\Hooks;
use AMXXBG\Core\Interfaces\Input;
use AMXXBG\Core\Interfaces\Lang;
use AMXXBG\Core\Interfaces\User;
use AMXXBG\Core\Interfaces\View;
use AMXXBG\Core\Lister;
use AMXXBG\Core\PluginAutoUpdater;
use AMXXBG\Core\Utils;

class Updates
{
    public function __construct()
    {
        Lang::load('admin/index');
        Lang::load('admin/updates');
        Lang::load('admin/plugins');
        if (!User::isAdmin()) {
            throw new Error(__('No permission'), '403');
        }
    }

    public function display($req, $res, $args)
    {
        Hooks::fire('controller.admin.updates.display');

        $coreUpdates = false;
        $coreUpdatesMessage = __('AMXXBG core up to date');

        $pluginUpdates = [];
        $allPlugins = Lister::getPlugins();

        // Check AMXXBG core updates
        $coreUpdater = new CoreAutoUpdater();
        if ($coreUpdater->checkUpdate() === false) {
            $coreUpdatesMessage = join('<br>', $coreUpdater->getErrors());
        } else {
            // If update available
            if ($coreUpdater->newVersionAvailable()) {
                $coreUpdates = true;
                $coreUpdatesMessage = sprintf(__('AMXXBG core updates available'), ForumSettings::get('o_cur_version'), $coreUpdater->getLatestVersion());
                $coreUpdatesMessage .= '<a href="https://github.com/amxx-bg/amxxbg/releases/tag/'.$coreUpdater->getLatestVersion().'" target="_blank">'.__('View changelog').'</a>';
            }
        }

        // Check plugins uavailable versions
        foreach ($allPlugins as $plugin) {
            // If plugin isn't well formed or doesn't want to be auto-updated, skip it
            if (!isset($plugin->name) || !isset($plugin->version) || (isset($plugin->skipUpdate) && $plugin->skipUpdate == true)) {
                continue;
            }
            $pluginsUpdater = new PluginAutoUpdater($plugin);
            // If check fails, add errors to display in view
            if ($pluginsUpdater->checkUpdate() === false) {
                $plugin->errors = join('<br>', $pluginsUpdater->getErrors());
                $pluginUpdates[] = $plugin;
            }
            // If update available, add plugin to display in view
            if ($pluginsUpdater->newVersionAvailable()) {
                $plugin->lastVersion = $pluginsUpdater->getLatestVersion();
                $pluginUpdates[] = $plugin;
            }
        }

        AdminUtils::generateAdminMenu('updates');

        return View::setPageInfo([
                'title' => [Utils::escape(ForumSettings::get('o_board_title')), __('Admin'), __('Updates')],
                'active_page' => 'admin',
                'admin_console' => true,
                'plugin_updates' => $pluginUpdates,
                'core_updates' => $coreUpdates,
                'core_updates_message' => $coreUpdatesMessage
            ]
        )->addTemplate('@forum/admin/updates')->display();
    }

    public function upgradePlugins($req, $res, $args)
    {
        Hooks::fire('controller.admin.updates.upgradePlugins');

        // Check submit button has been clicked
        if (!Input::post('upgrade-plugins')) {
            throw new Error(__('Wrong form values'), 500);
        }

        $upgradeResults = [];

        foreach (Input::post('plugin_updates') as $plugin => $version) {
            if ($plugin = Lister::loadPlugin($plugin)) {
                // If plugin isn't well formed or doesn't want to be auto-updated, skip it
                if (!isset($plugin->name) || !isset($plugin->version) || (isset($plugin->skipUpdate) && $plugin->skipUpdate == true)) {
                    continue;
                }
                $upgradeResults[$plugin->title] = [];
                $pluginsUpdater = new PluginAutoUpdater($plugin);
                $result = $pluginsUpdater->update();
                if ($result !== true) {
                    $upgradeResults[$plugin->title]['message'] = sprintf(__('Failed upgrade message'), $plugin->version, $pluginsUpdater->getLatestVersion());
                    $upgradeResults[$plugin->title]['errors'] = $pluginsUpdater->getErrors();
                } else {
                    $upgradeResults[$plugin->title]['message'] = sprintf(__('Successful upgrade message'), $plugin->version, $pluginsUpdater->getLatestVersion());
                }
                // Will not be empty if upgrade has warnings (zip archive or _upgrade.php file could not be deleted)
                $upgradeResults[$plugin->title]['warnings'] = $pluginsUpdater->getWarnings();
            } else {
                continue;
            }
        }

        // Reset cache
        Cache::flush();

        // Display upgrade results
        AdminUtils::generateAdminMenu('updates');

        return View::setPageInfo([
                'title'           => [Utils::escape(ForumSettings::get('o_board_title')), __('Admin'), __('Updates')],
                'active_page'     => 'admin',
                'admin_console'   => true,
                'upgrade_results' => $upgradeResults
            ]
        )->addTemplate('@forum/admin/updates')->display();
    }

    public function upgradeCore($req, $res, $args)
    {
        Hooks::fire('controller.admin.updates.upgradeCore');

        // Check submit button has been clicked
        if (!Input::post('upgrade-core')) {
            throw new Error(__('Wrong form values'), 500);
        }

        $key = __('AMXXBG core');
        $upgradeResults = [$key => []];
        $coreUpdater = new CoreAutoUpdater();
        $result = $coreUpdater->update();
        if ($result !== true) {
            $upgradeResults[$key]['message'] = sprintf(__('Failed upgrade message'), ForumEnv::get('FORUM_VERSION'), $coreUpdater->getLatestVersion());
            $upgradeResults[$key]['errors'] = $coreUpdater->getErrors();
        } else {
            $upgradeResults[$key]['message'] = sprintf(__('Successful upgrade message'), ForumEnv::get('FORUM_VERSION'), $coreUpdater->getLatestVersion());
            // Reset cache and update core version in database
            Cache::flush();
            if (!Database::table('config')->rawExecute('UPDATE `'.ForumEnv::get('DB_PREFIX').'config` SET `conf_value` = :value WHERE `conf_name` = "o_cur_version"', ['value' => ForumEnv::get('FORUM_VERSION')])) {
                $coreUpdater->addWarning(__('Could not update core version in database'));
            }
        }
        // Will not be empty if upgrade has warnings (zip archive or _upgrade.php file could not be deleted)
        $upgradeResults[$key]['warnings'] = $coreUpdater->getWarnings();

        // Display upgrade results
        AdminUtils::generateAdminMenu('updates');

        return View::setPageInfo([
                'title'           => [Utils::escape(ForumSettings::get('o_board_title')), __('Admin'), __('Updates')],
                'active_page'     => 'admin',
                'admin_console'   => true,
                'upgrade_results' => $upgradeResults
            ]
        )->addTemplate('@forum/admin/updates')->display();
    }
}
