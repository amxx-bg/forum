<?php
/**
 *
 * Copyright (C) 2019 AMXXBG
 * based on code by (C) 2008-2015 FluxBB
 * and Rickard Andersson (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 *
 * $app = new \Slim\Slim();
 * $app->add(new \Slim\Extras\Middleware\amxxbgAuth());
 *
 */

namespace AMXXBG\Middleware;
use AMXXBG\Core\Interfaces\ForumEnv;
use AMXXBG\Core\Interfaces\Router;
use AMXXBG\Core\Interfaces\User;

/**
 * Middleware to check if user is logged and admin
 */
class Admin
{
    public function __invoke($request, $response, $next)
    {
        // Redirect user to home page if not admin
        if (User::get()->g_id != ForumEnv::get('AMXXBG_ADMIN')) {
            return Router::redirect(Router::pathFor('home'), __('No permission'));
        }

        $response = $next($request, $response);
        return $response;
    }
}
