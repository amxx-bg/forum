<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Controller;

use AMXXBG\Core\Error;
use AMXXBG\Core\Interfaces\ForumEnv;
use AMXXBG\Core\Interfaces\ForumSettings;
use AMXXBG\Core\Interfaces\Hooks;
use AMXXBG\Core\Interfaces\Input;
use AMXXBG\Core\Interfaces\Lang;
use AMXXBG\Core\Interfaces\Request;
use AMXXBG\Core\Interfaces\Router;
use AMXXBG\Core\Interfaces\User;
use AMXXBG\Core\Interfaces\View;
use AMXXBG\Core\Utils;

class Register
{
    public function __construct()
    {
        $this->model = new \AMXXBG\Model\Register();
        Lang::load('register');
        Lang::load('prof_reg');
        Lang::load('antispam');
    }

    public function display($req, $res, $args)
    {
        Hooks::fire('controller.register.display');

        if (!User::get()->is_guest) {
            return Router::redirect(Router::pathFor('home'));
        }

        // Antispam feature
        $langAntispamQuestions = require ForumEnv::get('AMXXBG_ROOT').'amxxbg/lang/'.User::getPref('language').'/antispam.php';
        $indexQuestions = rand(0, count($langAntispamQuestions)-1);

        // Display an error message if new registrations are disabled
        // If $_REQUEST['username'] or $_REQUEST['password'] are filled, we are facing a bot
        if (ForumSettings::get('o_regs_allow') == '0' || Input::post('username') || Input::post('password')) {
            throw new Error(__('No new regs'), 403);
        }

        $user['errors'] = '';

        if (Request::isPost()) {
            $user = $this->model->checkErrors();

            // Did everything go according to plan? Insert the user
            if (empty($user['errors'])) {
                return $this->model->insertUser($user);
            }
        }

        View::setPageInfo([
                    'title' => [Utils::escape(ForumSettings::get('o_board_title')), __('Register')],
                    'active_page' => 'register',
                    'is_indexed' => true,
                    'errors' => $user['errors'],
                    'index_questions'    =>    $indexQuestions,
                    'languages' => \AMXXBG\Core\Lister::getLangs(),
                    'question' => array_keys($langAntispamQuestions),
                    'qencoded' => md5(array_keys($langAntispamQuestions)[$indexQuestions]),
            ]
        )->addTemplate('@forum/register/form')->display();
    }

    public function cancel($req, $res, $args)
    {
        Hooks::fire('controller.register.cancel');

        return Router::redirect(Router::pathFor('home'));
    }

    public function rules($req, $res, $args)
    {
        Hooks::fire('controller.register.rules');

        // If we are logged in, we shouldn't be here
        if (!User::get()->is_guest) {
            return Router::redirect(Router::pathFor('home'));
        }

        // Display an error message if new registrations are disabled
        if (ForumSettings::get('o_regs_allow') == '0') {
            throw new Error(__('No new regs'), 403);
        }

        if (ForumSettings::get('o_rules') != '1') {
            return Router::redirect(Router::pathFor('register'));
        }

        View::setPageInfo([
                'title' => [Utils::escape(ForumSettings::get('o_board_title')), __('Register'), __('Forum rules')],
                'active_page' => 'register',
            ]
        )->addTemplate('@forum/register/rules')->display();
    }
}
