<?php

/**
 * Copyright (C) 2019 AMXXBG
 * Parser (C) 2011 Jeff Roberson (jmrware.com)

 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

namespace AMXXBG\Model\Admin;

use AMXXBG\Core\Interfaces\ForumEnv;
use AMXXBG\Core\Interfaces\Hooks;

class Parser
{
    // Helper public function returns array of smiley image files
    //   stored in the style/img/smilies directory.
    public function getSmileyFiles()
    {
        $imgfiles = [];
        $filelist = scandir(ForumEnv::get('AMXXBG_ROOT').'style/img/smilies');
        $filelist = Hooks::fire('model.admin.parser.get_smiley_files.filelist', $filelist);
        foreach ($filelist as $file) {
            if (preg_match('/\.(?:png|gif|jpe?g)$/', $file)) {
                $imgfiles[] = $file;
            }
        }
        $imgfiles = Hooks::fire('model.admin.parser.get_smiley_files.imgfiles', $imgfiles);
        return $imgfiles;
    }
}
