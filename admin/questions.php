<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +-------------------------------------------------------------------------+
// | Quiz Plugin 1.0 for Geeklog- The Ultimate OSS Portal                    |
// | Date: July 25, 2003                                                     |
// +-------------------------------------------------------------------------+
// | Questions.php - Admin code                                              |
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

require_once("../../../lib-common.php"); // Path to your lib-common.php
require_once($_CONF['path'] . 'plugins/quiz/debug.php');  // Common Debug Code

/* Filter incoming variables and set them as globals */
$myvars = array('quizid','qid','op','show','page');
qz_GetData($myvars,true);

if (isset($qid) AND $qid >= 1) {
   $curQid = $qid;
}

$navbarMenu = array(
    'Quiz Listing'      => $_CONF['site_url'] .'/quiz/index.php',
    'Quiz Admin'        => $_CONF['site_admin_url'] .'/plugins/quiz/index.php',
    'New Question'      => $_CONF['site_admin_url'] .'/plugins/quiz/questions.php?op=newquestion&quizid='.$quizid,
    'Refresh'           => $_CONF['site_admin_url'] .'/plugins/quiz/questions.php?&quizid='.$quizid
);


function qz_updateAnsOrder($quizid) {
    global $_TABLES;
    $query = DB_query("SELECT id,aorder FROM {$_TABLES['quiz_answers']} WHERE qid={$quizid} ORDER by aorder asc");
    $order = 0;
    while (list($id,$aorder) = DB_fetchArray($query)) {
       $order++;
       DB_query("UPDATE {$_TABLES['quiz_answers']} SET aorder='$order' WHERE id={$id}");
   }
}

function qz_updateQuestionOrder($quizid) {
    global $_TABLES;

    $query = DB_query("SELECT qid,qorder FROM {$_TABLES['quiz_questions']} WHERE quizid={$quizid} ORDER by qorder asc");
    $order = 0;
    while (list($id,$qorder) = DB_fetchArray($query)) {
       $order++;
       DB_query("UPDATE {$_TABLES['quiz_questions']} SET qorder='$order' WHERE qid={$id}");
   }
}

// Handling of submit code

switch ($op) {

case 'savequestion':

    $HTTP_POST_VARS = qz_cleandata($HTTP_POST_VARS);
    $question = $HTTP_POST_VARS['question'];
    $qanswer = $HTTP_POST_VARS['qanswer'];
    $qvalue = $HTTP_POST_VARS['qvalue'];
    $qorder = ($HTTP_POST_VARS['qorder'] == "") ? "99" : $HTTP_POST_VARS['qorder'];

    if(!empty($question) AND !empty($qvalue)) {
       DB_query("INSERT INTO {$_TABLES['quiz_questions']} (quizid,question,qanswer,qvalue,qorder) VALUES ('$quizid', '$question', '$qanswer', '$qvalue', '$qorder')");
        $qid = DB_insertID();
        qz_updateQuestionOrder($quizid);
        $questionDir = $_CONF['path_html'] . "quiz/question_images/$qid/";
        if (isset($HTTP_POST_FILES['image'])) {
            include ('addimage.php');
        }
    } else {
        echo "Please complete all fields<br>";
    }
    break;

case 'savemultiquestions':

    $HTTP_POST_VARS = qz_cleandata($HTTP_POST_VARS);
    $question = $HTTP_POST_VARS['question'];
    $qanswer = $HTTP_POST_VARS['qanswer'];
    $qvalue = $HTTP_POST_VARS['qvalue'];
    $qorder = ($HTTP_POST_VARS['qorder'] == "") ? "99" : $HTTP_POST_VARS['qorder'];

    $i = 0;
    foreach ($question as $newquestion) {
        if(!empty($newquestion)) {
            DB_query("INSERT INTO {$_TABLES['quiz_questions']} (quizid,question,qanswer,qvalue,qorder) VALUES ('$quizid', '$newquestion', '[$qanswer[$i]}', '${qvalue[$i]}', '{$qorder[$i]}')");
        }
        $i++;
    }
    qz_updateQuestionOrder($quizid);
    break;

case 'updatequestion':

    $HTTP_POST_VARS = qz_cleandata($HTTP_POST_VARS);
    $question = $HTTP_POST_VARS['question'];
    $qanswer = $HTTP_POST_VARS['qanswer'];
    $qvalue = $HTTP_POST_VARS['qvalue'];
    $qorder = $HTTP_POST_VARS['qorder'];
    $qid = $HTTP_POST_VARS['qid'];
    $questionDir = $_CONF['path_html'] . "quiz/question_images/$qid/";

    if ($HTTP_POST_VARS['delete'] == "Del") {
        DB_query("DELETE FROM {$_TABLES['quiz_questions']} WHERE qid='{$qid}'");
        DB_query("DELETE FROM {$_TABLES['quiz_answers']} WHERE qid='{$qid}'");
    } else {
        DB_query("UPDATE {$_TABLES['quiz_questions']} SET question='$question', qanswer='$qanswer', qvalue='$qvalue', qorder='$qorder' WHERE qid='{$qid}'");
    }
    qz_updateQuestionOrder($quizid);

    if ($HTTP_POST_VARS['delimage'] == "1") {
        $filename = DB_getItem($_TABLES['quiz_images'],"filename","qid=$qid");
        if (file_exists($questionDir.$filename)) {
            unlink($questionDir.$filename);
        }
        DB_query("DELETE FROM {$_TABLES['quiz_images']} WHERE qid='{$qid}'");
    }
    if (isset($HTTP_POST_FILES['image'])) {
        include ('addimage.php');
    }
    unset($curQid);
    break;

case 'copyquestion':

    $source = DB_query("SELECT question, qvalue FROM {$_TABLES['quiz_questions']} WHERE qid={$qid}");
    list ($squestion, $svalue) = DB_fetchArray($source);
    DB_query("INSERT INTO {$_TABLES['quiz_questions']} (quizid,question,qanswer,qvalue,qorder) 
        VALUES ('$quizid','$squestion','0','$svalue','99')");
    qz_updateQuestionOrder($quizid);
    break;

case 'saveanswer':

    $HTTP_POST_VARS = qz_cleandata($HTTP_POST_VARS);
    $answer = $HTTP_POST_VARS['answer'];    
    $aorder = ($HTTP_POST_VARS['aorder'] == "") ? "99" : $HTTP_POST_VARS['aorder'];

    if(!empty($answer)) {
        DB_query("INSERT INTO {$_TABLES['quiz_answers']} (qid,answer,aorder) VALUES ('$qid', '$answer', '$aorder')");
        qz_updateAnsOrder($qid);
        if (isset($HTTP_POST_VARS['correct'])) {
            $id = DB_getItem($_TABLES['quiz_answers'],"id","answer='{$answer}'");
            echo "<br>UPDATE {$_TABLES['quiz_questions']} SET  qanswer='$id' WHERE qid='{$qid}'";
            DB_query("UPDATE {$_TABLES['quiz_questions']} SET  qanswer='$id' WHERE qid='{$qid}'");
        }

    } else {
        echo "Please complete all fields<br>";
    }
    break;

case 'savemultianswers':

    $HTTP_POST_VARS = qz_cleandata($HTTP_POST_VARS);
    $answer = $HTTP_POST_VARS['answer'];    
    $aorder = ($HTTP_POST_VARS['aorder'] == "") ? "99" : $HTTP_POST_VARS['aorder'];

    $i = 0;
    foreach ($answer as $newanswer) {
        if(!empty($newanswer)) {
            DB_query("INSERT INTO {$_TABLES['quiz_answers']} (qid,answer,aorder) VALUES ('$qid', '$newanswer', '{$aorder[$i]}')");
        }
        $i++;
    }
    qz_updateAnsOrder($qid);
    break;

case 'updateanswer':

    $HTTP_POST_VARS = qz_cleandata($HTTP_POST_VARS);
    $id = $HTTP_POST_VARS['id'];    
    $answer = $HTTP_POST_VARS['answer'];    
    $aorder = $HTTP_POST_VARS['aorder'];

    if ($HTTP_POST_VARS['delete'] == "Del") {
        DB_query("DELETE FROM {$_TABLES['quiz_answers']} WHERE id='{$HTTP_POST_VARS['id']}'");
    } else {
        DB_query("UPDATE {$_TABLES['quiz_answers']} SET answer='$answer', aorder='$aorder' WHERE id=$id");
        if (isset($HTTP_POST_VARS['correct'])) {
            DB_query("UPDATE {$_TABLES['quiz_questions']} SET  qanswer='$id' WHERE qid=$qid");
        } elseif (DB_getItem($_TABLES['quiz_questions'],"qanswer","qid=$qid") == $id) {
            DB_query("UPDATE {$_TABLES['quiz_questions']} SET  qanswer='0' WHERE qid=$qid");
        }
    }
    qz_updateAnsOrder($qid);
    break;

case 'copyanswer':

    $id = $HTTP_GET_VARS['id'];
    $source = DB_query("SELECT answer FROM {$_TABLES['quiz_answers']} WHERE id={$id}");
    list ($sanswer) = DB_fetchArray($source);
    DB_query("INSERT INTO {$_TABLES['quiz_answers']} (qid,answer,aorder) 
        VALUES ('$qid','$sanswer','99')");
    qz_updateAnsOrder($qid);
    break;

}

// Check if the page navigation is being used
if (empty($show)) {
    $show = 10;
}
if (empty($page)) {
    $page = 1;
}


// Main Code

$quizname = DB_getItem($_TABLES['quiz_master'],"name","quizid=$quizid");
echo COM_siteHeader();
echo '<script language="javascript">
    <!-- Begin
    function confirmSubmit(text) { 
      var yes = confirm(text); 
      if (yes) return true; 
      else return false; 
    } 

    //  End -->
    </script>';
echo COM_startBlock("ProQuiz Admin");
echo ppNavbar($navbarMenu);



$query = DB_query("SELECT count(*) FROM {$_TABLES['quiz_questions']} WHERE quizid=$quizid");
$nrows = DB_fetchArray($query);
$numpages = ceil($nrows['0'] / $show);
$offset = ($page - 1) * $show;
$base_url = $_CONF['site_admin_url'] . '/plugins/quiz/questions.php?quizid=' .$quizid. '&qid=' .$qid. '&show=' .$show.  '&page=' .$page; 
$imgset = $_CONF['site_url'] . '/quiz/images/';

echo '<table border="0" cellpadding="3" cellspacing="1" width="99%" ID="plg_table">
        <tr>
            <th id="plg_heading" colspan="5" width="100%" align="left" valign="top">'.$quizname.'</td>
        </tr>
        <tr>
            <td><label>Order</label></td>
            <td><label>Question</label></td>
            <td><label>Value</label></td>
            <td><label>Answer</label></td>
            <td><label>Actions</label></td>
       </tr>';

if ($op == 'newquestion') {
    echo '<td colspan="5" style="padding:5px 2px 5px 5px;"><table border="0" cellpadding="1" cellspacing="1" width="100%">';
    if ($HTTP_POST_VARS['imagemode'] == 'on') {
        echo '<tr><form name="frm_add" action="'.$PHP_SELF.'" method="post" enctype="multipart/form-data">';
    } else {
        echo '<tr><form name="frm_add" action="'.$PHP_SELF.'" method="post">';
    }
    echo '<input type="hidden" name="imagemode" value="">';
    echo '<input type="hidden" name="op" value="savequestion">';
    echo '<input type="hidden" name="quizid" value="'.$quizid.'">';
    echo '<td align="center"><input type="text" name="qorder" size="3" value="'.$HTTP_POST_VARS['qorder'].'"></td>
        <td><input type="text" name="question" size="55" value="'.$HTTP_POST_VARS['question'].'"></td>
        <td><input type="text" name="qvalue"  size="3" value="'.$HTTP_POST_VARS['qvalue'].'"></td>
        <td><input type="text" name="qanswer"  size="3"value="'.$HTTP_POST_VARS['qanswer'].'"></td>
        <td width="20%" style="vertical-align:middle; text-align:center; padding:5px;">';
    if ($HTTP_POST_VARS['imagemode'] != 'on') {
        echo '<a href="' .$PHP_SELF. '?op=newquestion&image=on&quizid='.$quizid.'&qid=' .$qid. '&show=' .$show. '&page=' .$page. '" ><img src="'.$imgset.'photo.gif" border="0" TITLE="Add Question Photo" onClick="document.frm_add.imagemode.value=\'on\';document.frm_add.op.value=\'newquestion\';document.frm_add.submit();return false;"></a>&nbsp;';
    }
    echo '<input type="submit" value="Submit"></td>
            </tr>';
    if ($HTTP_POST_VARS['imagemode'] == 'on') {
        echo '<tr><td>&nbsp;</td><td colspan="4"><input type="file" name="image" size="40"></td></tr>';
    }
    echo '</form></table></td></tr>';
}


if ($nrows[0] > 0) {


    $query = DB_query("SELECT question.qid,qorder,question,qvalue,aorder FROM {$_TABLES['quiz_questions']} question LEFT JOIN {$_TABLES['quiz_answers']} answer on question.qanswer=answer.id WHERE quizid=$quizid ORDER BY qorder LIMIT $offset, $show"); 

    while ( list($qid,$qorder,$question,$qvalue,$qanswer) = DB_fetchARRAY($query)) {

        if (!$qanswer) {   // Check if a null was returned - no answer defined for question
            $qanswer = "0";
        }
        if (DB_count($_TABLES['quiz_images'], "qid", $qid) != 0) {
            $imageonfile = true;
        } else {
            $imageonfile = false;
        }

        if (($op == "editquestion" OR $op == "questionimage") AND $HTTP_GET_VARS['qid'] == $qid ) {
            echo '<tr><td colspan="5" style="padding:5px 2px 5px 5px;"><table border="0" cellpadding="1" cellspacing="1" width="100%">';
            echo '<tr><form action="'.$PHP_SELF.'" method="post" enctype="multipart/form-data" style="margin:0px;padding:0px;">
                <td align="center"><input type="text" name="qorder" size="3" value="'.$qorder.'"></td>
                <td><input type="text" name="question" size="55" value="'.$question.'"></td>
                <td><input type="text" name="qvalue"  size="3" value="'.$qvalue.'"></td>
                <td><input type="text" name="qanswer"  size="3" value="'.$qanswer.'"></td>
                <td width="15%" style="vertical-align:top; text-align:center;padding:2px;" nowrap>';
            if ($op != "questionimage" and !$imageonfile) {
               echo '<a href="' .$PHP_SELF. '?op=questionimage&quizid='.$quizid.'&qid=' .$qid. '&show=' .$show. '&page=' .$page. '"><img src="'.$imgset.'photo.gif" border="0" TITLE="Add Question Photo"></a>&nbsp;';
            }
             echo '<input type="submit" value="Update">&nbsp;<input type="submit" name="delete" value="Del"><input type="hidden" name="op" value="updatequestion"><input type="hidden" name="quizid" value="'.$quizid.'"><input type="hidden" name="qid" value="'.$qid.'"></td>
                </tr>';

            // Check if there is already an image on record for this question
            if ($imageonfile) {
                $iquery = DB_query("SELECT * FROM {$_TABLES['quiz_images']} WHERE qid=$qid");
                list ($id, $qid,$filename,$title) = DB_fetchArray($iquery);
                $questionimage = $_CONF['site_url'] . "/quiz/question_images/$qid/$filename";
                echo '<tr><td>&nbsp;</td><td><img src="'.$questionimage.'"></td>
                <td colspan="3">&nbsp;<input type="checkbox" name="delimage" value="1">&nbsp;Delete Image</td></tr>';
            } elseif ($op == "questionimage") {
                echo '<tr><td>&nbsp;</td>
                <td><input type="file" name="image" size="40"></td>
                <td colspan="4" style="padding-left:5px;"></td>
                    </tr>';
            }
            echo '</form></table></td></tr>';

        } else {

            echo '<tr valign="top">
                <td align="center">' .$qorder.'</td>
                <td style="padding-left:5px;">' .$question;
            if ( ($imageonfile and $op == "viewimage$qid") OR ($imageonfile AND $op == 'answers' AND $qid == $curQid) ) {
                $iquery = DB_query("SELECT * FROM {$_TABLES['quiz_images']} WHERE qid=$qid");
                list ($id, $qid,$filename,$title) = DB_fetchArray($iquery);
                $questionimage = $_CONF['site_url'] . "/quiz/question_images/$qid/$filename";
                echo '<div style="padding:5px;"><img src="'.$questionimage.'"></div>';
            }
            echo '</td>
                <td style="padding-left:5px;">' .$qvalue.'</td>
                <td style="padding-left:5px;">' .$qanswer.'</td>
                <td width="15%" style="text-align:center; padding-left:5px;padding-right:5px;" nowrap>';
            if ($imageonfile) {
                if ( ($op == "viewimage$qid")OR ($imageonfile AND $op == 'answers' AND $qid == $curQid) ) {
                    echo '<a href="' .$PHP_SELF. '?quizid='.$quizid.'&qid=' .$qid. '&show=' .$show. '&page=' .$page. '"><img src="'.$imgset.'photo.gif" border="0" TITLE="Hide Photo for Question"></a>&nbsp;';
                } else {
                   echo '<a href="' .$PHP_SELF. '?op=viewimage'.$qid.'&quizid='.$quizid.'&qid=' .$qid. '&show=' .$show. '&page=' .$page. '"><img src="'.$imgset.'photo.gif" border="0" TITLE="View Photo for Question"></a>&nbsp;';
                }
           } else {
               echo '<span style="padding-left:15px;">&nbsp;</span>';
           }
           echo '<a href="' .$PHP_SELF. '?op=editquestion&quizid='.$quizid.'&qid=' .$qid. '&show=' .$show. '&page=' .$page. '"><img src="'.$imgset.'editquiz.gif" border="0" TITLE="Edit Question"></a>';
           echo '&nbsp;<a href="' .$_CONF['site_admin_url'] .'/plugins/quiz/questions.php?op=copyquestion&quizid='.$quizid.'&qid='.$qid. '&show=' .$show. '&page=' .$page. '"  onclick="return confirm(\'Please confirm that you want to copy this question\');"><img src="'.$imgset.'copy.gif" border="0" TITLE="Copy Question"></a>';
           echo '&nbsp;<a href="' .$_CONF['site_admin_url'] .'/plugins/quiz/questions.php?op=answers&quizid='.$quizid.'&qid='.$qid. '&show=' .$show. '&page=' .$page. '"><img src="'.$imgset.'answers.gif" border="0" TITLE="Question Answers"></a>';
            echo '</td></tr>';
        }
    }

    $qsql = DB_query("SELECT SUM(qvalue) as sum FROM {$_TABLES['quiz_questions']} WHERE quizid=$quizid");
    $qsum = DB_fetchArray($qsql);
    $qtotal = DB_getItem($_TABLES['quiz_master'],"total_score","quizid=$quizid");
    echo '<tr style="line-height:24pt; vertical-align:middle;">
        <td colspan="2" style="padding:5px; text-align:left;"><b>Current Quiz Value:</b>&nbsp;'.$qsum['sum'].'</td>
        <td colspan="3" style="padding:5px; text-align:left;"><b>Defined Total Score:</b>&nbsp;'.$qtotal.'</td>
        </tr>';
    echo '</table><br>';
    echo COM_printPageNavigation($base_url,$page, $numpages);

    // Answers
    if (isset($curQid)) {

        $qid = $curQid;
        $correctAnswer= DB_getItem($_TABLES['quiz_questions'],"qanswer", "qid=$qid");
        $question = DB_getItem($_TABLES['quiz_questions'],"question", "qid=$qid");
        $query = DB_query("SELECT id,answer,aorder FROM {$_TABLES['quiz_answers']} WHERE qid=$qid ORDER BY aorder");
        $numanswers = DB_numRows($query);

        if ($numanswers > 0 ) {

            echo '<table border="0" cellpadding="1" cellspacing="1" width="99%">
                <tr><form action="'.$PHP_SELF.'" method="post">
                    <td colspan="4" width="100%" align=left valign="top"><br>' . $helpmsg . '</td>
                    <td align=right valign="top"><br><input type="submit" name="submit" value="New Answer"><input type="hidden" name="op" value="newanswer"><input type="hidden" name="qid" value="'.$qid.'"><input type="hidden" name="quizid" value="'.$quizid.'"><input type="hidden" name="show" value="'.$show.'"><input type="hidden" name="page" value="'.$page.'">&nbsp;<br><br></td>
                </tr></form>
              </table>';

            echo '<table border="0" cellpadding="3" cellspacing="1" width="99%" id="plg_table">
                    <tr>
                        <th id="plg_heading" colspan="5" width="100%" align=left valign="top"><font size=2><b>Answers for:&nbsp;'.$question.'</b></font></td>
                    </tr>
                    <tr>
                        <td width="20"><label style="padding-left:10px;">#</label></td>
                        <td width="70%"><label>Answer</label></td>
                        <td><label>Correct</label></td>
                        <td><label>Actions</label></td>
                   </tr>';

            if ($HTTP_POST_VARS['op'] == "newanswer") {
                echo '<tr><form action="'.$PHP_SELF.'" method="post">
                    <td align="center"><input type="text" name="aorder" size="3"></td>
                    <td><input type="text" name="answer" size="55"></td>
                    <td align="center"><input type="radio" name="correct" value="1"></td>
                    <td valign="middle" width="20%" style="text-align:center;padding-left:5px;padding-right:5px;"><input type="submit" value="Submit"><input type="hidden" name="op" value="saveanswer"><input type="hidden" name="qid" value="'.$qid.'"><input type="hidden" name="quizid" value="'.$quizid.'"><input type="hidden" name="show" value="'.$show.'"><input type="hidden" name="page" value="'.$page.'"></td>
                    </tr></form>';
            }

            while ( list($id,$answer,$aorder) = DB_fetchARRAY($query)) {

                if ($HTTP_GET_VARS['op'] == "editanswer" AND $HTTP_GET_VARS['id'] == $id ) {
                    if ($id == $correctAnswer) {
                        $checked = " CHECKED ";
                    }

                    echo '<tr><form action="'.$PHP_SELF.'" method="post">
                            <td align="center">
                                <input type="text" name="aorder" size="3" value="'.$aorder.'">
                            </td>
                            <td>
                                <input type="text" name="answer" size="55" value="'.$answer.'">
                            </td>
                            <td width="5%" align="center">
                                <input type="checkbox" name="correct" '.$checked.' size="3" value="Correct">
                            </td>
                            <td valign="middle" width="15%"><input type="submit" value="Submit">&nbsp;
                                <input type="submit" name="delete" value="Del">
                                <input type="hidden" name="op" value="updateanswer">
                                <input type="hidden" name="quizid" value="'.$quizid.'">
                                <input type="hidden" name="qid" value="'.$qid.'">
                                <input type="hidden" name="id" value="'.$id.'">
                                <input type="hidden" name="show" value="'.$show.'">
                                <input type="hidden" name="page" value="'.$page.'">
                            </td>
                        </tr></form>';
                } else {
                    echo '<tr>
                    <td width="20" align="center">' .$aorder.'</td>
                    <td>' .$answer.'</td>';
                    if ($id == $correctAnswer) {
                        echo '<td width="5%" align="center">Yes</td>';
                     } else {
                        echo '<td width="5%" align="center">No</td>';
                     }
                     echo '<td width="15%" style="text-align:center;padding-left:5px;padding-right:5px;">';
                     echo '<a href="' .$PHP_SELF. '?op=editanswer&id='.$id.'&quizid='.$quizid.'&qid=' .$qid. '&show=' .$show. '&page=' .$page. '"><img src="'.$imgset.'editquiz.gif" border="0" TITLE="Edit Answer"></a>';
                     echo '&nbsp;<a href="' .$_CONF['site_admin_url'] .'/plugins/quiz/questions.php?op=copyanswer&id='.$id.'&quizid='.$quizid.'&qid='.$qid. '&show=' .$show. '&page=' .$page. '"  onclick="return confirm(\'Please confirm that you want to copy this answer\');"><img src="'.$imgset.'copy.gif" border="0" TITLE="Copy Answer"></a>';
                     echo '</td></tr>';
                }
            }
            echo '</form></table><br><p>';

        } else {

            echo '<table border="1" cellpadding="1" cellspacing="1" width="99%">
                    <tr>
                        <td bgcolor=#BBBECE colspan="5" width="100%" align=left valign="top"><font size=2><b>Answers for:&nbsp;'.$question.'</b></font></td>
                    </tr>
                    <tr bgcolor="ECE9D8" align="center">
                        <td>#</td>
                        <td>Answer</td>
                   </tr>';
            
            for ($i = 1; $i <= $_CONFQUIZ ['numanswers']; $i++) {
                echo '<tr><form action="'.$PHP_SELF.'" method="post">
                    <td align="center"><input type="text" name="aorder[]" size="3"></td>
                    <td align="center"><input type="text" name="answer[]" size="85"></td>
                    </tr>';
            }

            echo '<tr>
                    <td colspan="5" align="center" valign="middle" width="20%">
                        <input type="submit" value="Submit">
                        <input type="hidden" name="op" value="savemultianswers">
                        <input type="hidden" name="quizid" value="'.$quizid.'">
                        <input type="hidden" name="qid" value="'.$qid.'">
                        <input type="hidden" name="id" value="'.$id.'">
                        <input type="hidden" name="show" value="'.$show.'">
                        <input type="hidden" name="page" value="'.$page.'">
                    </td>
                 </tr>
                </form></table><br><p>';

        }
    } else {
        echo '<br><p />';
    }


} else {
    echo '</tr></form></table><br><p>';
}

echo COM_endBlock();
echo '<p>';
echo COM_siteFooter();

?>