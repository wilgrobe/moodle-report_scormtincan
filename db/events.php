<?php


/**
 * Add event handlers for the quiz
 *
 * @package    report_scormtincan
 * @copyright 2015 Walt Disney Company
 * @category   event
 */

defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname'   => '\mod_scorm\event\sco_launched',
        'includefile' => 'report/scormtincan/lib.php',
        'callback'    => '\report_scormtincan\report_scormtincan::scormtincan_sco_attempt_started',
    ),
    array(
        'eventname'   => '\mod_scorm\event\sco_exited',
        'includefile' => 'report/scormtincan/lib.php',
        'callback'    => '\report_scormtincan\report_scormtincan::scormtincan_sco_attempt_exited',
    ),
	array(
        'eventname'   => '\mod_scorm\event\sco_passed',
        'includefile' => 'report/scormtincan/lib.php',
        'callback'    => '\report_scormtincan\report_scormtincan::scormtincan_sco_attempt_passed',
    ),
    array(
        'eventname'   => '\mod_scorm\event\sco_failed',
		'includefile' => 'report/scormtincan/lib.php',
        'callback'    => '\report_scormtincan\report_scormtincan::scormtincan_sco_attempt_failed',
	),
	array(
		'eventname'   => '\mod_scorm\event\sco_completed',
		'includefile' => 'report/scormtincan/lib.php',
		'callback'    => '\report_scormtincan\report_scormtincan::scormtincan_sco_attempt_completed',
	),
	array(
		'eventname'   => '\mod_scorm\event\sco_scored',
		'includefile' => 'report/scormtincan/lib.php',
		'callback'    => '\report_scormtincan\report_scormtincan::scormtincan_sco_attempt_scored',
    ),
);