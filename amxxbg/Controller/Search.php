<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Controller;

use AMXXBG\Core\Error;
use AMXXBG\Core\Interfaces\ForumSettings;
use AMXXBG\Core\Interfaces\Hooks;
use AMXXBG\Core\Interfaces\Input;
use AMXXBG\Core\Interfaces\Lang;
use AMXXBG\Core\Interfaces\Router;
use AMXXBG\Core\Interfaces\User;
use AMXXBG\Core\Interfaces\View;
use AMXXBG\Core\Utils;

class Search
{
    public function __construct()
    {
        $this->model = new \AMXXBG\Model\Search();
        Lang::load('userlist');
        Lang::load('search');
        Lang::load('topic');
        Lang::load('forum');
    }

    public function display($req, $res, $args)
    {
        Hooks::fire('controller.search.display');

        if (!User::can('search.topics')) {
            throw new Error(__('No search permission'), 403);
        }

        // Figure out what to do :-)
        if (Input::query('action') || isset($args['search_id'])) {
            $search = (isset($args['search_id'])) ? $this->model->getSearchResults($args['search_id']) : $this->model->getSearchResults();

            if (is_object($search)) {
                // $search is most likely a Router::redirect() to search page (no hits or other error) or to a search_id
                return $search;
            } else {

                // No results to display, redirect with message
                if (!isset($search['is_result'])) {
                    return Router::redirect(Router::pathFor('search'), ['error', __('No hits')]);
                }

                // We have results to display
                View::setPageInfo([
                    'title' => [Utils::escape(ForumSettings::get('o_board_title')), __('Search results')],
                    'active_page' => 'search',
                    'search' => $search,
                    'footer' => $search,
                ]);

                $display = $this->model->displaySearchResults($search);

                View::setPageInfo([
                    'display' => $display,
                ]);

                if ($search['show_as'] == 'posts') {
                    View::addTemplate('@forum/search/posts')->display();
                } else {
                    View::addTemplate('@forum/search/topics')->display();
                }
            }
        }
        // Display the form
        else {
            View::setPageInfo([
                'title' => [Utils::escape(ForumSettings::get('o_board_title')), __('Search')],
                'active_page' => 'search',
                'is_indexed' => true,
                'forums' => $this->model->forumsList(),
            ])->addTemplate('@forum/search/form')->display();
        }
    }

    public function quicksearches($req, $res, $args)
    {
        Hooks::fire('controller.search.quicksearches');

        return Router::redirect(Router::pathFor('search').'?action=show_'.$args['show']);
    }
}
