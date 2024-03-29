<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Model\Api;

use AMXXBG\Core\Database as DB;
use AMXXBG\Core\Interfaces\Input;
use AMXXBG\Core\Interfaces\User;
use AMXXBG\Core\Random;
use AMXXBG\Core\Utils;

/**
 * Api base class, used to authenticate the user
 * @package AMXXBG\Model\Api
 */
class Api
{
    protected $errorMessage = ["error" => "Not Found"];

    protected $connected = false;

    protected $isAdMod = false;

    protected $tmpUser;

    protected $user;

    /**
     * Api constructor.
     * Perform the authentication using given token, id, username or nothing at all.
     * If an username or ID is given, compare to the provided token.
     * Otherwise, use current session based on cookies.
     */
    public function __construct()
    {
        // Get the user ID from its name
        if (Input::query('username')) {
            $userId = DB::table('users')
                ->whereLike('username', Input::query('username'))
                ->findOneCol('id');
            if ($userId) {
                $this->tmpUser = User::get($userId);
            }
        }
        // Load the user object from its ID directly
        elseif (Input::query('id')) {
            $this->tmpUser = User::get(Input::query('id'));
        }

        // Validate authentication using the token...
        if (Input::query('token') &&                                                         // We have a token
            (Input::query('username') || Input::query('id')) &&                              // User's ID or username are provided
            is_object($this->tmpUser) &&                                                     // The user loaded above exists
            Utils::hashEquals(self::getToken($this->tmpUser), Input::query('token'))) {     // Provided token is correct
            $this->connected = true;
            $this->user = $this->tmpUser;
        }
        // ... or login as guest
        else {
            $this->user = User::get(1);
        }

        // If he is admin or moderator
        if (User::isAdminMod($this->user)) {
            $this->isAdMod = true;
        }
    }

    /**
     * Get token for the given user
     * @param $user User instance to give the token of
     * @return string Token wanted
     */
    public static function getToken($user)
    {
        return Random::hash($user->password.$user->registered.$user->username);
    }
}
