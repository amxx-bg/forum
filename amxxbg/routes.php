<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

use AMXXBG\Core\Error;
use AMXXBG\Core\Interfaces\Container;
use AMXXBG\Core\Interfaces\ForumEnv;
use AMXXBG\Core\Interfaces\ForumSettings;
use AMXXBG\Core\Interfaces\Route;
use AMXXBG\Core\Interfaces\View;
use AMXXBG\Middleware\Admin as IsAdmin;
use AMXXBG\Middleware\AdminMod as IsAdmMod;
use AMXXBG\Middleware\JsonHeader;
use AMXXBG\Middleware\Logged as IsLogged;
use AMXXBG\Middleware\ModeratePermission;
use AMXXBG\Middleware\ReadBoard as CanReadBoard;

Route::map(['GET', 'POST'], '/install', '\AMXXBG\Controller\Install:run')->setName('install');

// Index
Route::get('/', '\AMXXBG\Controller\Index:display')->add(new CanReadBoard)->setName('home');
Route::get('/rules', '\AMXXBG\Controller\Index:rules')->setName('rules');
Route::get('/mark-read', '\AMXXBG\Controller\Index:markread')->add(new IsLogged)->setName('markRead');

// Forum
Route::group('/forum', function () {
    Route::get('/{id:\d+}/{name:[\w\-]+}[/page/{page:\d+}]', '\AMXXBG\Controller\Forum:display')->setName('Forum');
    Route::get('/{id:\d+}/{name:[\w\-]+}/mark-read', '\AMXXBG\Controller\Forum:markread')->add(new IsLogged)->setName('markForumRead');
    Route::get('/{id:\d+}/{name:[\w\-]+}/subscribe', '\AMXXBG\Controller\Forum:subscribe')->add(new IsLogged)->setName('subscribeForum');
    Route::get('/{id:\d+}/{name:[\w\-]+}/unsubscribe', '\AMXXBG\Controller\Forum:unsubscribe')->add(new IsLogged)->setName('unsubscribeForum');
    Route::get('/{id:\d+}/{name:[\w\-]+}/moderate/page/{page:\d+}', '\AMXXBG\Controller\Forum:moderate')->setName('moderateForum');
    Route::post('/{id:\d+}/{name:[\w\-]+}/moderate[/page/{page:\d+}]', '\AMXXBG\Controller\Forum:dealposts')->setName('dealPosts');
})->add(new CanReadBoard);

// Topic
Route::group('/topic', function () {
    Route::get('/{id:\d+}/{name:[\w\-]+}[/page/{page:\d+}]', '\AMXXBG\Controller\Topic:display')->setName('Topic');
    Route::get('/{id:\d+}/{name:[\w\-]+}/post/{pid:\d+}', '\AMXXBG\Controller\Topic:viewpost')->setName('viewPost');
    Route::get('/{id:\d+}/{name:[\w\-]+}/action/{action:[\w\-]+}', '\AMXXBG\Controller\Topic:action')->setName('topicAction');
    Route::get('/{id:\d+}/{name:[\w\-]+}/subscribe', '\AMXXBG\Controller\Topic:subscribe')->add(new IsLogged)->setName('subscribeTopic');
    Route::get('/{id:\d+}/{name:[\w\-]+}/unsubscribe', '\AMXXBG\Controller\Topic:unsubscribe')->add(new IsLogged)->setName('unsubscribeTopic');
    Route::get('/{id:\d+}/{name:[\w\-]+}/close', '\AMXXBG\Controller\Topic:close')->add(new IsAdmMod)->add(new ModeratePermission)->setName('closeTopic');
    Route::get('/{id:\d+}/{name:[\w\-]+}/open', '\AMXXBG\Controller\Topic:open')->add(new IsAdmMod)->add(new ModeratePermission)->setName('openTopic');
    Route::get('/{id:\d+}/{name:[\w\-]+}/stick', '\AMXXBG\Controller\Topic:stick')->add(new IsAdmMod)->add(new ModeratePermission)->setName('stickTopic');
    Route::get('/{id:\d+}/{name:[\w\-]+}/unstick', '\AMXXBG\Controller\Topic:unstick')->add(new IsAdmMod)->add(new ModeratePermission)->setName('unstickTopic');
    Route::map(['GET', 'POST'], '/{id:\d+}/{name:[\w\-]+}/move/from/{fid:\d+}', '\AMXXBG\Controller\Topic:move')->add(new IsAdmMod)->add(new ModeratePermission)->setName('moveTopic');
    Route::map(['GET', 'POST'], '/{id:\d+}/{name:[\w\-]+}/moderate/forum/{fid:\d+}[/page/{page:\d+}]', '\AMXXBG\Controller\Topic:moderate')->add(new IsAdmMod)->add(new ModeratePermission)->setName('moderateTopic');
})->add(new CanReadBoard);

// Post routes
Route::group('/post', function () {
    Route::map(['GET', 'POST'], '/new-topic/{fid:\d+}', '\AMXXBG\Controller\Post:newpost')->setName('newTopic');
    Route::map(['GET', 'POST'], '/reply/{tid:\d+}', '\AMXXBG\Controller\Post:newreply')->setName('newReply');
    Route::map(['GET', 'POST'], '/reply/{tid:\d+}/quote/{qid:\d+}', '\AMXXBG\Controller\Post:newreply')->setName('newQuoteReply');
    Route::map(['GET', 'POST'], '/delete/{id:\d+}', '\AMXXBG\Controller\Post:delete')->setName('deletePost');
    Route::map(['GET', 'POST'], '/edit/{id:\d+}', '\AMXXBG\Controller\Post:editpost')->setName('editPost');
    Route::map(['GET', 'POST'], '/report/{id:\d+}', '\AMXXBG\Controller\Post:report')->setName('report');
    Route::get('/get-host/{pid:\d+}', '\AMXXBG\Controller\Post:gethost')->setName('getPostHost');
})->add(new CanReadBoard);

// Userlist
Route::get('/userlist', '\AMXXBG\Controller\Userlist:display')->add(new CanReadBoard)->setName('userList');

// Auth routes
Route::group('/auth', function () {
    Route::map(['GET', 'POST'], '', '\AMXXBG\Controller\Auth:login')->setName('login');
    Route::map(['GET', 'POST'], '/forget', '\AMXXBG\Controller\Auth:forget')->setName('resetPassword');
    Route::get('/logout/token/{token}', '\AMXXBG\Controller\Auth:logout')->setName('logout');
});

// Register routes
Route::group('/register', function () {
    Route::get('', '\AMXXBG\Controller\Register:rules')->setName('registerRules');
    Route::map(['GET', 'POST'], '/agree', '\AMXXBG\Controller\Register:display')->setName('register');
    Route::get('/cancel', '\AMXXBG\Controller\Register:cancel')->setName('registerCancel');
});

// Search routes
Route::group('/search', function () {
    Route::get('[/{search_id:\d+}]', '\AMXXBG\Controller\Search:display')->setName('search');
    Route::get('/show/{show}', '\AMXXBG\Controller\Search:quicksearches')->setName('quickSearch');
})->add(new CanReadBoard);

// Help
Route::get('/help', '\AMXXBG\Controller\Help:display')->add(new CanReadBoard)->setName('help');

// Profile routes
Route::group('/user', function () {
    Route::map(['GET', 'POST'], '/{id:\d+}', '\AMXXBG\Controller\Profile:display')->setName('userProfile');
    Route::map(['GET', 'POST'], '/{id:\d+}/section/{section}', '\AMXXBG\Controller\Profile:display')->setName('profileSection');
    Route::map(['GET', 'POST'], '/{id:\d+}/action/{action}[/pid/{pid:\d+}]', '\AMXXBG\Controller\Profile:action')->setName('profileAction');
    Route::map(['GET', 'POST'], '/email/{id:\d+}', '\AMXXBG\Controller\Profile:email')->setName('email');
    Route::get('/get-host/{ip}', '\AMXXBG\Controller\Profile:gethostip')->setName('getHostIp');
})->add(new IsLogged);


// Admin routes

Route::group('/admin', function () {

    // Admin index
    Route::get('[/action/{action}]', '\AMXXBG\Controller\Admin\Index:display')->setName('adminAction');
    Route::get('/index', '\AMXXBG\Controller\Admin\Index:display')->setName('adminIndex');

    // Admin updates
    Route::get('/updates', '\AMXXBG\Controller\Admin\Updates:display')->setName('adminUpdates');
    Route::post('/updates/upgrade-core', '\AMXXBG\Controller\Admin\Updates:upgradeCore')->setName('adminUpgradeCore');
    Route::post('/updates/upgrade-plugins', '\AMXXBG\Controller\Admin\Updates:upgradePlugins')->setName('adminUpgradePlugins');
    Route::post('/updates/upgrade-themes', '\AMXXBG\Controller\Admin\Updates:upgradeThemes')->setName('adminUpgradeThemes');

    // Admin bans
    Route::group('/bans', function () {
        Route::get('', '\AMXXBG\Controller\Admin\Bans:display')->setName('adminBans');
        Route::get('/delete/{id:\d+}', '\AMXXBG\Controller\Admin\Bans:delete')->setName('deleteBan');
        Route::map(['GET', 'POST'], '/edit/{id:\d+}', '\AMXXBG\Controller\Admin\Bans:edit')->setName('editBan');
        Route::map(['GET', 'POST'], '/add[/{id:\d+}]', '\AMXXBG\Controller\Admin\Bans:add')->setName('addBan');
    });

    // Admin options
    Route::map(['GET', 'POST'], '/options', '\AMXXBG\Controller\Admin\Options:display')->add(new IsAdmin)->setName('adminOptions');

    // Admin categories
    Route::group('/categories', function () {
        Route::get('', '\AMXXBG\Controller\Admin\Categories:display')->setName('adminCategories');
        Route::post('/add', '\AMXXBG\Controller\Admin\Categories:add')->setName('addCategory');
        Route::post('/edit', '\AMXXBG\Controller\Admin\Categories:edit')->setName('editCategory');
        Route::post('/delete', '\AMXXBG\Controller\Admin\Categories:delete')->setName('deleteCategory');
    })->add(new IsAdmin);

    // Admin censoring
    Route::map(['GET', 'POST'], '/censoring', '\AMXXBG\Controller\Admin\Censoring:display')->add(new IsAdmin)->setName('adminCensoring');

    // Admin reports
    Route::map(['GET', 'POST'], '/reports', '\AMXXBG\Controller\Admin\Reports:display')->setName('adminReports');

    // Admin permissions
    Route::map(['GET', 'POST'], '/permissions', '\AMXXBG\Controller\Admin\Permissions:display')->add(new IsAdmin)->setName('adminPermissions');

    // Admin statistics
    Route::get('/statistics', '\AMXXBG\Controller\Admin\Statistics:display')->setName('statistics');
    Route::get('/phpinfo', '\AMXXBG\Controller\Admin\Statistics:phpinfo')->setName('phpInfo');

    // Admin forums
    Route::group('/forums', function () {
        Route::map(['GET', 'POST'], '', '\AMXXBG\Controller\Admin\Forums:display')->setName('adminForums');
        Route::post('/add', '\AMXXBG\Controller\Admin\Forums:add')->setName('addForum');
        Route::map(['GET', 'POST'], '/edit/{id:\d+}', '\AMXXBG\Controller\Admin\Forums:edit')->setName('editForum');
        Route::map(['GET', 'POST'], '/delete/{id:\d+}', '\AMXXBG\Controller\Admin\Forums:delete')->setName('deleteForum');
    })->add(new IsAdmin);

    // Admin groups
    Route::group('/groups', function () {
        Route::map(['GET', 'POST'], '', '\AMXXBG\Controller\Admin\Groups:display')->setName('adminGroups');
        Route::map(['GET', 'POST'], '/add', '\AMXXBG\Controller\Admin\Groups:addedit')->setName('addGroup');
        Route::map(['GET', 'POST'], '/edit/{id:\d+}', '\AMXXBG\Controller\Admin\Groups:addedit')->setName('editGroup');
        Route::map(['GET', 'POST'], '/delete/{id:\d+}', '\AMXXBG\Controller\Admin\Groups:delete')->setName('deleteGroup');
    })->add(new IsAdmin);

    // Admin plugins
    Route::group('/plugins', function () {
        Route::map(['GET', 'POST'], '', '\AMXXBG\Controller\Admin\Plugins:index')->setName('adminPlugins');
        Route::map(['GET', 'POST'], '/info/{name:[\w\-]+}', '\AMXXBG\Controller\Admin\Plugins:info')->setName('infoPlugin');
        Route::get('/activate/{name:[\w\-]+}', '\AMXXBG\Controller\Admin\Plugins:activate')->setName('activatePlugin');
        Route::get('/download/{name:[\w\-]+}/{version}', '\AMXXBG\Controller\Admin\Plugins:download')->setName('downloadPlugin');
        Route::get('/deactivate/{name:[\w\-]+}', '\AMXXBG\Controller\Admin\Plugins:deactivate')->setName('deactivatePlugin');
        Route::get('/uninstall/{name:[\w\-]+}', '\AMXXBG\Controller\Admin\Plugins:uninstall')->setName('uninstallPlugin');
    });

    // Admin maintenance
    Route::map(['GET', 'POST'], '/maintenance', '\AMXXBG\Controller\Admin\Maintenance:display')->add(new IsAdmin)->setName('adminMaintenance');

    // Admin parser
    Route::map(['GET', 'POST'], '/parser', '\AMXXBG\Controller\Admin\Parser:display')->add(new IsAdmin)->setName('adminParser');

    // Admin users
    Route::group('/users', function () {
        Route::map(['GET', 'POST'], '', '\AMXXBG\Controller\Admin\Users:display')->setName('adminUsers');
        Route::get('/ip-stats/id/{id:\d+}', '\AMXXBG\Controller\Admin\Users:ipstats')->setName('usersIpStats');
        Route::get('/show-users', '\AMXXBG\Controller\Admin\Users:showusers')->setName('usersIpShow');
    });
})->add(new IsAdmMod);

// API
Route::group('/api', function () {
    Route::get('/user/{id:\d+}', '\AMXXBG\Controller\Api\User:display')->setName('userApi');
    Route::get('/forum/{id:\d+}', '\AMXXBG\Controller\Api\Forum:display')->setName('forumApi');
    Route::post('/forum/{id:\d+}', '\AMXXBG\Controller\Api\Topic:newTopic')->setName('newTopicApi');
    Route::get('/topic/{id:\d+}', '\AMXXBG\Controller\Api\Topic:display')->setName('topicApi');
    Route::post('/topic/{id:\d+}[/quote/{qid:\d+}]', '\AMXXBG\Controller\Api\Topic:newReply')->setName('newReplyApi');
    Route::get('/post/{id:\d+}', '\AMXXBG\Controller\Api\Post:display')->setName('postApi');
    Route::delete('/post/{id:\d+}', '\AMXXBG\Controller\Api\Post:delete')->setName('deletePostApi');
    Route::put('/post/{id:\d+}', '\AMXXBG\Controller\Api\Post:update')->setName('updatePostApi');
    Route::patch('/post/{id:\d+}', '\AMXXBG\Controller\Api\Post:update')->setName('updatePostApi');
})->add(new JsonHeader);

// Override the default Not Found Handler
Container::set('notFoundHandler', function ($c) {
    return function ($request, $response) use ($c) {
        throw new Error(__('Bad request'), 404);
    };
});

Container::set('errorHandler', function ($c) {
    return function ($request, $response, $e) use ($c) {
        $error = [
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'back' => true,
            'html' => false,
            'hide' => false,
        ];

        // Hide internal mechanism
        if (!ForumEnv::get('AMXXBG_DEBUG')) {
            $error['message'] = function_exists('__') ? __('Error') : 'Error';
            $error['hide'] = true;
        }

        // Display a simple error page that does not require heavy user-specific methods like permissions
        if (method_exists($e, 'isSimpleError') && $e->isSimpleError()
            || !function_exists('__')) {
            if (ob_get_contents()) {
                ob_end_clean();
            }

            // ob_start to avoid an extra "1" returned by PHP with a successful inclusion
            ob_start();
            include(ForumEnv::get('AMXXBG_ROOT') . 'amxxbg/View/errorSimple.php');
            $include = ob_get_clean();

            $response->write($include);

            return $response;
        }

        if (method_exists($e, 'hasBacklink')) {
            $error['back'] = $e->hasBacklink();
        }

        if (method_exists($e, 'displayHtml')) {
            $error['html'] = $e->displayHtml();
        }

        return View::setPageInfo([
            'title' => [\AMXXBG\Core\Utils::escape(ForumSettings::get('o_board_title')), __('Error')],
            'msg'    =>    $error['message'],
            'backlink'    => $error['back'],
            'html'    => $error['html'],
        ])->addTemplate('@forum/error')->display();
    };
});
