<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +-------------------------------------------------------------------------+
// | Quiz Plugin 1.0 for Geeklog- The Ultimate OSS Portal                    |
// | Date: July 25, 2003                                                     |
// +-------------------------------------------------------------------------+
// | functions.inc                                                           |
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


$langfile = $_CONF['path'] . 'plugins/quiz/language/' . $_CONF['language'] . '.php';
if (file_exists ($langfile)) 
{
    include_once ($langfile);
}
else 
{
    include_once ($_CONF['path'] . 'plugins/quiz/language/english.php');
}

require_once ($_CONF['path'] . 'plugins/quiz/config.php');
require_once ($_CONF['path_system'] . 'lib-portalparts.php');

/**
* Returns the items for this plugin that should appear on the main menu
*/
function plugin_getmenuitems_quiz()
{
    global $_TABLES,$_CONF;
    if ( SEC_hasRights('quiz.user')) {
        $menuitems['quizes'] ="$_CONF[site_url]/quiz/index.php";
        return $menuitems;
    }
}


/**
* Geeklog is checking to see if this plugin supports comments
*/
function plugin_commentsupport_quiz() 
{
    // Not implemented yet
    return false;
}



/**
* This will put an option for quiz admin in the command and control block on moderation.php
*
*/
function plugin_cclabel_quiz() 
{
    global $_CONF;
    if (SEC_hasRights('quiz.edit')) {
        return array('quizes',$_CONF['site_admin_url'] . "/plugins/quiz/index.php",$_CONF['site_url'] . '/quiz/images/quiz.gif');
    }
}

/**
* returns the administrative option for this plugin
*
*/
function plugin_getadminoption_quiz() 
{
    global $_TABLES, $_CONF;

    if (SEC_hasRights('quiz.edit')) {
    $numquizes = DB_getITEM($_TABLES['quiz_master'],"count(*)");
    return array('Quizes', $_CONF['site_admin_url'] . '/plugins/quiz/index.php', $numquizes);
    }    

}

/**
* Called by the plugin Editor to display the current plugin code version 
* This may be different then the version installed and registered currently.
* If newer then you may want to run the update
*/
function plugin_chkVersion_guiz() {
    global $_CONFQUIZ; 
    return $_CONFQUIZ['version'];
}


/**
* Removes the datastructures for this plugin from the Geeklog database
* This may get called by the install routine to undue anything created during the install.
* Added check to see that plugin is first disabled.
*/  
function plugin_uninstall_quiz($installCheck='')
{
    global $_CONF,$_TABLES, $LANG_QUIZ;

    $pi_name='quiz';
    $FEATURES = array ('quiz.edit', 'quiz.user');
    $TABLES = array ('quiz_master','quiz_questions','quiz_answers','quiz_results','quiz_images');
  
     // Check and see if plugin is still enabled - if so display warning and exit
    if ($installCheck != '' && DB_getItem($_TABLES['plugins'],'pi_enabled', 'pi_name = "' .$pi_name. '"')) {
        COM_errorLog("Plugin is installed and enabled. Disable first if you want to de-install it",1);
        $display .= COM_startBlock($LANG_QUIZ['warning']);
        $display .= $LANG_QUIZ['enabled'];
        $display .= COM_endBlock();
        echo $display;
        exit;
    }
    
    // Ok to proceed and delete plugin - Unregister the plugin with Geeklog
    COM_errorLog('Attempting to unregister the quiz plugin from Geeklog',1);
    DB_query("DELETE FROM {$_TABLES['plugins']} WHERE pi_name = 'quiz'",1);
    // Drop quiz plugin tables
    foreach($TABLES as $table) {
        $t = $_TABLES["$table"];
        COM_errorLog("Removing Table $t",1);
        DB_query("DROP TABLE $t",1);
    }

    // Remove Admin Security for this plugin
    $grp_id = DB_getItem($_TABLES['vars'], 'value', "name = '{$pi_name}_admin'");
    COM_errorLog("Removing $pi_name Admin Group", 1);
    DB_query("DELETE FROM {$_TABLES['groups']} WHERE grp_id = $grp_id",1);
    DB_query("DELETE FROM {$_TABLES['vars']} WHERE name = '{$pi_name}_admin'");
    COM_errorLog("Removing root users from admin of $pi_name");
    DB_query("DELETE FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id = $grp_id",1);
    
    // Remove all the associated features - access rights. The feature ID's were stored in the vars table during install.
    foreach ($FEATURES as $feature) {
        COM_errorLog("Removing $feature feature and rights to it",1);
        $feat_id = DB_getItem($_TABLES['features'], 'ft_id', "ft_name = '$feature'");
        COM_errorLog("DELETE FROM {$_TABLES['access']} WHERE acc_ft_id = $feat_id");
        DB_query("DELETE FROM {$_TABLES['access']} WHERE acc_ft_id = $feat_id",1);
        DB_query("DELETE FROM {$_TABLES['features']} WHERE ft_id = $feat_id",1);
    }

    /* Delete members images */
    $ImageDir = $_CONF['path_html'] . "quiz/question_images/";
    if ($handle = @opendir($ImageDir)) { 
        while ($file = readdir($handle)) { 
            if ($file=='.' || $file=='..') continue; 
            @unlink ($ImageDir .$file);
        }
    }
   
    COM_errorLog('...success',1);
    return true;
}


function qz_preparefordb($str,$htmlallowed=false,$htmlfilter=false,$censor=false) {
    if ($censor) {
        $str = COM_checkWords($str);
    }
    if($htmlallowed) {
        if(!get_magic_quotes_gpc() ) {
            if ($htmlfilter) {
                $str = addslashes(COM_checkHTML($str));
            } else {
                $str = addslashes($str);
            }
        } else {
            if ($htmlfilter) {
                // COM_checkHTML will strip slashes so I need to add them again
                $str = addslashes(COM_checkHTML($str));
            } else {
                $str = $str;
            }
        }
    } else {
        if(get_magic_quotes_gpc() ) {
            $str = htmlspecialchars($str);
        } else {
            $str = addslashes(htmlspecialchars($str));
        }
    }
    return $str;
}

/* Callback Function for the array walk function below to apply
   Clean any posted data before saving to the database
*/
function qz_cleanfield(&$field) {
    if (is_array($field)) {
         array_walk($field,'qz_cleanfield');
    } else {
        $field = qz_preparefordb($field);
    }
}

// Function to clean any posted data before saving to database
function qz_cleandata($postarray) {
    array_walk($postarray,'qz_cleanfield');
    return $postarray;
}




function qz_GetData($vars,$setglobal=false,$type='')  {
  $return_data = array();

  #setup common reference to SuperGlobals depending which array is needed
  if ($type == "GET" OR $type == "POST") {
    if ($type =="GET") { $SG_Array =& $_GET; }
    if ($type =="POST") { $SG_Array =& $_POST; }

    # loop through SuperGlobal data array and grab out data for allowed fields if found
    foreach($vars as $key)  {
      if (array_key_exists($key,$SG_Array)) { $return_data[$key]=$SG_Array[$key]; }
    }

  } else {
    foreach ($vars as $key) {
      if (array_key_exists($key, $_POST)) { 
        $return_data[$key] = $_POST[$key];
      } elseif (array_key_exists($key, $_GET)) { 
        $return_data[$key] = $_GET[$key];
      }
    }
  }

    # loop through $vars array and apply the filter
    foreach($vars as $value)  {
      $return_data[$value]  = qz_ApplyFilter($return_data[$value]);
    }

  // Optionally set $GLOBALS or return the array
  if ($setglobal) {
      # loop through final data and define all the variables using the $GLOBALS array
      foreach ($return_data as $key=>$value)  {
        $GLOBALS[$key]=$value;
      }
  } else {
      return $return_data;
  }

}

function qz_ApplyFilter( $parameter, $isnumeric = false ,$returnzero=true) {

    $p = COM_stripslashes( $parameter );
    $p = strip_tags( $p );
    $p = COM_killJS( $p );

    if( $isnumeric ) {
        // Note: PHP's is_numeric() accepts values like 4e4 as numeric
        // Strip out any common number formatting characters
        $p = preg_replace('/[\s-\(\)]+/', '', $p );
        if( !is_numeric( $p ) || ( preg_match( '/^([0-9]+)$/', $p ) == 0 )) {
            if ($returnzero) {
                $p = 0;
            } else {
                $p = '';
            }
        }
    } else {
        $pa = explode( "'", $p );
        $pa = explode( '"', $pa['0'] );
        $pa = explode( '`', $pa['0'] );
        $p = $pa['0'];
    }

    return $p;
}



?>