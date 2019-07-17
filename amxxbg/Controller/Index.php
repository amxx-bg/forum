<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Controller;

use AMXXBG\Core\Error;
use AMXXBG\Core\Interfaces\ForumSettings;
use AMXXBG\Core\Interfaces\Hooks;
use AMXXBG\Core\Interfaces\Lang;
use AMXXBG\Core\Interfaces\Router;
use AMXXBG\Core\Interfaces\User;
use AMXXBG\Core\Interfaces\View;
use AMXXBG\Core\Track;
use AMXXBG\Core\Utils;
use AMXXBG\Model\Auth;

class Index
{
    public function __construct()
    {
        $this->model = new \AMXXBG\Model\Index();
        Lang::load('index');
        Lang::load('misc');
    }

    public function display($req, $res, $args)
    {
        Hooks::fire('controller.index.index');
        return View::setPageInfo([
            'title' => [Utils::escape(ForumSettings::get('o_board_title'))],
            'active_page' => 'index',
            'is_indexed' => true,
            'index_data' => $this->model->printCategoriesForums(),
            'stats' => $this->model->stats(),
            'online'    =>    $this->model->usersOnline(),
            'forum_actions'        =>    $this->model->forumActions(),
            'cur_cat'   => 0
        ])->addTemplate('@forum/index')->display();
    }

    public function rules()
    {
        Hooks::fire('controller.index.rules');

        if (ForumSettings::get('o_rules') == '0' || (User::get()->is_guest && !User::can('board.read') && ForumSettings::get('o_regs_allow') == '0')) {
            throw new Error(__('Bad request'), 404);
        }

        return View::setPageInfo([
            'title' => [Utils::escape(ForumSettings::get('o_board_title')), __('Forum rules')],
            'active_page' => 'rules'
            ]
        )->addTemplate('@forum/misc/rules')->display();
    }

    public function markread()
    {
        Hooks::fire('controller.index.markread');

        Auth::setLastVisit(User::get()->id, User::get()->logged);
        // Reset tracked topics
        Track::setTrackedTopics(null);
        return Router::redirect(Router::pathFor('home'), __('Mark read redirect'));
    }
}
