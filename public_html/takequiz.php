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

/* Filter incoming variables and set them as globals */
$myvars = array('quizid','op');
qz_GetData($myvars,true);


if (SEC_hasRights('quiz.edit')) {
    $navbarMenu = array(
        'Quiz Admin'      => $_CONF['site_admin_url'] .'/plugins/quiz/index.php',
        'Quiz Listing'    => $_CONF['site_url'] .'/quiz/index.php',
        'Reload Quiz'     => $_CONF['site_url'] .'/quiz/takequiz.php?quizid='.$quizid
    );
} else {
    $navbarMenu = array(
        'Quiz Listing'         => $_CONF['site_url'] .'/quiz/index.php',
        'Reload Quiz'     => $_CONF['site_url'] .'/quiz/takequiz.php?quizid='.$quizid
    );
}


if ($op == "scorequiz") {

    $answersCnt = 0;
    $questionsCnt = 0;
    $attemptedQuestionsCnt = 0;
    $correctCnt = 0;
    $score = 0;

    // Determine how long user took to complete test form - rounded up to nearest min
    $testtime = round( (time() - $HTTP_POST_VARS['start'] )/ 60 );

    $pass = DB_getItem($_TABLES['quiz_master'],"pass_score", "quizid=$quizid");
    $quizname = DB_getItem($_TABLES['quiz_master'],"name", "quizid=$quizid");
    if(!empty($_USER['uid']) AND $_USER['uid'] > 1 ) {
        $uid = $_USER['uid'];
        $username = ucfirst(DB_getItem($_TABLES['users'],"username", "uid=$uid"));
    }

    $query = DB_query("SELECT qid,qanswer,qvalue,qorder FROM {$_TABLES['quiz_questions']} WHERE quizid=$quizid ORDER BY qorder");
    while ( list($qid,$qanswer,$qvalue,$qorder) = DB_fetchARRAY($query)) {
        //echo "<br>qid: $qid, qanswer: $qanswer";
        $questionCnt++;
        $studentAnswer = $HTTP_POST_VARS["question$qid"];
        if (isset($studentAnswer) AND ($studentAnswer != "")) {
            $attemptedQuestionCnt++;
        }
        $correctans = DB_getItem($_TABLES['quiz_answers'],"id","qid=$qid AND id=$qanswer");
        //echo "<br>Question $questionCnt, Correct Answer is: $correctans and Attempted Answer is: $studentAnswer";
        if ($questionCnt == 1) {
            $questions = $qid;
            $answers = $studentAnswer;
        } else {
            $questions = $questions . "," . $qid;
            $answers = $answers . "," . $studentAnswer;
        }
        if ($studentAnswer == $correctans) {
            $correctCnt++;
            $score = $score + $qvalue;
        }

    }
    echo COM_siteHeader();
    echo COM_startBlock("Quiz Results for:&nbsp;$quizname");
    if ($attemptedQuestionCnt > 0 ) {
        $date = time();
        DB_query("INSERT INTO {$_TABLES['quiz_results']} (quizid,uid,score,date,test_durmin,questions,answers) VALUES ('$quizid', '$uid', '$score', '$date', '$testtime', '$questions', '$answers')");
        echo "<br><p>$username, you correctly answered $correctCnt out of $questionCnt questions.";
        if ($pass > $score) {
            echo "<br>Your score was $score which is a failed mark. A score of $pass is required.";
        } else {
            echo "<br>You have sucessfully passed. Your score was $score.<br><br>";
        }
        if ($attemptedQuestionCnt != $questionCnt) {
            echo "<br><p>You only answered $attemptedQuestionCnt out of a total of $questionCnt questions on the quiz<br><br>";
        }
    } else {
            echo "<br><p>$attemptedQuestionCnt, You did not answer any questions. Your results have not been recorded<br>";
    }
    echo '<br><a href="'.$_CONF['site_url'] .'/quiz/index.php">Quiz Listings</a><p />';
    echo COM_endBlock();
    echo COM_siteFooter();

} else {

    // Show the quiz questions

    // Start timer
    $starttime = time();

    $quizname = DB_getItem($_TABLES['quiz_master'],"name","quizid=$quizid");
    echo COM_siteHeader();
    echo COM_startBlock("Questions for: $quizname");
    echo ppNavbar($navbarMenu);

    $query = DB_query("SELECT count(*) FROM {$_TABLES['quiz_questions']} WHERE quizid=$quizid");
    $nrows = DB_fetchArray($query);

    if ($nrows[0] > 0) {
        echo '<hr><table width="100%" border="0" cellspacing="0" cellpadding="0">';
        echo '<tr><td width="100%"><form name="quizresults" method="post" action="'.$PHP_SELF.'" onSubmit="document.quizresults.op.value=\'scorequiz\'; return true;">';
        echo '<input type="hidden" name="op" value="">';
        $query = DB_query("SELECT question_order, question_type, num_of_questions FROM {$_TABLES['quiz_master']} WHERE quizid = {$quizid}");
        list ($question_order, $question_type, $num_of_questions) = DB_fetchArray($query);
        if ( $question_type == '1') {            // Random Questions from Pool
            for ( $i = 0; $i < 10; $i++ )
                $randnum .= rand( 0, 9 );
            $order = "ORDER BY RAND($randnum) LIMIT $num_of_questions ";
        } elseif ( $question_order == '0') {    // Random Questions - all questions defined for quiz
            for ( $i = 0; $i < 10; $i++ )
                $randnum .= rand( 0, 9 );
            $order = "ORDER BY RAND($randnum)";
        } else {                                // Fixed order - all questions defined for quiz
            $order = "ORDER BY qorder";
        }

        $questionQuery = DB_query("SELECT qid,qorder,question,qvalue,qanswer FROM {$_TABLES['quiz_questions']} WHERE quizid=$quizid $order"); 

        $questioncnt = 1;
        while ( list($qid,$qorder,$question,$qvalue,$qanswer) = DB_fetchARRAY($questionQuery)) {
            if (DB_count($_TABLES['quiz_images'], "qid", $qid) != 0) {
                $imageonfile = true;
            } else {
                $imageonfile = false;
            }
            echo '<table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr valign="top"> 
                    <td colspan="4"align="left">'.$questioncnt.') '.$question;
            echo '</td></tr><tr>';
            if ($imageonfile) {
                $iquery = DB_query("SELECT * FROM {$_TABLES['quiz_images']} WHERE qid=$qid");
                list ($id, $qid,$filename,$title) = DB_fetchArray($iquery);
                $questionimage = $_CONF['site_url'] . "/quiz/question_images/$qid/$filename";
                echo '<td valign="top" style="padding:5px;"><img src="'.$questionimage.'"></td>';
            } else {
                echo '<td valign="top">&nbsp;</td>';
            }
            echo '<td width="63%"><table width="100%" border="0">';

            $answerQuery = DB_query("SELECT id,answer,aorder FROM {$_TABLES['quiz_answers']} WHERE qid=$qid ORDER BY aorder"); 
            $answercnt = 1;
            while ( list($id,$answer,$aorder) = DB_fetchARRAY($answerQuery)) {
                    echo '<tr><td>&nbsp;' .$answercnt. ')&nbsp;<label for="question'.$qid.'ans'.$answercnt.'">';
                    echo '<input type="radio" name="question'.$qid.'" id="question'.$qid.'ans'.$answercnt.'" value="'.$id.'">';
                    echo '&nbsp;'.$answer.'</label></td></tr>';
                    $answercnt ++;
            }
            echo '<tr><td colspan="2"><hr></td></tr>';
            echo '</table></td></tr></table><br>';
            $questioncnt ++;
        }
        echo '</td></tr><tr><td align="center"><input type="submit" name="scorequiz" value="Submit"><input type="hidden" name="start" value="' .$starttime. '"><input type="hidden" name="quizid" value="'.$quizid. '"><br><p /></td></form></tr></table>';

    }
    echo COM_endBlock();
    echo COM_siteFooter();

}

?>