<?php

$_SQL[] = "ALTER TABLE {$_TABLES['quiz_master']} ADD linkedquiz mediumint(8) NOT NULL AFTER pretest_req";
$_SQL[] = "ALTER TABLE {$_TABLES['quiz_results']} ADD questions varchar(255) NOT NULL AFTER test_durmin";
$_SQL[] = "ALTER TABLE {$_TABLES['quiz_results']} ADD answers varchar(255) NOT NULL AFTER test_durmin";

?>