<?php

/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Core;

class Error extends \Exception
{
    protected $backlink;

    protected $html;

    protected $simpleError;

    public function __construct($message, $code = 400, $backlink = true, $html = false, $simpleError = false)
    {
        parent::__construct($message, $code);
        $this->backlink = (bool) $backlink;
        $this->html = (bool) $html;
        $this->simpleError = (bool) $simpleError;
    }

    public function hasBacklink()
    {
        return $this->backlink;
    }

    public function displayHtml()
    {
        return $this->html;
    }

    public function isSimpleError()
    {
        return $this->simpleError;
    }
}
