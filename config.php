<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +-------------------------------------------------------------------------+
// | Quiz Plugin 1.0 for Geeklog- The Ultimate OSS Portal                    |
// | Date: July 25, 2003                                                     |
// +-------------------------------------------------------------------------+
// | config.php                                                              |
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

$_CONFQUIZ['version'] = '0.8.3';

$_TABLES['quiz_master']        = $_DB_table_prefix . 'quiz_master';
$_TABLES['quiz_questions']     = $_DB_table_prefix . 'quiz_questions';
$_TABLES['quiz_answers']       = $_DB_table_prefix . 'quiz_answers';
$_TABLES['quiz_results']       = $_DB_table_prefix . 'quiz_results';
$_TABLES['quiz_images']        = $_DB_table_prefix . 'quiz_images';

$_CONFQUIZ ['debug']           = false;        // Set to true to see dump out POST and GET vars
$_CONFQUIZ ['numanswers']      = 5;           // Default number of answers for Quiz Questions

/* Setting for Image Upload Script */
$_CONFQUIZ['allowableImageTypes'] = array(
    'image/bmp'        => '.bmp,.ico',
    'image/gif'        => '.gif',
    'image/pjpeg'      => '.jpg,.jpeg',
    'image/jpeg'       => '.jpg,.jpeg',
    'image/png'        => '.png',
    'image/x-png'      => '.png'
);
$_CONFQUIZ['ROOT_DIR'] = 'C:/Program Files/GnuWin32/bin';       // Where Image Conversion Tools are
$_CONFQUIZ['annotation_font'] = $_CONF['path_html'] . 'club/annotate.ttf';
$_CONFQUIZ['convert_tool'] = 'gd';
$_CONFQUIZ['gd_type'] = 'GD2';
$_CONFQUIZ['image_width'] = 500;
$_CONFQUIZ['image_quality'] = 90;
$_CONFQUIZ['max_upload_width'] = 1280;
$_CONFQUIZ['max_upload_height'] = 1024;
$_CONFQUIZ['max_upload_size'] = 1048576;
$_CONFQUIZ['auto_thumbnail_dimension'] = 150;
$_CONFQUIZ['auto_thumbnail_resize_type'] = 1;
$_CONFQUIZ['auto_thumbnail_quality'] = 90;
$_CONFQUIZ['bmpconvertcmd'] = $_CONFQUIZ['ROOT_DIR'] . "/bmptoppm %s | " .$_CONFQUIZ['ROOT_DIR']. "/ppmtojpeg > %s";
$_CONFQUIZ['gifconvertcmd'] = $_CONFQUIZ['ROOT_DIR'] . "/giftopnm %s | " .$_CONFQUIZ['ROOT_DIR']. "/pnmtojpeg > %s";

//$_CONFQUIZ ['status']          = array("Hidden","Active");

?>