<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +-------------------------------------------------------------------------+
// | Quiz Plugin 1.0 for Geeklog- The Ultimate OSS Portal                    |
// | Date: July 25, 2003                                                     |
// +-------------------------------------------------------------------------+
// | addimage.php - Program to add an image to a question                    |
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

include_once($_CONF['path_system'] . 'classes/upload.class.php');

if (!file_exists($questionDir)) {
    mkdir ($questionDir);
    chmod ($questionDir,0755);
}

$upload = new upload();
$upload->setContinueOnError(true);
$upload->setMaxFileUploads('1');
$upload->setLogFile("$clubLogfile");
$upload->setLogging(false);
$upload->setAllowedMimeTypes($_CONFQUIZ['allowableImageTypes']);
if (!$upload->setPath($questionDir)) {
    print 'File Upload Errors:<BR>' . $upload->printErrors();
    exit;
}
$upload->setMaxDimensions($_CONFQUIZ['max_upload_width'], $_CONFQUIZ['max_upload_height']);
$upload->setMaxFileSize($_CONFQUIZ['max_upload_size']); 

// Set file permissions on file after it gets uploaded (number is in octet)
$upload->setPerms('0774');
$filenames = array();
$realfilenames = array();
$uploadtypes = array();
$uploadfilepos = array();

$upload->setDebug(true);
$upload->uploadFiles();

if ($upload->areErrors()) {
    $retval = COM_siteHeader('menu');
    $retval .= COM_startBlock('File Upload Errors');
    $retval .= $upload->printErrors(false);
    $retval .= COM_endBlock();
    $retval .= COM_siteFooter('true');
    echo $retval;
} else {
    $retval = COM_refresh("{$_CONF['site_admin_url']}/plugins/quiz/questions.php?quizid=$quizid");
}
$filename = $HTTP_POST_FILES['image']['name'];
if(!empty($filename) AND file_exists($questionDir.$filename)) {
    $filetype = str_replace("image/", "",$HTTP_POST_FILES['image']['type']);
    if (DB_count($_TABLES['quiz_images'],'qid',$qid) > 0) {
        DB_query("UPDATE {$_TABLES['quiz_images']} SET filename='{$filename}' WHERE qid={$qid}");
    } else {
        DB_query("INSERT INTO {$_TABLES['quiz_images']} (qid,filename) VALUES ('$qid', '$filename')");
    }
    $imginfo = getimagesize($questionDir.$filename);
    $pos = strrpos($filename,'.');
    $filenameonly = substr($filename, 0,$pos);
    $src = $questionDir.$filename;
    $dest = $questionDir.$filenameonly.".jpg";

    // handle image according to type
    switch ($imginfo[2]) {
        case 1: // gif
            // convert gif to jpg using shell command
            $command = sprintf($_CONFQUIZ['gifconvertcmd'] ,$src,$dest);
            //COM_errorLOG("execute $command");
            $test = exec($command);
            // remove original gif file and rename converted png
            unlink($src);
            $chmod = @chmod ($dest,0755);
            $newfile = $filenameonly . ".jpg";
            DB_query("UPDATE {$_TABLES['quiz_images']} SET filename='$newfile' WHERE qid=$qid");
            $filename = $newfile;
            break;

        case 6: // bmp
            // convert bmp to jpg using shell command
            $command = sprintf($_CONFQUIZ['bmpconvertcmd'] ,$src,$dest);
            exec($command);
            // remove original gif file and rename converted png
            unlink($src);
            $chmod = @chmod ($dest,0755);
            $newfile = $filenameonly . ".jpg";
            DB_query("UPDATE {$_TABLES['quiz_images']} SET filename='$newfile' WHERE qid=$qid");
            $filename = $newfile;
            break;

        default:
        break;
    }

    makethumbnail($filename);
    $imageinfo = getimagesize($questionDir.$filename);
    if ($imageinfo['0'] > $_CONFQUIZ['image_width'] ) {
        //COM_errorLOG("Resize $questionDir$filename");
        resize_image($questionDir.$filename,  $_CONFQUIZ['image_quality'], $_CONFQUIZ['image_width'], $resize_type = 1);
    }

}

function makethumbnail($image_name) {
    global $_CONF, $_CONFQUIZ, $questionDir;
    $src = $questionDir.$image_name;
    $dest = $src."_tmp";
    $do_create = false;
    if ($image_info = @getimagesize($src)) {
        if ($image_info[2] == 1 || $image_info[2] == 2 || $image_info[2] == 3) {
            $do_create = true;
        }
    }
    if ($do_create) {
        $dimension = (intval($_CONFQUIZ['auto_thumbnail_dimension'])) ? intval($_CONFQUIZ['auto_thumbnail_dimension']) : 100;
        $resize_type = (intval($_CONFQUIZ['auto_thumbnail_resize_type'])) ? intval($_CONFQUIZ['auto_thumbnail_resize_type']) : 1;
        $quality = (intval($_CONFQUIZ['auto_thumbnail_quality']) && intval($_CONFQUIZ['auto_thumbnail_quality']) <= 100) ? intval($_CONFQUIZ['auto_thumbnail_quality']) : 100;
        if (create_thumbnail($src, $dest, $quality, $dimension, $resize_type)) {
            $chmod = @chmod ($dest,0755);
            unlink($src);
            rename($dest, $src);
        }
    }
}

function resize_image($file, $quality, $dimension, $resize_type = 1) {
    global $_CONFQUIZ;
    $image_info = (defined("IN_CP")) ? getimagesize($file) : @getimagesize($file);
    if (!$image_info) {
        return false;
    }
    $file_bak = $file.".bak";
    if (!rename($file, $file_bak)) {
        return false;
    }
    $width_height = get_width_height($dimension, $image_info[0], $image_info[1], $resize_type);
    $resize_handle = "resize_image_".$_CONFQUIZ['convert_tool'];
    if ($resize_handle($file_bak, $file, $quality, $width_height['width'], $width_height['height'], $image_info)) {
        @chmod($file, 0755);
        @unlink($file_bak);
        $chmod = @chmod ($file,0755);
        return true;
    } else {
        rename($file_bak, $file);
        return false;
    }
}

function get_width_height($dimension, $width, $height, $resize_type = 1) {
    if ($resize_type == 2) {
        $new_width = $dimension;
        $new_height = floor(($dimension/$width) * $height);
    } elseif ($resize_type == 3) {
        $new_width = floor(($dimension/$height) * $width);
        $new_height = $dimension;
    } else {
        $ratio = $width / $height;
        if ($ratio > 1) {
            $new_width = $dimension;
            $new_height = floor(($dimension/$width) * $height);
        } else {
            $new_width = floor(($dimension/$height) * $width);
            $new_height = $dimension;
        }
    }
    return array("width" => $new_width, "height" => $new_height);
}


function create_thumbnail($src, $dest, $quality, $dimension, $resize_type) {
    global $_CONFQUIZ;
    if (file_exists($dest)) {
        @unlink($dest);
    }
    $image_info = (defined("IN_CP")) ? getimagesize($src) : @getimagesize($src);
    if (!$image_info) {
        return false;
    }
    $width_height = get_width_height($dimension, $image_info[0], $image_info[1], $resize_type);
    $resize_handle = "resize_image_".$_CONFQUIZ['convert_tool'];
    if ($resize_handle($src, $dest, $quality, $width_height['width'], $width_height['height'], $image_info)) {
        @chmod($dest, 0755);
        return true;
    } else {
        return false;
    }
}

function resize_image_gd($src, $dest, $quality, $width, $height, $image_info) {
    $types = array(1 => "gif", 2 => "jpeg", 3 => "png");
    if ($_CONFQUIZ['gd_type'] = "GD2") {
        $thumb = imagecreatetruecolor($width, $height);
    } else {
        $thumb = imagecreate($width, $height);
    }
    $image_create_handle = "imagecreatefrom".$types[$image_info[2]];
    if ($image = $image_create_handle($src)) {
        if ($_CONFQUIZ['gd_type'] = "GD2") {
            imagecopyresampled($thumb, $image, 0, 0, 0, 0, $width, $height, ImageSX($image), ImageSY($image));
        } else {
            imagecopyresized($thumb, $image, 0, 0, 0, 0, $width, $height, ImageSX($image), ImageSY($image));
        }
        $image_handle = "image".$types[$image_info[2]];
        $image_handle($thumb, $dest, $quality);
        imagedestroy($image);
        imagedestroy($thumb);
    }
    return (file_exists($dest)) ? 1 : 0;
}

?>