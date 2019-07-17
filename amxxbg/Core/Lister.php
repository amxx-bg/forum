<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Core;

use AMXXBG\Core\Interfaces\ForumEnv;

class Lister
{
    /**
     * Get all valid plugin files.
     */
    public static function getPlugins()
    {
        $plugins = [];

        foreach (glob(ForumEnv::get('AMXXBG_ROOT').'plugins/*/amxxbg.json') as $pluginFile) {
            $plugins[] =  json_decode(file_get_contents($pluginFile));
        }

        return $plugins;
    }

    /**
     * Load data of a single plugin
     */
    public static function loadPlugin($pluginName = '')
    {
        return json_decode(file_get_contents(ForumEnv::get('AMXXBG_ROOT').'plugins/'.$pluginName.'/amxxbg.json'));
    }

    /**
     * Get all official plugins using GitHub API
     */
    public static function getOfficialPlugins()
    {
        $plugins = [];

        // Get the official list from the website
        $content = json_decode(AdminUtils::getContent('https://amxxbg.org/plugins.json'));

        // If internet is available
        if (!is_null($content)) {
            foreach ($content as $plugin) {
                // Get information from each repo
                // TODO: cache
                $plugins[] = json_decode(AdminUtils::getContent('https://raw.githubusercontent.com/amxxbg/'.$plugin.'/master/amxxbg.json'));
            }
        }

        return $plugins;
    }

    /**
     * Get available styles
     */
    public static function getStyles()
    {
        $styles = [];

        $iterator = new \DirectoryIterator(ForumEnv::get('AMXXBG_ROOT').'style/themes/');
        foreach ($iterator as $child) {
            if (!$child->isDot() && $child->isDir() && file_exists($child->getPathname().DIRECTORY_SEPARATOR.'style.css')) {
                // If the theme is well formed, add it to the list
                $styles[] = $child->getFileName();
            }
        }

        natcasesort($styles);
        return $styles;
    }

    /**
     * Get available langs
     */
    public static function getLangs($folder = '')
    {
        $langs = [];

        $iterator = new \DirectoryIterator(ForumEnv::get('AMXXBG_ROOT').'amxxbg/lang/');
        foreach ($iterator as $child) {
            if (!$child->isDot() && $child->isDir() && file_exists($child->getPathname().DIRECTORY_SEPARATOR.'common.po')) {
                // If the lang pack is well formed, add it to the list
                $langs[] = $child->getFileName();
            }
        }

        natcasesort($langs);
        return $langs;
    }
}
