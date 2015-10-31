<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Geeklog Quiz Plugin 0.7 for Geeklog - The Ultimate Weblog                 |
// | Release date: July 19,2003                                                |
// +---------------------------------------------------------------------------+
// | upgrade_v0.7  - upgrade from version 0.5                                  |
// | Quiz Plugin admin settings                                                |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2000,2001 by the following authors:                         |
// | Geeklog Author: Tony Bibbs       - tony@tonybibbs.com                     |
// +---------------------------------------------------------------------------+
// | Quiz Plugin Authors                                                       |
// | Blaine Lang,    contact: geeklog@langfamily.ca   www.langfamily.ca        |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//
include_once('../../../lib-common.php');
include_once($_CONF['path'] . 'plugins/quiz/config.php');

function plugin_upgrade_quiz() {
    global $_TABLES, $_CONF;
    
    require_once($_CONF['path'] . 'plugins/quiz/sql/updates/mysql_0.5_to_0.7.php');
    COM_errorLOG("Begin processing {$_CONF['path']}plugins/quiz/sql/updates/mysql_0.5_0.7.php\nRecord Count is:" .count($_SQL));
    for ($i = 1; $i <= count($_SQL); $i++) {
        $progress .= "executing " . current($_SQL) . "<br>\n";
        COM_errorLOG("executing " . current($_SQL));
        DB_query(current($_SQL));
        next($_SQL);
    }

return true;

}


COM_errorLog("Geeklog Quiz Plugin Ver 0.7 Upgrade initiated",1);
$display = COM_siteHeader();
$display .= COM_startBlock("Geeklog Quiz Plugin Ver 0.7 Upgrade");
$display .= "<p><Making Geeklog Quiz Plugin Table changes - should not effect data";
if (plugin_upgrade_quiz()) {
    $display .= "<p>Sucesss ...<p>Check error.log for details ";
    COM_errorLog('...success',1);
} else {
    $display .= "<p>There was a SQL error, Manual Investigation required. Check error.log for details";
    COM_errorLog('Error ...  Upgrade did not complete successfully',1);
}

$display .= COM_endBlock();
$display .= COM_siteFooter();
echo $display; 

?>