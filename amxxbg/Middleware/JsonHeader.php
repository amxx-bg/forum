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

/**
 * Middleware to change the header to Json
 */
class JsonHeader
{
    public function __invoke($request, $response, $next)
    {
        $response = $response->withHeader('Content-type', 'application/json');

        $response = $next($request, $response);

        return $response;
    }
}
