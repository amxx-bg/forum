<?php
/**
 * Copyright (C) 2019 AMXXBG
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Core;

use AMXXBG\Core\Interfaces\ForumEnv;

class CoreAutoUpdater extends AutoUpdater
{
    public function __construct()
    {
        // Construct parent class
        parent::__construct(getcwd().'/temp', getcwd());

        // Set plugin informations
        $this->setRootFolder('amxxbg');
        $this->setCurrentVersion(ForumEnv::get('FORUM_VERSION'));
        $this->setUpdateUrl('https://api.github.com/repos/amxxbg/amxxbg/releases');
    }
}