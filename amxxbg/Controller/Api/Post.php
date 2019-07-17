<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Controller\Api;

use AMXXBG\Core\Interfaces\ForumSettings;
use AMXXBG\Core\Utils;

class Post extends Api
{
    private $model;
    
    public function __construct()
    {
        $this->model = new \AMXXBG\Model\Api\Post();
    }

    public function display($req, $res, $args)
    {
        return json_encode($this->model->display($args['id']), JSON_PRETTY_PRINT);
    }

    public function delete($req, $res, $args)
    {
        // Fetch some information about the post, the topic and the forum
        $curPost = $this->model->getInfoDelete($args['id']);

        if (!is_object($curPost)) {
            return $curPost;
        }

        if (ForumSettings::get('o_censoring') == '1') {
            $curPost['subject'] = Utils::censor($curPost['subject']);
        }

        // Sort out who the moderators are and if we are currently a moderator (or an admin)
        $isTopicPost = $this->model->getDeletePermissions($curPost, $args);

        \AMXXBG\Model\Post::handleDeletion($isTopicPost, $args['id'], $curPost);

        return json_encode("Success", JSON_PRETTY_PRINT);
    }

    public function update($req, $res, $args)
    {
        // Fetch some information about the post, the topic and the forum
        $curPost = $this->model->getInfoEdit($args['id']);

        if (!is_object($curPost)) {
            return $curPost;
        }

        $isAdmmod = $this->model->getEditPermissions($curPost);

        $canEditSubject = $args['id'] == $curPost['first_post_id'];

        if (ForumSettings::get('o_censoring') == '1') {
            $curPost['subject'] = Utils::censor($curPost['subject']);
            $curPost['message'] = Utils::censor($curPost['message']);
        }

        // Start with a clean slate
        $errors = [];

        // Let's see if everything went right
        $errors = \AMXXBG\Model\Post::checkErrorsEdit($canEditSubject, $errors, $isAdmmod);

        // Setup some variables before post
        $post = \AMXXBG\Model\Post::setupEditVariables($curPost, $isAdmmod, $canEditSubject, $errors);

        // Did everything go according to plan?
        if (empty($errors)) {
            // Edit the post
            $this->model->update($args, $canEditSubject, $post, $curPost, $isAdmmod);

            return json_encode($this->model->display($args['id']), JSON_PRETTY_PRINT);
        }

        return json_encode($errors, JSON_PRETTY_PRINT);
    }
}
