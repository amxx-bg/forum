<?php
/**
 *
 * Copyright (C) 2019 AMXXBG
 *  License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 *
 * $app = new \Slim\Slim();
 * $app->add(new \Slim\Extras\Middleware\amxxbgAuth());
 *
 */

namespace AMXXBG\Middleware;
use AMXXBG\Core\Interfaces\Router;
use AMXXBG\Core\Interfaces\User;

/**
 * Middleware to check if user is logged
 */
class Logged
{
    public function __invoke($request, $response, $next)
    {
        // Redirect user to login page if not logged
        if (User::get()->is_guest) {
            return Router::redirect(Router::pathFor('login'));
        }

        $response = $next($request, $response);
        return $response;
    }
}
