<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +-------------------------------------------------------------------------+
// | My Program 1.0 for Geeklog- The Ultimate OSS Portal                     |
// | Date: Month Day, 2003                                                   |
// +-------------------------------------------------------------------------+
// | debug.php - Common code to show POST and GET variables                  |
// +-------------------------------------------------------------------------+
// | Copyright (C) 2003 by the following authors:                            |
// |                                                                         |
// | Author:                                                                 |
// | Blaine Lang                 -    blaine@portalparts.com                 |
// +-------------------------------------------------------------------------+
// |                                                                         |
// | This program is free software; you can redistribute it and/or           |
// | modify it under the terms of the GNU General Public License             |
// | as published by the Free Software Foundation; either version 2          |
// | of the License, or (at your option) any later version.                  |
// |                                                                         |
// | This program is distributed in the hope that it will be useful,         |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                    |
// | See the GNU General Public License for more details.                    |
// |                                                                         |
// | You should have received a copy of the GNU General Public License       |
// | along with this program; if not, write to the Free Software Foundation, |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.         |
// |                                                                         |
// +-------------------------------------------------------------------------+
//

// Debug Code to show variables
if ($_CONFQUIZ['debug']) {
    if (!empty($HTTP_POST_VARS)) {
        echo COM_startBlock("HTTP_POST_VARS");
        var_dump($HTTP_POST_VARS);
        echo COM_endBlock();
    }
    if (!empty($HTTP_GET_VARS)) {
        echo COM_startBlock("HTTP_GET_VARS");
        var_dump($HTTP_GET_VARS);
        echo COM_endBlock();
    }

    if (!empty($HTTP_POST_FILES)) {
        echo COM_startBlock("HTTP_POST_FILES");
        var_dump($HTTP_POST_FILES);
        echo COM_endBlock();
    }
}

?>