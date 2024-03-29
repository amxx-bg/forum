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

use AMXXBG\Core\Error;
use AMXXBG\Core\Interfaces\User;

/**
 * Middleware to check if user is logged and admin
 */
class AdminMod
{
    public function __invoke($request, $response, $next)
    {
        // Middleware to check if user is allowed to moderate, if he's not redirect to error page.
        if (!User::isAdminMod()) {
            throw new Error(__('No permission'), 403);
        }
        $response = $next($request, $response);
        return $response;
    }
}
