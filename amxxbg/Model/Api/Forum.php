<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Model\Api;

use AMXXBG\Core\Error;
use AMXXBG\Core\Interfaces\Hooks;

class Forum extends Api
{
    public function display($id)
    {
        $forum = new \AMXXBG\Model\Forum();

        Hooks::bind('model.forum.get_info_forum_query', function ($curForum) {
            $curForum = $curForum->select('f.num_posts');
            return $curForum;
        });

        try {
            $data = $forum->getForumInfo($id);
        } catch (Error $e) {
            return $this->errorMessage;
        }

        $data = $data->asArray();

        $data['moderators'] = unserialize($data['moderators']);

        return $data;
    }
}
