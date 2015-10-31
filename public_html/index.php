<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +-------------------------------------------------------------------------+
// | Quiz Plugin 1.0 for Geeklog- The Ultimate OSS Portal                    |
// | Date: July 25, 2003                                                     |
// +-------------------------------------------------------------------------+
// | Index.php - Main User Program                                           |
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

require_once("../lib-common.php"); // Path to your lib-common.php
require_once($_CONF['path'] . 'plugins/quiz/debug.php');  // Common Debug Code

$quizstatus = array("Hidden", "Active");

if (empty($_USER['uid']) OR $_USER['uid'] == 1) {
    echo COM_siteHeader();
    echo $LANG_QZERR['noaccess'];
    echo COM_siteFooter();
    exit();
} else {
    $uid = $_USER['uid'];
    echo COM_siteHeader();
} 

if (SEC_hasRights('quiz.edit')) {
    $navbarMenu = array(
        'Quiz Admin'      => $_CONF['site_admin_url'] .'/plugins/quiz/index.php',
        'Refresh'         => $_CONF['site_url'] .'/quiz/index.php'
    );
} else {
    $navbarMenu = array(
        'Refresh'         => $_CONF['site_url'] .'/quiz/index.php'
    );
}

// Check if the page navigation is being used
if (empty($_GET['show'])) {
    $show = 10;
} 
// Check if page was specified
if (empty($_GET['page'])) {
    $page = 1;
} 
// Display the Top Tool Bar
$query = DB_query("SELECT count(*) FROM {$_TABLES['quiz_master']}");
$nrows = DB_fetchArray($query);
$numpages = ceil($nrows['0'] / $show);
$offset = ($page - 1) * $show;
$base_url = $_CONF['site_url'] . '/quiz/index.php?show=' . $show . '&page=' . $page;

echo COM_startBlock("ProQuiz Listing");
echo ppNavbar($navbarMenu);

echo '<br><p><table border="0" cellpadding="3" cellspacing="1" width="99%" ID="plg_table">
       <tr>
            <th id="plg_heading" colspan="7" width="100%" style="padding-left:10px; text-align:left; vertical-align:top; font-size:12; font-weight:bold;">Quiz Listing</td>
        </tr>
        <tr>
            <td width="20"><label>#</label></td>
            <td>&nbsp;&nbsp;</td>
            <td width="40%"><label>Description</label></td>
            <td><label>Date</label></td>
            <td width="10%"><label>Questions</label></td>
            <td width="10%"><label>Score</label></td>
            <td width="25%"><label>Options</label></td>
       </tr>';
if ($nrows[0] > 0) {
    $query = DB_query("SELECT quizid,name,date,status,pass_score,pretest_req FROM {$_TABLES['quiz_master']} ORDER BY quizid DESC LIMIT $offset, $show");
    while (list($id, $name, $date, $status, $pass, $pretest) = DB_fetchARRAY($query)) {
        $date = strftime($_CONF['shortdate'], $date);
        $result = "N/A";
        $options_link = "N/A";
        $pretestRequired = false;    
        $questionCount = DB_count($_TABLES['quiz_questions'], "quizid", $id);
        $quizresults = DB_query("SELECT score,date FROM {$_TABLES['quiz_results']} WHERE quizid={$id} AND uid={$uid} ORDER by score DESC");
        $numresults = DB_numRows($quizresults);
        if ($numresults > 0) {
            list($score, $resultsdate) = DB_fetchArray($quizresults);
            $result = $score . '/' . $pass;
            $options_link = '<a href="' . $PHP_SELF . '?op=results&quiz=' . $id . '">Show Results</a>';
        } elseif ($pretest) {
            $pretest_quiz = DB_getItem($_TABLES['quiz_master'], "linkedquiz", "quizid=$id"); 
            // Check and see if pre-test has been completed
            $pretestresults = DB_query("SELECT score,pass_score FROM {$_TABLES['quiz_master']} master, {$_TABLES['quiz_results']} results WHERE master.quizid=results.quizid AND results.quizid={$pretest_quiz} AND uid={$uid} ORDER by score DESC Limit 1");
            if (DB_numRows($pretestresults) > 0) {
                list($pretest_score, $pass_score) = DB_fetchArray($pretestresults);
                if ($pretest_score >= $pass_score) {
                    $result = "Pretest Passed";
                } else {
                    $result = "Pretest Failed";
                    $pretestRequired = true;
                } 
                $options_link = "<a href=\"{$_CONF['site_url']}/quiz/takequiz.php?quizid=$pretest_quiz\">Pretest - Quiz#$pretest_quiz</a>";
            } elseif ($pretest_quiz > 0) {
                $pretestRequired = true;
                $options_link = "<a href=\"{$_CONF['site_url']}/quiz/takequiz.php?quizid=$pretest_quiz\">Pretest - Quiz#$pretest_quiz</a>";
            } 
        } 
        echo '<tr style="line-height:14pt; padding-left:5px;padding-right:5px;"><td align="center">' . $id . '</td>';
        if ($numresults > 0 AND $score >= $pass) {
            echo '<td width="1%" bgcolor="#00FF00">&nbsp;</td>';
        } elseif ($numresults > 0) {
            echo '<td width="1%" bgcolor="#FF0000">&nbsp;</td>';
        } else {
            echo '<td width="1%">&nbsp;</td>';
        } 
        if ($pretestRequired) {
            echo '<td width="40%">' . '<a href="" onclick="alert(\'Pretest needs to be completed first\')">' . $name . '<a></td>';
        } else {
            echo '<td width="40%">' . '<a href="' . $_CONF['site_url'] . '/quiz/takequiz.php?quizid=' . $id . '">' . $name . '<a></td>';
        } 
        echo '<td>' . $date . '</td>
          <td width="15%" align="center">' . $questionCount . '</td>
          <td nowrap style="padding-left:5px;padding-right:5px;">' . $result . '</td>
          <td nowrap style="text-align:center;padding-left:5px;padding-right:5px;">' . $options_link . '</td>
        </tr>';
    } 
    echo '<p />' . COM_printPageNavigation($base_url, $page, $numpages);
} 

echo '</table><br><p>';

if ($HTTP_GET_VARS['op'] == "results") {
    $quiz = $HTTP_GET_VARS['quiz'];
    $quizresults = DB_query("SELECT id,score,date,test_durmin,questions,answers FROM {$_TABLES['quiz_results']} WHERE quizid={$quiz} AND uid={$uid} ORDER by date DESC");
    $numresults = DB_numRows($quizresults);
    $pass = DB_getItem($_TABLES['quiz_master'], "pass_score", "quizid={$quiz}");
    $description = DB_getItem($_TABLES['quiz_master'], "description", "quizid={$quiz}");

    if ($numresults > 0) {
        echo '<br><p><table border="0" cellpadding="3" cellspacing="1" width="90%" id="plg_table">
            <tr>
                <th id="plg_heading" colspan="5" width="100%" style="padding-left:10px; text-align:left; vertical-align:top;">Quiz Results<br><font size="-2">(' . $description . ')</font></td>
            </tr>
            <tr align="center">
                <td width="20">&nbsp;&nbsp;</td>
                <td><label style="padding:5px;">Date</label></td>
                <td nowrap style="padding:5px;"><label>Score</label></td>
                <td nowrap style="padding:5px;"><label>Time(min)</label></td>
                <td nowrap style="padding:5px;"><label>Quiz Answers</label></td>
           </tr>';
        while (list($res_record, $score, $resultsdate, $testdur, $questions, $answers) = DB_fetchArray($quizresults)) {
            $date = strftime($_CONF['shortdate'], $resultsdate);
            echo '<tr align="center">';
            if ($score >= $pass) {
                echo '<td width="1%" bgcolor="#00FF00">&nbsp;</td>';
            } else {
                echo '<td width="1%" bgcolor="#FF0000">&nbsp;</td>';
            } 
            echo '<td width="24%">' . $date . '</td>';
            echo '<td width="10%">' . $score . '</td>';
            echo '<td width="15%">' . $testdur . '</td>';
            if ($questions !="" AND $answers != "") {
            echo '<td nowrap width="50%" style="text-align:center;padding-left:5px;padding-right:5px;"><a href="' . $_CONF['site_url'] . '/quiz/showresults.php?id=' . $res_record . '">View Detail</a></td></tr>';
            } else {
                echo '<td nowrap width="50%" style="text-align:center;padding-left:5px;padding-right:5px;">incomplete</td></tr>';
            }
        } 
        echo '</table><br>';
        echo '<a href="' . $PHP_SELF . '">Hide Results</a><p />';
    } 
} 
echo COM_endBlock();
echo COM_siteFooter();

?>