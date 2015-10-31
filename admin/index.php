<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +-------------------------------------------------------------------------+
// | Quiz Plugin 1.0 for Geeklog- The Ultimate OSS Portal                    |
// | Date: July 25, 2003                                                     |
// +-------------------------------------------------------------------------+
// | Main Admin - index.php                                                  |
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
require_once("../../../lib-common.php");                  // Path to your lib-common.php
require_once($_CONF['path'] . 'plugins/quiz/debug.php');  // Common Debug Code

if (!SEC_hasRights('quiz.edit')) {
    $display = COM_siteHeader('menu');
    $display .= COM_startBlock($LANG_QUIZ['access_denied']);
    $display .= $LANG_QUIZ['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter();
    echo $display;
    exit;
}


/* Filter incoming variables and set them as globals */
$myvars = array('uid','op','quizid','show','page');
qz_GetData($myvars,true);

$navbarMenu = array(
    'Quiz Listing'      => $_CONF['site_url'] .'/quiz/index.php',
    'New Quiz'      => $_CONF['site_admin_url'] .'/plugins/quiz/index.php?op=newquiz',
    'Refresh'           => $_CONF['site_admin_url'] .'/plugins/quiz/index.php'
);


if(empty($_USER['uid']) OR $_USER['uid'] == 1 ) {
    echo COM_siteHeader();
    echo "Anonymous Access not permitted. Add proper Error Message here";
    echo COM_siteFooter();
    exit();
} else {
    $uid = $_USER['uid'];
    echo COM_siteHeader();
}

echo '<script language="javascript">
    <!-- Begin
    function confirmSubmit(text) { 
      var yes = confirm(text); 
      if (yes) return true; 
      else return false; 
    } 
    //  End -->
    </script>';

// Check if the page navigation is being used
if (empty($_GET['show'])) {
    $show = 10;
}
// Check if page was specified
if (empty($_GET['page'])) {
    $page = 1;
}


switch ($op) {

case 'newquiz':
    echo COM_startBlock("Create New Quiz");
    echo '<body onload="document.newquiz.linkedquiz.disabled = 1")></body>';
    echo '<form name="newquiz" action="'.$PHP_SELF.'" method="post" style="margin:0px;">';
    echo '<table width="100%" border="0" cellspacing="1" cellpadding="5" ID="plg_table">
      <tr> 
        <td>Name</td>
        <td colspan="5"><input name="quizname" type="text" size="50" maxlength="80"> 
        </td>
        <td colspan="1"><div align="left"> 
            <label for="chk01">Enabled</label>
            <input name="enabled" type="checkbox" id="chk01" value="1" checked>
          </div></td>
      </tr>
      <tr> 
        <td>Description</td>
        <td colspan="6"><textarea name="quizdesc" cols="60" rows="3" id="quizdesc"></textarea></td>
      </tr>
      <tr> 
        <td colspan="1">Number of Questions</td>
        <td colspan="2"><input name="quizquestcnt" type="text" id="quizquestcnt" size="3"></td>
        <td colspan="3">Time Allowed (min)</td>
        <td colspan="1" align="left" width="40%"><input name="quiztime" type="text" id="quiztime" size="3"></td>
      </tr>
      <tr> 
        <td colspan="1">Total Score</td>
        <td colspan="2"><input name="quiztotalscore" type="text" id="quiztotalscore" size="3"></td>
        <td colspan="3">Pass Score</td>
        <td colspan="1"><input name="quizpass" type="text" id="quizpass" size="3"></td>
      </tr>
      <tr> 
        <td colspan="3"><fieldset><legend>Question Order</legend><label> 
          <input type="radio" name="quizorder" value="0">
          Random</label> <label> 
          <input type="radio" name="quizorder" value="1" checked>
          Fixed</label></fieldset></td>
        <td colspan="4"><fieldset><legend>Quiz Type</legend><label> 
          <input type="radio" name="quiztype" value="0" checked>
          Standard</label> <label> 
          <input type="radio" name="quiztype" value="1">
          Random Questions from Pool</label></fieldset></td>
      </tr>
      <tr> 
        <td colspan="3" rowspan="2">Available to<div style="margin-top:5px;"><select name="quizaccess">' . COM_optionList($_TABLES['groups'], "grp_id,grp_name",$HTTP_POST_VARS['quizaccess']) . '</select></div></td>
         <td colspan="4"><label for="chk02" style="padding-right:10px;">Pretest Req</label><input name="pretest" type="checkbox" id="chk02" value="1"' . $pretest.' onclick="if(pretest.checked) {linkedquiz.disabled=false} else {linkedquiz.disabled=true};"></td>
      </tr>
      <tr>
            <td colspan="4"><span style="padding-right:15px;">Linked Quiz:</span><select name="linkedquiz"><option value="0">Select linked quizes' . COM_optionList($_TABLES['quiz_master'], "quizid,name", "{$A['linkedquiz']}") . '</select></td>
      </tr>
      <tr>
       <td colspan="7" height="50" colspan="7"><div style="text-align:center; padding-top:10px;"><input type="Submit" name="submit" value="Create Quiz"><input type="hidden" name="op" value="createquiz"></div></td>
      </tr>
    </table></form>';
        echo COM_endBlock();
        echo COM_siteFooter();
        break;

case 'createquiz':
    // Need to use prepare for DB to allow quotes - name and description fields
    // Will need to add stripslashes on display for name and description    

    $HTTP_POST_VARS = qz_cleandata($HTTP_POST_VARS);
    $quizname = $HTTP_POST_VARS['quizname'];
    $quizdesc = $HTTP_POST_VARS['quizdesc'];
    $date = time();
    $author = $_USER['uid'];
    $quizaccess = $HTTP_POST_VARS['quizaccess'];
    $quizorder = $HTTP_POST_VARS['quizorder'];
    $quiztype = $HTTP_POST_VARS['quiztype'];
    $quiztotal = $HTTP_POST_VARS['quiztotalscore'];
    $quizpass = $HTTP_POST_VARS['quizpass'];
    $quizquestionnum = $HTTP_POST_VARS['quizquestcnt'];
    $quiztime = $HTTP_POST_VARS['quiztime'];
    $quizpretest = $HTTP_POST_VARS['pretest'];
    $enabled = $HTTP_POST_VARS['enabled'];
    $linkedquiz = $HTTP_POST_VARS['linkedquiz'];

    DB_query("INSERT INTO {$_TABLES['quiz_master']} (name,description,date,author,group_id,question_order,question_type,total_score,pass_score,num_of_questions,maxtime,pretest_req,linkedquiz, status) VALUES ('$quizname', '$quizdesc', '$date', '$author', '$quizaccess', '$quizorder', '$quiztype', '$quiztotal', '$quizpass', '$quizquestionnum', '$quiztime', '$quizpretest','$linkedquiz', '$enabled')");

    break;

case 'editquiz':

    $query=DB_Query("SELECT * FROM {$_TABLES['quiz_master']} WHERE quizid=$quizid");
    $A=DB_fetchArray($query,false);

    if ($A['status'] == 1) {
        $chkenabled = "checked";
    } else {
        $chkenabled = '';
    }

    if ($A['pretest_req'] == 1) {
        $pretest = "checked";
    } else {
        $pretest = '';
    }

    if ($A['question_order'] == 0) {
        $chkrandom = "checked";
        $chkfixed = "";
    } else {
        $chkrandom = "";
        $chkfixed = "checked";
    }

    if ($A['question_type'] == 0) {
        $chkstandard = "checked";
        $chkpool = "";
    } else {
        $chkstandard = "";
        $chkpool = "checked";
    }

    echo COM_startBlock("Update Quiz");
    echo '<body onload="document.editquiz.linkedquiz.disabled = 1")></body>';
    echo '<form name="editquiz" action="'.$PHP_SELF.'" method="post" style="margin:0px;">';
    echo '<table width="100%" border="0" cellspacing="1" cellpadding="5" bgcolor="#EFEFEF" ID="plg_table">
      <tr> 
        <td>Name</td>
        <td colspan="4"> <input name="quizname" type="text" size="40" maxlength="120" value="' .$A['name'] .'"> 
        </td>
        <td colspan="2"> <div align="left"> 
            <label for="chk01">Enabled</label>
            <input name="enabled" type="checkbox" id="chk01" value="1" '.$chkenabled.'>
          </div></td>
      </tr>
      <tr> 
        <td>Description</td>
        <td colspan="6"> <textarea name="quizdesc" cols="60" rows="3" id="quizdesc">'. $A['description']. '</textarea></td>
      </tr>
      <tr> 
        <td colspan="1">Number of Questions</td>
        <td colspan="2"><input name="quizquestcnt" type="text" id="quizquestcnt" size="3" value="' .$A['num_of_questions'] .'"></td>
        <td colspan="3">Time Allowed (min)</td>
        <td colspan="1" align="left" width="40%"><input name="quiztime" type="text" id="quiztime" size="3" value="' .$A['maxtime']. '"></td>
      </tr>
      <tr> 
        <td colspan="1">Total Score</td>
        <td colspan="2"><input name="quiztotalscore" type="text" id="quiztotalscore" size="3" value="' .$A['total_score']. '"></td>
        <td colspan="3">Pass Score</td>
        <td colspan="1"><input name="quizpass" type="text" id="quizpass" size="3" value="' .$A['pass_score']. '"></td>
      </tr>
      <tr> 
        <td colspan="3"><fieldset><legend>Question Order</legend><label> 
          <input type="radio" name="quizorder" value="0" ' .$chkrandom. '>
          Random</label> <label> 
          <input type="radio" name="quizorder" value="1"' .$chkfixed.'>
          Fixed</label></fieldset></td>
        <td colspan="4"><fieldset><legend>Quiz Type</legend><label> 
          <input type="radio" name="quiztype" value="0"'.$chkstandard.'>
          Standard</label> <label> 
          <input type="radio" name="quiztype" value="1"'.$chkpool.'>
          Random Questions from Pool</label></fieldset></td>
      </tr>
      <tr> 
        <td colspan="3" rowspan="2">Available to<div style="margin-top:5px;"><select name="quizaccess">' . COM_optionList($_TABLES['groups'], "grp_id,grp_name",$A['group_id']) . '</select></div></td>
         <td colspan="4"><label for="chk02" style="padding-right:10px;">Pretest Req</label><input name="pretest" type="checkbox" id="chk02" value="1"' . $pretest.' onclick="if(pretest.checked) {linkedquiz.disabled=false} else {linkedquiz.disabled=true};"></td>
      </tr>
      <tr>
            <td colspan="4"><span style="padding-right:15px;">Linked Quiz:</span><select name="linkedquiz"><option value="0">Select linked quizes' . COM_optionList($_TABLES['quiz_master'], "quizid,name", "{$A['linkedquiz']}") . '</select></td>
      </tr>
      <tr>
          <td colspan=7" width="100%" valign="middle" align="center" style="padding:10px;"><input type="submit" name="op" value="Delete" onClick="return confirmSubmit(\'Do you really want\nto delete this quiz?\')"><span style="padding-left:15px;"><input type="Submit" name="op" value="Update"><input type="hidden" name="quizid" value="'.$quizid.'"></span></td>
      </tr>
      </table></form>';
        echo COM_endBlock();
        echo COM_siteFooter();
        exit;
        break;

case 'Update';
    $HTTP_POST_VARS = qz_cleandata($HTTP_POST_VARS);
    $quizname = $HTTP_POST_VARS['quizname'];
    $quizdesc = $HTTP_POST_VARS['quizdesc'];
    $date = time();
    $author = $_USER['uid'];
    $quizaccess = $HTTP_POST_VARS['quizaccess'];
    $quizorder = $HTTP_POST_VARS['quizorder'];
    $quiztype = $HTTP_POST_VARS['quiztype'];
    $quiztotal = $HTTP_POST_VARS['quiztotalscore'];
    $quizpass = $HTTP_POST_VARS['quizpass'];
    $quizquestionnum = $HTTP_POST_VARS['quizquestcnt'];
    $quiztime = $HTTP_POST_VARS['quiztime'];
    $quizpretest = $HTTP_POST_VARS['pretest'];
    $linkedquiz = $HTTP_POST_VARS['linkedquiz'];
    $enabled = $HTTP_POST_VARS['enabled'];

    DB_query("UPDATE {$_TABLES['quiz_master']} SET name='$quizname', description='$quizdesc', date='$date', author='author', group_id='$quizaccess', question_order='$quizorder', question_type='$quiztype', total_score='$quiztotal', pass_score='$quizpass', num_of_questions='$quizquestionnum', maxtime='$quiztime', pretest_req='$quizpretest', linkedquiz='$linkedquiz', status='$enabled'  WHERE quizid=$quizid");
    break;

case 'Delete':

    // Select each question and delete it and any answers
    $sql = DB_query("SELECT qid FROM {$_TABLES['quiz_questions']} WHERE quizid=$quizid");
    while (list ($qid) = DB_fetchArray($sql)) {
        DB_query("DELETE FROM {$_TABLES['quiz_answers']} WHERE qid=$qid");
        DB_query("DELETE FROM {$_TABLES['quiz_questions']} WHERE qid=$qid");
    }
    // Delete the master quiz record
    DB_query("DELETE FROM {$_TABLES['quiz_master']} WHERE quizid=$quizid");

    break;

case 'copyquiz':
    $date = time();
    $source = DB_query("SELECT name, description, group_id, question_order, question_type, total_score, pass_score, num_of_questions, pretest_req, maxtime FROM {$_TABLES['quiz_master']} WHERE quizid=$quizid");
    list ($sname, $sdescription, $sgroup, $squestion_order, $squestion_type, $stotal_score, $spass_score, $snum_of_questions, $spretest_req, $smaxtime) = DB_fetchArray($source);
    DB_query("INSERT INTO {$_TABLES['quiz_master']} (name,description, date, author, group_id, question_order, question_type, total_score, pass_score, num_of_questions,pretest_req, maxtime) 
        VALUES ('$sname', '$sdescription', '$date', '$uid', '$sgroup', '$squestion_order', '$squestion_type', '$stotal_score', '$spass_score', '$snum_of_questions', '$spretest_req', '$smaxtime')");
    $newQuizid = DB_insertID();

    $source1 = DB_query("SELECT qid,question, qanswer, qvalue, qorder FROM {$_TABLES['quiz_questions']} WHERE quizid=$quizid");
    while (list ($sqid, $squestion, $sanswer, $svalue, $sorder) = DB_fetchArray($source1)) {
        DB_query("INSERT INTO {$_TABLES['quiz_questions']} (quizid,question,qanswer,qvalue,qorder) 
            VALUES ('$newQuizid','$squestion','$sanswer','$svalue','$sorder')");
        $newQid = DB_insertID();
        $source2 = DB_query("SELECT answer,aorder FROM {$_TABLES['quiz_answers']} WHERE qid=$sqid");
        while (list ($sanswer, $sorder) = DB_fetchArray($source2)) {
            DB_query("INSERT INTO {$_TABLES['quiz_answers']} (qid,answer,aorder) 
                VALUES ('$newQid','$sanswer','$sorder')");
        }
    }
    
    break;
    
case 'questions' :
    echo COM_refresh($_CONF['site_admin_url'] .'/plugins/quiz/questions.php?quizid='.$quizid);
    exit;
    break;

case 'results' :
    if (!isset($quizid)) {
        echo "<br>Error no Quizid";
        exit;
    }

    $navbarMenu = array(
        'Quiz Listing'      => $_CONF['site_url'] .'/quiz/index.php',
        'Quiz Admin'        => $_CONF['site_admin_url'] .'/plugins/quiz/index.php',
        'New Quiz'          => $_CONF['site_admin_url'] .'/plugins/quiz/index.php?op=newquiz',
        'Refresh'           => $_CONF['site_admin_url'] .'/plugins/quiz/index.php'
    );

   $helpmsg = '<p>Summary view of all test attempts for this quiz. Entries with the <font color=green>Green</font> flag are passing attempts. The results show the users score and time in min they took to take the quiz along with required min passing score. The <i>View Detail</i> link will show the actual quiz questions and the users answers<br><p />';
    $quizresults = DB_query("SELECT id,uid,score,date,test_durmin,questions,answers FROM {$_TABLES['quiz_results']} WHERE quizid={$quizid} ORDER by date DESC");
    $numresults = DB_numRows($quizresults);
    $pass = DB_getItem($_TABLES['quiz_master'], "pass_score", "quizid={$quizid}");
    $description = DB_getItem($_TABLES['quiz_master'], "description", "quizid={$quizid}");
    echo COM_startBlock("Quiz Results for all attempts on record");
    echo ppNavbar($navbarMenu);
    if ($numresults > 0) {
        echo '<table border="0" cellpadding="1" cellspacing="1" width="99%">
                <tr>
                    <td colspan="4" width="100%" align=left valign="top">' . $helpmsg . '</td>
                </tr>
          </table>';
        echo '<table border="0" cellpadding="3" cellspacing="1" width="100%" ID="plg_table">
            <tr>
                <th id="plg_heading" colspan="7" width="100%" style="padding-left:10px; text-align:left; vertical-align:top;"><b>Quiz Results</b>&nbsp;<font size="-2"(' . $description . ')</font></td>
            </tr>
            <tr align="center">
                <td width="1%">&nbsp;&nbsp;</td>
                <td nowrap style="padding:5px;" width="40%"><label>Date</label></td>
                <td nowrap style="padding:5px;"><label>User</label></td>
                <td nowrap style="padding:5px;"><label>Score</label></td>
                <td nowrap style="padding:5px;"><label>Pass</label></td>
                <td nowrap style="padding:5px;"><label>Time(min)</label></td>
                <td nowrap style="padding-left:5px; padding-right:5px;"><label>Quiz Answers</label></td>
           </tr>';
        while (list($res_record, $uid, $score, $resultsdate, $testdur,$questions,$answers) = DB_fetchArray($quizresults)) {
            $date = strftime($_CONF['date'], $resultsdate);
            $username = DB_getItem($_TABLES['users'], "username", "uid=$uid");
            echo '<tr align="center">';
            if ($score >= $pass) {
                echo '<td width="1%" bgcolor="#00FF00">&nbsp;</td>';
            } else {
                echo '<td width="1%" bgcolor="#FF0000">&nbsp;</td>';
            } 
            echo '<td width="24%">' . $date . '</td>';
            echo '<td width="15%">' . $username . '</td>';
            echo '<td width="5%">' . $score . '</td>';
            echo '<td width="5%">' . $pass . '</td>';    
            echo '<td width="10%">' . $testdur . '</td>';
            if ($questions != "" AND $answers != "") {
                echo '<td nowrap width="15%" style="padding-left:5px;padding-right:5px;"><a href="' . $_CONF['site_url'] . '/quiz/showresults.php?admin=true&id=' . $res_record . '">View Detail</a></td></tr>';
            } else {
                echo '<td nowrap width="15%" style="padding-left:5px;padding-right:5px;">Incomplete</a></td></tr>';
            }
        } 
        echo '</table><br><p />';
    }     
    echo COM_endBlock();
    
    exit;
    break;
}


// Main Code - Display the Top Tool Bar

$query = DB_query("SELECT count(*) FROM {$_TABLES['quiz_master']}");
$nrows = DB_fetchArray($query);
$numpages = ceil($nrows['0'] / $show);
$offset = ($page - 1) * $show;
$base_url = $_CONF['site_url'] . '/quiz/index.php?show='.$show. '&page='.$page; 

echo COM_startBlock("ProQuiz Admin Listing");
echo ppNavbar($navbarMenu);

echo '<table border="0" cellpadding="1" cellspacing="1" width="99%">
        <tr>
            <td width="100%" align=left valign="top">' . $LANG_QUIZ['helpmsg'] . '</td>
        </tr>
      </table>';

    echo '<table border="0" cellpadding="3" cellspacing="1" width="99%" ID="plg_table">
        <tr>
            <th id="plg_heading" colspan="6" width="100%" style="padding-left:10px; text-align:left; vertical-align:top; font-size:12; font-weight:bold; line-height:14pt;">Quiz Administration</td>
        </tr>
        <tr align="center">
            <td width="20"><label style="padding-left:10px;">#</label></td>
            <td width="50%"><label>Description</label></td>
            <td><label>Last Edit</label></td>
            <td style="padding-left:5px;padding-right:5px;" width="15%"><label>Questions</label></td>
            <td style="padding-left:5px;padding-right:5px;" width="15%"><label>Pass/Total</label></td>
            <td width="20%"><label>Action</label></td>
       </tr>';

    $query = DB_query("SELECT quizid,name,date,status,pass_score FROM {$_TABLES['quiz_master']} ORDER BY date DESC LIMIT $offset, $show"); 
    while ( list($id,$name,$date,$status,$pass_score) = DB_fetchARRAY($query)) {
        $query2 = DB_query("SELECT count(*), SUM(IF( score >= $pass_score, 1, 0 )) AS pass FROM {$_TABLES['quiz_results']} WHERE quizid = $id"); 
        list ($total_quizrecords,$total_passrecords) = DB_fetchArray($query2);
        if (!isset($total_passrecords)) {
            $total_passrecords = 0;
        }
        $date = strftime($_CONF['shortdate'],$date);
        $questionCount = DB_count($_TABLES['quiz_questions'], "quizid", $id);
        echo '<tr>
          <td align="center">' .$id.'</td>
          <td width="40%" style="padding-left:5px; padding-right:5px; text-align:left;">' .'<a href="'.$_CONF['site_url'] .'/quiz/takequiz.php?quizid='.$id.'">' .$name. '<a></td>
          <td style="padding-left:5px; padding-right:5px; text-align:center;">' .$date.'</td>
          <td width="10%" style="text-align:center;">' .$questionCount.'</td>
          <td width="15%" align="center"> ' .$total_passrecords. '/' . $total_quizrecords .'</td>
          <td width="30%" style="padding-left:5px;">';
          echo '<a href="'.$PHP_SELF.'?op=editquiz&quizid='.$id.'"><img src="'.$_CONF['site_url'].'/quiz/images/editquiz.gif" border="0" TITLE="Edit Quiz"></a>&nbsp;';
          echo '<a href="'.$PHP_SELF.'?op=copyquiz&quizid='.$id.'"><img src="'.$_CONF['site_url'].'/quiz/images/copy.gif" border="0" TITLE="Copy Quiz"></a>&nbsp;';
          echo '<a href="'.$PHP_SELF.'?op=questions&quizid='.$id.'"><img src="'.$_CONF['site_url'].'/quiz/images/questions.gif" border="0" TITLE="Quiz Questions"></a>&nbsp;';
          if ($total_quizrecords > 0) {
            echo '<a href="'.$PHP_SELF.'?op=results&quizid='.$id.'"><img src="'.$_CONF['site_url'].'/quiz/images/results.gif" border="0" TITLE="Quiz Results"></a>';
          }

        echo '</tr>';
    }
    echo '<p />' . COM_printPageNavigation($base_url,$page, $numpages);

echo '</table><br><p>';
echo COM_endBlock();
echo COM_siteFooter();

?>