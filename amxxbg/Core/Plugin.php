<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher.
 */

namespace AMXXBG\Core;

use AMXXBG\Core\Database as DB;
use AMXXBG\Core\Interfaces\Cache;
use AMXXBG\Core\Interfaces\Container;
use AMXXBG\Core\Interfaces\ForumEnv;

class Plugin
{
    public static function getActivePlugins()
    {
        $activePlugins = Cache::isCached('activePlugins') ? Cache::retrieve('activePlugins') : [];

        return $activePlugins;
    }

    public function setActivePlugins()
    {
        $activePlugins = [];
        $results = DB::table('plugins')->select('name')->where('active', 1)->findArray();
        foreach ($results as $plugin) {
            $activePlugins[] = $plugin['name'];
        }
        Cache::store('activePlugins', $activePlugins);

        return $activePlugins;
    }

    /**
     * Run activated plugins
     */
    public function loadPlugins()
    {
        $activePlugins = $this->getActivePlugins();

        foreach ($activePlugins as $plugin) {
            if ($class = $this->load($plugin)) {
                $class->run();
            }
        }
    }

    /**
     * Child classes may use this method to quickly get plugin name added in admin menu
     *
     * @param  array $items Array of plugin names to display in admin panel sidebar
     * @return array        Pushed initial array above
     */
    public function getName($items)
    {
        // Split name
        $classNamespace = explode('\\', get_class($this));
        $className = end($classNamespace);

        // Prettify and return name of child class
        preg_match_all('%[A-Z]*[a-z]+%m', $className, $result, PREG_PATTERN_ORDER);

        $items[] = Utils::escape(implode(' ', $result[0]));

        return $items;
    }

    /**
     * Try to load a plugin class based on URI/folder-name
     * @param  string $plugin the name of the plugin to load
     * @return mixed         Return an instance of the plugin class or (bool) false if it could not be loaded
     */
    public function load($plugin)
    {
        // Plugins loaded via composer ($this->getNamespace should return valid namespace)
        if (class_exists($className = '\AMXXBG\Plugins\\'.$this->getNamespace($plugin))) {
            $class = new $className();
            return $class;
        }
        // "Complex" plugins which need to register namespace via bootstrap.php
        if (file_exists($file = ForumEnv::get('AMXXBG_ROOT').'plugins/'.$plugin.'/bootstrap.php')) {
            $className = require $file;
            $class = new $className();
            return $class;
        }
        // Simple plugins, only a amxxbg.json and the main class
        if (file_exists($file = $this->checkSimple($plugin))) {
            require_once $file;
            $className = '\AMXXBG\Plugins\\'.$this->getNamespace($plugin);
            $class = new $className();
            return $class;
        }
        // Invalid plugin
        return false;
    }

    // Clean a Simple Plugin's parent folder name to load it
    protected function getNamespace($path)
    {
        return str_replace(" ", "", str_replace("/", "\\", ucwords(str_replace('-', ' ', str_replace('/ ', '/', ucwords(str_replace('/', '/ ', $path)))))));
    }

    // For plugins that don't need to provide a Composer autoloader, check if it can be loaded
    protected function checkSimple($plugin)
    {
        return ForumEnv::get('AMXXBG_ROOT').'plugins' . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR . $this->getNamespace($plugin) . '.php';
    }
}
