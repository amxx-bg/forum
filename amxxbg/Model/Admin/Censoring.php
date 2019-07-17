<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Model\Admin;

use AMXXBG\Core\Database as DB;
use AMXXBG\Core\Error;
use AMXXBG\Core\Interfaces\Cache as CacheInterface;
use AMXXBG\Core\Interfaces\Hooks;
use AMXXBG\Core\Interfaces\Input;
use AMXXBG\Core\Interfaces\Router;
use AMXXBG\Core\Utils;
use AMXXBG\Model\Cache;

class Censoring
{
    public function addWord()
    {
        $searchFor = Utils::trim(Input::post('new_search_for'));
        $replaceWith = Utils::trim(Input::post('new_replace_with'));

        if ($searchFor == '') {
            throw new Error(__('Must enter word message'), 400);
        }

        $setSearchWord = ['search_for' => $searchFor,
                                'replace_with' => $replaceWith];

        $setSearchWord = Hooks::fire('model.admin.censoring.add_censoring_word_data', $setSearchWord);

        $result = DB::table('censoring')
            ->create()
            ->set($setSearchWord)
            ->save();

        // Regenerate the censoring cache
        CacheInterface::store('search_for', Cache::getCensoring('search_for'));
        CacheInterface::store('replace_with', Cache::getCensoring('replace_with'));

        return Router::redirect(Router::pathFor('adminCensoring'), __('Word added redirect'));
    }

    public function updateWord()
    {
        $id = intval(key(Input::post('update')));

        $searchFor = Utils::trim(Input::post('search_for')[$id]);
        $replaceWith = Utils::trim(Input::post('replace_with')[$id]);

        if ($searchFor == '') {
            throw new Error(__('Must enter word message'), 400);
        }

        $setSearchWord = ['search_for' => $searchFor,
                                'replace_with' => $replaceWith];

        $setSearchWord = Hooks::fire('model.admin.censoring.update_censoring_word_start', $setSearchWord);

        $result = DB::table('censoring')
            ->findOne($id)
            ->set($setSearchWord)
            ->save();

        // Regenerate the censoring cache
        CacheInterface::store('search_for', Cache::getCensoring('search_for'));
        CacheInterface::store('replace_with', Cache::getCensoring('replace_with'));

        return Router::redirect(Router::pathFor('adminCensoring'), __('Word updated redirect'));
    }

    public function removeWord()
    {
        $id = intval(key(Input::post('remove')));
        $id = Hooks::fire('model.admin.censoring.remove_censoring_word_start', $id);

        $result = DB::table('censoring')->findOne($id);
        $result = Hooks::fireDB('model.admin.censoring.remove_censoring_word', $result);
        $result = $result->delete();

        // Regenerate the censoring cache
        CacheInterface::store('search_for', Cache::getCensoring('search_for'));
        CacheInterface::store('replace_with', Cache::getCensoring('replace_with'));

        return Router::redirect(Router::pathFor('adminCensoring'), __('Word removed redirect'));
    }

    public function getWords()
    {
        $wordData = [];

        $wordData = DB::table('censoring')
                        ->orderByAsc('id');
        $wordData = Hooks::fireDB('model.admin.censoring.update_censoring_word_query', $wordData);
        $wordData = $wordData->findArray();

        return $wordData;
    }
}
