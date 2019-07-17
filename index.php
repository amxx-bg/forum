<?php

namespace AMXXBG;

use AMXXBG\Core\Interfaces\AMXXBG;
use AMXXBG\Core\Interfaces\SlimStatic;
use AMXXBG\Middleware\Auth;
use AMXXBG\Middleware\Core;
use AMXXBG\Middleware\Csrf;
use AMXXBG\Middleware\RedirectNonTrailingSlash;
use Slim\App;

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Start a session for flash messages
session_cache_limiter(false);
session_start();

// Load Conposer dependencies
require 'vendor/autoload.php';

// Instantiate Slim
SlimStatic::boot(new App);

// Allow static proxies to be called from anywhere in App
Statical::addNamespace('*', __NAMESPACE__.'\\*');

AMXXBG::add(new RedirectNonTrailingSlash);
AMXXBG::add(new Csrf);
AMXXBG::add(new Auth);
AMXXBG::add(new Core);

// Load the routes
require 'amxxbg/routes.php';

// Run it, baby!
AMXXBG::run();
