<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +-------------------------------------------------------------------------+
// | Quiz Plugin 1.0 for Geeklog- The Ultimate OSS Portal                    |
// | Date: July 25, 2003                                                     |
// +-------------------------------------------------------------------------+
// | takequiz.php                                                            |
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

require_once("../lib-common.php"); // Path to your lib-common.php
require_once($_CONF['path'] . 'plugins/quiz/debug.php');  // Common Debug Code

if (SEC_hasRights('quiz.edit')) {
    $helpmsg = '<p>The following are the quiz questions and answers. The correct answer will be shown selected with the quiz attempt answers in <font color="green">green if correct</font><font color="black"> or </font><font color="red">red if wrong</font><p />';
} else {
    $helpmsg = '<p>The following are the quiz questions and answers. The quiz attempt answers will be shown in <font color="green">green if correct</font><font color="black"> or </font><font color="red">red if wrong</font><p />';
}

// Check if Quiz ID or Question ID are set - need to check both GET and POST
if (isset($HTTP_GET_VARS['id']) AND $HTTP_GET_VARS['id'] != "" ) {
    $id = $HTTP_GET_VARS['id'];

    if (DB_getItem($_TABLES['quiz_results'],"uid","id=$id") == $_USER['uid']  OR SEC_hasRights('quiz.edit') ) {

        echo COM_siteHeader();
        echo COM_startBlock("Quiz Results");
        // Show the quiz results for this user
        $quizid = DB_getItem($_TABLES['quiz_results'],"quizid","id=$id");
        $query = DB_query("SELECT name, total_score, pass_score FROM {$_TABLES['quiz_master']} WHERE quizid=$quizid");
        list ($quizname, $total_score,$pass_score) = DB_fetchArray($query);

        $query = DB_query("SELECT questions,answers,score FROM {$_TABLES['quiz_results']} WHERE id=$id");
        $nrows = DB_numRows($query);

        if ($nrows > 0) {
            if ($HTTP_GET_VARS['admin'] AND SEC_hasRights('quiz.edit')) {
                $navbarMenu = array(
                    'Quiz Listing'      => $_CONF['site_url'] .'/quiz/index.php',
                    'Quiz Admin'        => $_CONF['site_admin_url'] .'/plugins/quiz/index.php',
                    'Results Listing'   => $_CONF['site_admin_url'] .'/plugins/quiz/index.php?op=results&quizid='.$quizid
                );
            } else {
                $navbarMenu = array(
                    'Quiz Listing'      => $_CONF['site_url'] .'/quiz/index.php',
                    'Results Listing'   => $_CONF['site_url'] .'/quiz/index.php?op=results&quiz='.$quizid,
                );
            }
            echo ppNavbar($navbarMenu);

            list ($questions, $answers, $score) = DB_fetchArray($query);
            echo '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
            echo '<tr><td width="100%" style="padding-left:5px;">Your score for the quiz <b>'. $quizname .'</b> was: ' .$score. '/' .$total_score .', pass score is: '. $pass_score;
            echo $helpmsg . '</td></tr>';
            echo '<tr><td width="100%"><hr></td></tr>';
            echo '<tr><td width="100%">';

            $arrQuest = explode(",",$questions);
            $arrAns = explode(",",$answers);
            $i = 0;

            foreach ($arrQuest as $qid) {
                $quizResultAnsID = $arrAns[$i];
                $i++;
                $query = DB_query("SELECT  question, qanswer, qvalue FROM {$_TABLES['quiz_questions']} WHERE qid=$qid");
                list ($question, $qanswer, $qvalue) = DB_fetchArray($query);
                if (DB_count($_TABLES['quiz_images'], "qid", $qid) != 0) {
                    $imageonfile = true;
                } else {
                    $imageonfile = false;
                }
                echo '<table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr> 
                        <td colspan="4"align="left">'.$i.') '.$question.'<br><span style="padding-left:18px;"><b>Value:&nbsp;</b>'.$qvalue.'</span></td>
                        </tr>
                        <tr>';
                if ($imageonfile) {
                    $iquery = DB_query("SELECT * FROM {$_TABLES['quiz_images']} WHERE qid=$qid");
                    list ($id, $qid,$filename,$title) = DB_fetchArray($iquery);
                    $questionimage = $_CONF['site_url'] . "/quiz/question_images/$qid/$filename";
                    echo '<td valign="top" style="padding:5px;"><img src="'.$questionimage.'"></td>';
                } else {
                    echo '<td valign="top">&nbsp;</td>';
                }
                echo '<td width="63%">
                        <table width="100%">';

                $answerQuery = DB_query("SELECT id,answer,aorder FROM {$_TABLES['quiz_answers']} WHERE qid=$qid ORDER BY aorder"); 
                $answercnt = 1;
                while ( list($id,$answer,$aorder) = DB_fetchARRAY($answerQuery)) {
                    if ($id == $quizResultAnsID) {
                        if ($id == $qanswer) {
                            echo '<tr><td style="color:green;padding-left:5px;">';
                        } else {
                            echo '<tr><td style="color:red;padding-left:5px;">';
                        }
                    } else {
                        echo '<tr><td style="color:black;padding-left:5px;">';
                    }
                    echo $answercnt. ')&nbsp;';
                    echo '<input type="radio"';
                    if ($id == $qanswer AND SEC_hasRights('quiz.edit')) {
                        echo " checked";
                    }
                    echo '>';
                    echo '&nbsp;'.$answer.'</td></tr>';
                    $answercnt ++;
                }
                echo '<tr><td colspan="2"><hr></td></tr>';
                echo '</table></td></tr></table><br>';
            }

            echo '</td></tr><tr><td align="center"></tr></table>';
        }

        echo COM_endBlock();
        echo COM_siteFooter();

    } else {
        echo "<br>Need to add a error message here.. Trying to access result for another user";
    }
} else {
    echo "<br>Need to add a error message here.. Invalid ID was passed";
}

?>