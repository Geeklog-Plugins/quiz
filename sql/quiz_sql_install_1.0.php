<?php

# SQL Commands for New Install of the Geeklog Quiz Plugin
# Last updated July 13 /2003
# Blaine Lang geeklog@langfamily.ca

#
# Table structure for table `{$_TABLES['quiz_answers']}`
#
# Creation: Jul 12, 2003 at 10:11 AM
# Last update: Jul 12, 2003 at 12:54 PM
# Last check: Jul 12, 2003 at 10:11 AM
#
$_SQL[] = "CREATE TABLE {$_TABLES['quiz_answers']} (
  `id` mediumint(9) NOT NULL auto_increment,
  `qid` mediumint(9) NOT NULL default '0',
  `answer` varchar(254) NOT NULL default '',
  `aorder` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `qid` (`qid`)
) TYPE=MyISAM COMMENT='Quiz Answers for a specific Question - Quiz Plugin' AUTO_INCREMENT=1;";

# --------------------------------------------------------

#
# Table structure for table `gl_quiz_master`
#
# Creation: Jul 05, 2003 at 03:33 PM
# Last update: Jul 12, 2003 at 03:07 PM
#
$_SQL[] = "CREATE TABLE {$_TABLES['quiz_master']} (
  `quizid` mediumint(9) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default '',
  `description` longtext NOT NULL,
  `date` int(11) NOT NULL default '0',
  `author` mediumint(9) NOT NULL default '0',
  `group_id` mediumint(9) NOT NULL default '0',
  `question_order` tinyint(4) NOT NULL default '1',
  `question_type` tinyint(4) NOT NULL default '1',
  `total_score` mediumint(9) NOT NULL default '100',
  `pass_score` mediumint(9) NOT NULL default '0',
  `num_of_questions` mediumint(9) NOT NULL default '0',
  `pretest_req` tinyint(4) NOT NULL default '0',
  `linkedquiz` mediumint(9) NOT NULL,
  `maxtime` mediumint(9) NOT NULL default '0',
  `status` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`quizid`)
) TYPE=MyISAM COMMENT='Master Quiz Record for Quiz Plugin' AUTO_INCREMENT=1;";

# --------------------------------------------------------

#
# Table structure for table `{$_TABLES['quiz_questions']}`
#
# Creation: Jul 07, 2003 at 10:33 PM
# Last update: Jul 12, 2003 at 02:54 PM
# Last check: Jul 07, 2003 at 10:33 PM
#
$_SQL[] = "CREATE TABLE {$_TABLES['quiz_questions']} (
  `qid` mediumint(9) NOT NULL auto_increment,
  `quizid` mediumint(9) NOT NULL default '0',
  `question` varchar(254) NOT NULL default '',
  `qanswer` tinyint(4) NOT NULL default '0',
  `qvalue` tinyint(4) NOT NULL default '0',
  `qorder` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`qid`),
  KEY `testid` (`quizid`)
) TYPE=MyISAM COMMENT='Question Table for Quiz Plugin ' AUTO_INCREMENT=1;";

# --------------------------------------------------------

#
# Table structure for table `gl_quiz_results`
#
# Creation: Jun 29, 2003 at 09:54 PM
# Last update: Jun 29, 2003 at 09:54 PM
# Last check: Jun 29, 2003 at 09:54 PM
#
$_SQL[] = "CREATE TABLE {$_TABLES['quiz_results']} (
  `id` mediumint(9) NOT NULL auto_increment,
  `quizid` mediumint(9) NOT NULL default '0',
  `uid` mediumint(9) NOT NULL default '0',
  `score` mediumint(9) NOT NULL default '0',
  `date` int(11) NOT NULL default '0',
  `test_durmin` mediumint(9) NOT NULL default '0',
  `questions` varchar(255) NOT NULL,
  `answers` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `quizid` (`quizid`),
  KEY `uid` (`uid`)
) TYPE=MyISAM COMMENT='Quiz Results for user - Quiz Plugin' AUTO_INCREMENT=1;";


#
# Table structure for table `gl_quiz_images`
#
# Creation: Apr 02, 2004 at 04:03 PM
# Last update: Apr 02, 2004 at 04:03 PM
# Last check: Apr 02, 2004 at 04:03 PM
#

$_SQL[] = "CREATE TABLE {$_TABLES['quiz_images']} (
  `id` mediumint(9) NOT NULL auto_increment,
  `qid` mediumint(9) NOT NULL default '0',
  `filename` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `qid` (`qid`)
) TYPE=MyISAM;";


#
# Insert Default Quiz for demo
# Dumping data for table `{$_TABLES['quiz_answers']}`
#
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (1, 2, 'The capital of Canada', 1);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (2, 2, 'The capital of Ontario', 2);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (3, 2, 'A non-capital city', 3);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (4, 1, 'On the North shore of Lake Erie', 1);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (5, 1, 'On the South shore of Lake Ontario', 2);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (6, 1, 'On the North shore of Lake Ontario', 3);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (7, 1, 'On the East Shore of Lake Erie', 4);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (8, 3, '1780', 1);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (9, 3, '1834', 2);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (10, 3, '1882', 3);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (11, 3, '1901', 4);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (12, 4, 'Mohawk word for \"Meeting Place\"', 1);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (13, 4, 'Iroquois word for \"Land of many lakes\"', 2);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (14, 4, 'Named after the founder General William Toronto', 3);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (15, 4, 'Was the harbour town on route to an indian village called Ronto - as in To Ronto. Later just called Toronto', 4);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (16, 5, '1.6 Million', 1);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (17, 5, '2.1 Million', 2);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (18, 5, '2.4 Million', 3);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (19, 5, '3.1 Million', 4);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (20, 5, '4.7 Million', 5);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (21, 6, '30 Ethic groups and 35 languages', 1);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (22, 6, '50 Ethic groups and 65 languages', 2);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (23, 6, '72 Ethic groups and 82 languages', 3);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (24, 6, '80 Ethic groups and 100 languages', 4);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (25, 7, 'The longest Street in the world', 1);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (26, 7, 'The shortest street in the world', 2);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (27, 7, 'The only city street with no street lights', 3);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (28, 7, 'The only street in Toronto that does not allow cars', 4);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (29, 8, '1602 ft, 10 inches', 1);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (30, 8, '1815 ft, 5 inches', 2);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (31, 8, '1902 ft, 6 inches', 3);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (32, 8, '1980 ft, 5 inches', 4);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (33, 9, '1967', 1);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (34, 9, '1976', 2);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (35, 9, '1982', 3);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (36, 9, '1988', 4);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (37, 10, 'Toronto Capitals', 2);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (38, 10, 'Toronto Maple Leafs', 3);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (39, 10, 'Toronto Maple Laughs', 1);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_answers']} VALUES (40, 10, 'Toronto Raptors', 4);";

#
# Dumping data for table `gl_quiz_master`
#
$_SQL[]= "INSERT INTO {$_TABLES['quiz_master']} VALUES (1, 'Toronto Tour Guide Certification','General questions to test your knowledge of Toronto', 1058149267, 4, 2, 0, 0, 0, 70, 0, 0, 0, 0, 0);";

#
# Dumping data for table `gl_quiz_questions`
#

$_SQL[]= "INSERT INTO {$_TABLES['quiz_questions']} VALUES (1, 1, 'Where is Toronto Located', 6, 10, 1);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_questions']} VALUES (2, 1, 'Toronto is ...', 2, 10, 2);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_questions']} VALUES (3, 1, 'What year was Toronto formed', 9, 10, 3);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_questions']} VALUES (4, 1, 'The word Toronto means', 12, 10, 4);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_questions']} VALUES (5, 1, 'The population of Toronto is', 18, 10, 5);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_questions']} VALUES (6, 1, 'How many Languages are home to Toronto', 24, 10, 6);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_questions']} VALUES (7, 1, 'Yonge Street is', 25, 10, 7);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_questions']} VALUES (8, 1, 'How tall is the CN Tower', 30, 10, 8);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_questions']} VALUES (9, 1, 'What year was the CN Tower built', 34, 10, 9);";
$_SQL[]= "INSERT INTO {$_TABLES['quiz_questions']} VALUES (10, 1, 'Toronto Hockey Team is called', 38, 10, 10);";


?>