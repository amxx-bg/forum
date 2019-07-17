<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Controller\Api;

class Forum extends Api
{
    private $model;
    
    public function __construct()
    {
        $this->model = new \AMXXBG\Model\Api\Forum();
    }

    public function display($req, $res, $args)
    {
        return json_encode($this->model->display($args['id']), JSON_PRETTY_PRINT);
    }
}
