<?php

/**
 * Copyright (C) 2019 AMXXBG
 * based on code by (C) 2008-2015 FluxBB
 * and Rickard Andersson (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher.
 */

namespace AMXXBG\Controller\Admin;

use AMXXBG\Core\AdminUtils;
use AMXXBG\Core\Error;
use AMXXBG\Core\Interfaces\Container;
use AMXXBG\Core\Interfaces\Cache as CacheInterface;
use AMXXBG\Core\Interfaces\ForumSettings;
use AMXXBG\Core\Interfaces\Hooks;
use AMXXBG\Core\Interfaces\Input;
use AMXXBG\Core\Interfaces\Lang;
use AMXXBG\Core\Interfaces\Router;
use AMXXBG\Core\Interfaces\User;
use AMXXBG\Core\Interfaces\View;
use AMXXBG\Core\Utils;
use AMXXBG\Model\Cache;

class Categories
{
    public function __construct()
    {
        $this->model = new \AMXXBG\Model\Admin\Categories();
        Lang::load('admin/categories');
        if (!User::isAdmin()) {
            throw new Error(__('No permission'), '403');
        }
    }

    public function add($req, $res, $args)
    {
        Hooks::fire('controller.admin.categories.add');

        $catName = Utils::trim(Input::post('cat_name'));
        if ($catName == '') {
            return Router::redirect(Router::pathFor('adminCategories'), __('Must enter name message'));
        }

        if ($this->model->addCategory($catName)) {
            return Router::redirect(Router::pathFor('adminCategories'), __('Category added redirect'));
        } else { //TODO, add error message
            return Router::redirect(Router::pathFor('adminCategories'), __('Category added redirect'));
        }
    }

    public function edit($req, $res, $args)
    {
        Hooks::fire('controller.admin.categories.edit');

        if (empty(Input::post('cat'))) {
            throw new Error(__('Bad request'), '400');
        }

        foreach (Input::post('cat') as $catId => $properties) {
            $category = ['id' => (int) $catId,
                              'name' => Utils::escape($properties['name']),
                              'order' => (int) $properties['order'],];
            if ($category['name'] == '') {
                return Router::redirect(Router::pathFor('adminCategories'), __('Must enter name message'));
            }
            $this->model->updateCategory($category);
        }

        // Regenerate the quick jump cache
        CacheInterface::store('quickjump', Cache::quickjump());

        return Router::redirect(Router::pathFor('adminCategories'), __('Categories updated redirect'));
    }

    public function delete($req, $res, $args)
    {
        Hooks::fire('controller.admin.categories.delete');

        $catToDelete = (int) Input::post('cat_to_delete');

        if ($catToDelete < 1) {
            throw new Error(__('Bad request'), '400');
        }

        if (intval(Input::post('disclaimer')) != 1) {
            return Router::redirect(Router::pathFor('adminCategories'), __('Delete category not validated'));
        }

        if ($this->model->deleteCategory($catToDelete)) {
            return Router::redirect(Router::pathFor('adminCategories'), __('Category deleted redirect'));
        } else {
            return Router::redirect(Router::pathFor('adminCategories'), __('Unable to delete category'));
        }
    }

    public function display($req, $res, $args)
    {
        Hooks::fire('controller.admin.categories.display');

        AdminUtils::generateAdminMenu('categories');

        View::setPageInfo([
                'title' => [Utils::escape(ForumSettings::get('o_board_title')), __('Admin'), __('Categories')],
                'active_page' => 'admin',
                'admin_console' => true,
                'cat_list' => $this->model->categoryList(),
        ])->addTemplate('@forum/admin/categories')->display();
    }
}
