<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +-------------------------------------------------------------------------+
// | Quiz Plugin 1.0 for Geeklog- The Ultimate OSS Portal                    |
// | Date: July 25, 2003                                                     |
// +-------------------------------------------------------------------------+
// | language.php                                                            |
// +-------------------------------------------------------------------------+
// | Copyright (C) 2003 by the following authors:                            |
// |                                                                         |
// | Author:                                                                 |
// | Blaine Lang                 -    langmail@sympatico.ca                  |
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

$LANG_QUIZ = array (
    'admin_only    '        => 'Sorry Admins Only. If you are an Admin please login first.',
    'plugin'                => 'Plugin',
    'searchlabel'           => 'Quizes',
    'statslabel'            => 'Quiz Plugin - Label',
    'statsheading1'         => 'Quiz Plugin - Heading1',
    'searchresults'         => 'Quiz Plugin Search Results',
    'useradminmenu'         => 'Quiz Plugin Settings',
    'useradmintitle'        => 'Quiz Plugin User Preferences',
    'access_denied'         => 'Access Denied',
    'access_denied_msg'     => 'Only Root Users have Access to this Page.  Your user name and IP have been recorded.',
    'admin'                 => 'Plugin Admin',
    'install_header'        => 'Install/Uninstall Plugin',
    'installed'             => 'The Plugin is now installed,<p><i>Enjoy,<br><a href="MAILTO:langmail@sympatico.ca">Blaine</a></i>',
    'uninstalled'           => 'The Plugin is Not Installed',
    'install_success'       => 'Installation Successful<p><b>Next Steps</b>: 
        <ol><li>Use the Quiz Plugin Admin to configure your defailt settings
        <li>Add new quiz and setup questions and answers</ol>
        <p>Review the <a href="%s">Install Notes</a> for more information.',
        
    'install_failed'        => 'Installation Failed -- See your error log to find out why.',
    'uninstall_msg'         => 'Plugin Successfully Uninstalled',
    'install'               => 'Install',
    'uninstall'             => 'UnInstall',
    'enabled'               => '<br>Plugin is installed and enabled.<br>Disable first if you want to De-Install it.<p>',
    'warning'               => 'Quiz Plugin De-Install Warning',
    'helpmsg'               => 'To view a quiz, click on the title of the quiz. To modify or delete a Quiz - use <b >Edit</b> for that quiz. To create a new quiz click on <b>New Quiz</b> or use <b>Copy</b> to create a copy of an exising quiz.<br><br>'
);

$LANG_QZMSG = array (
    'helpmsg01'             => 'To take a quiz, click on the title of the quiz. If you have already taken this test you will see a <font color="green">Green</font> or <font color="red">Red</font> indication by each Quiz record. Your best result for the quiz will be shown including a link in the <i>Options</i> column to view the detail results. If a pretest is required, the status of the pretest (Pass or Fail) will be shown and the <i>Options</i> column will have a descriptive link to the defined pretest.<br><br>',
    'msg99'             => ''
);

$LANG_QZERR = array  (
    'noaccess'         =>  'Anonymous Access not permitted. Add proper Error Message here',
    'err99'         =>  ''
)


?>