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
use AMXXBG\Model\Forum;

/**
 * Middleware to check if the moderator has its special powers for this post
 * Take the topic ID, get the forum ID and check with it
 */
class ModeratePermission
{
    public function __invoke($request, $response, $next)
    {
        $args = $request->getAttribute('routeInfo')[2];
        $tid = $args['id'];

        $fid = Forum::getId($tid);
        $permission = Forum::canModerate($fid);

        if (!$permission) {
            throw new Error(__('No permission'), 403);
        }

        $response = $next($request, $response);
        return $response;
    }
}
