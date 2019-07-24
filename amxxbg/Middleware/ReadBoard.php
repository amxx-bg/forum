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
 * Middleware to check if user is allowed to read the board
 */
class ReadBoard
{
    public function __invoke($request, $response, $next)
    {
        // Display error page
        if (!User::can('board.read')) {
            throw new Error(__('No view'), 403);
        }
        $response = $next($request, $response);
        return $response;
    }
}
