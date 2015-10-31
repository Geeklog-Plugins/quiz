<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +-------------------------------------------------------------------------+
// | Portalparts Common Funcitons Library for Geeklog Plugins                |
// | Date: Jan 1, 2004                                                       |
// +-------------------------------------------------------------------------+
// | lib-portalparts.com                                                     |
// +-------------------------------------------------------------------------+
// | Copyright (C) 2004 by Consult4Hire Inc.                                 |
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

/* PortalPart Navbar Function */

function ppNavbar ($menuitems, $selected='', $parms='') {
    global $_CONF;

    $navbar = new Template($_CONF['path_layout'] . 'navbar');
    $navbar->set_file (array (
        'navbar'       => 'navbar.thtml',
        'menuitem'     => 'menuitem.thtml',
        ));
    for ($i=1; $i <= count($menuitems); $i++)  {
        $parms = explode( "=",current($menuitems) );
        $navbar->set_var( 'link',   current($menuitems));
        if (key($menuitems) == $selected) {
            $navbar->set_var( 'cssactive', 'id="active"');
            $navbar->set_var( 'csscurrent','id="current"');
        } else {
            $navbar->set_var( 'cssactive', '');
            $navbar->set_var( 'csscurrent','');
        }
        $navbar->set_var( 'label',  key($menuitems));
        $navbar->parse( 'menuitems', 'menuitem', true );
        next($menuitems);
    }
    $navbar->parse ('output', 'navbar');
    $retval = $navbar->finish($navbar->get_var('output'));
    return $retval;
}



function ppPrepareForDB($var) {
    // Need to call addslashes again as COM_checkHTML stips it out
    $var = COM_checkHTML($var);
    $var = addslashes($var);
    return $var;
}


function ppApplyFilter( $parameter, $isnumeric = false ,$returnzero=true) {

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


function ppGetData($vars,$setglobal=false,$type='')  {
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
      $return_data[$value]  = ppApplyFilter($return_data[$value]);
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

/* Convert a text based date YYYY-MM-DD to a unix timestamp integer value */
function ppConvertDate($date,$time='') {
        // Breakup the string using either a space, fwd slash, bkwd slash or colon as a delimiter
        $atok = strtok($date," /-\\:");
        while ($atok !== FALSE) {
            $atoks[] = $atok;
            $atok = strtok(" /-\\:");  // get the next token
        }
        if ($time == '') {
            return $timestamp = mktime(0,0,0,$atoks[1],$atoks[2],$atoks[0]);
        } else {
            echo "<br>Time is:$time";
            $btok = strtok($time," /-\\:");
            while ($btok !== FALSE) {
                $btoks[] = $btok;
                $btok = strtok(" /-\\:");
            }
            print_r($btoks);
            return $timestamp = mktime($btoks[0],$btoks[1],$btoks[2],$atoks[1],$atoks[2],$atoks[0]);
        }
}

?>