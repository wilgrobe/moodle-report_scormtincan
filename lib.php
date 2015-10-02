<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants for module tincan
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the tincan specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package report_scormtincan
 * @copyright  2015 Walt Disney Company
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * NOTE: Not all core functions for Moodle can be placed here. The event
 * classes and triggers must be placed in the appropriate folders. Those
 * files can be found at
 */

namespace report_scormtincan;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/mod/scorm/locallib.php');
require ($CFG->dirroot .'/vendor/autoload.php');

try {
	$lrs = report_scormtincan::scormtincan_setup_lrs();
}
catch (Exception $e) {
	$lrs;
}

class report_scormtincan {
	
	//TODO: validate endpoint is not empty
	public static function scormtincan_setup_lrs(){
		$version = get_config('report_scormtincan', 'lrsversion');
		if (empty($version)){
			$version = '1.0.0';
		}
		
		$lrs = new \TinCan\RemoteLRS();
		$lrs
			->setEndPoint(get_config('report_scormtincan', 'lrsendpoint'))
		   	->setAuth(get_config('report_scormtincan', 'lrslogin'), get_config('report_scormtincan', 'lrspass'))
			->setversion($version);
		return $lrs;
	}
	
	/**
	 * Report Sco Experienced to LRS (attempted)
	 * @param Event $event
	 * @return boolean
	 */
	public static function scormtincan_sco_attempt_started(\core\event\base $event){
		global $CFG, $DB;
		$data = $event->get_data();
		$scorm = $event->get_record_snapshot('scorm', $data['other']['instanceid']);

		$lrs = self::scormtincan_setup_lrs();

		$statement = array(
			'actor' => self::scormtincan_getactor(),
			'verb' => array(
				'id' => 'http://adlnet.gov/expapi/verbs/attempted',
				'display' => array(
					'en-US' => 'attempted',
					'en-GB' => 'attempted',
					),
				),
			'object' => array(
				'id' =>  $CFG->wwwroot . '/course/view.php?id='. $event->courseid,
				'extensions' => array(
					$CFG->wwwroot . '/course/view.php?id=' => $event->courseid,
				),
				'definition' => array(
					'name' => array(
						'en-US' => $scorm->name,
						'en-GB' => $scorm->name,
					),
					'description' => array(
						'en-US' => $scorm->intro,
						'en-GB' => $scorm->intro,
					),
				),
			),
		);

		try {
			$response = $lrs->saveStatement($statement);
		}
		catch (Exception $e) {
			debug("The lrs recording attempt has failed.");
			debug_print_backtrace();
			//TODO: handle error
		}

		return true;
	}
	
	/**
	 * Report Sco Suspended to LRS (suspended)
	 * @param Event $event
	 * @return boolean
	 */
	public static function scormtincan_sco_attempt_exited(\core\event\base $event){
		global $CFG, $DB;
		$data = $event->get_data();
		$scorm = $event->get_record_snapshot('scorm', $data['other']['instanceid']);

		$statement = array(
			'actor' => self::scormtincan_getactor(),
			'verb' => array(
				'id' => 'http://adlnet.gov/expapi/verbs/suspended',
				'display' => array(
					'en-US' => 'suspended',
					'en-GB' => 'suspended',
					),
				),
			'object' => array(
				'id' =>  $CFG->wwwroot . '/course/view.php?id='. $event->courseid,
				'extensions' => array(
					$CFG->wwwroot . '/course/view.php?id=' => $event->courseid,
				),
				'definition' => array(
					'name' => array(
						'en-US' => $scorm->name,
						'en-GB' => $scorm->name,
					),
					'description' => array(
						'en-US' => $scorm->intro,
						'en-GB' => $scorm->intro,
					),
				),
			),
		);
		try {
			$response = $lrs->saveStatement($statement);
		}
		catch (Exception $e) {
			debug("The lrs recording attempt has failed.");
			debug_print_backtrace();
			//TODO: handle error
		}
		return true;
	}

	/**
	 * Report Sco Attempt Passed to LRS (passed)
	 * @param Event $event
	 * @return boolean
	 */
	public static function scormtincan_sco_attempt_passed(\core\event\base $event){
		global $CFG, $DB;
		$data = $event->get_data();
		$scorm = $event->get_record_snapshot('scorm', $data['other']['instanceid']);

		$lrs = self::scormtincan_setup_lrs();
		$score = self::scormtincan_getscores($data);
		$result = [
			"success" => true,
			"completion" => true,
		];
		if($score){
			$result['score'] = $score;
		}

		$statement = array(
			'actor' => self::scormtincan_getactor(),
			'verb' => array(
				'id' => 'http://adlnet.gov/expapi/verbs/passed',
				'display' => array(
					'en-US' => 'passed',
					'en-GB' => 'passed',
				),
			),
			'object' => array(
				'id' =>  $CFG->wwwroot . '/course/view.php?id='. $event->courseid,
				'extensions' => array(
					$CFG->wwwroot . '/course/view.php?id=' => $event->courseid,
				),
				'definition' => array(
					'name' => array(
						'en-US' => $scorm->name,
						'en-GB' => $scorm->name,
					),
					'description' => array(
						'en-US' => $scorm->intro,
						'en-GB' => $scorm->intro,
					),
				),
			),
			"result" => $result,
		);

		try {
			$response = $lrs->saveStatement($statement);
		}
		catch (Exception $e) {
			throw $e;
			//TODO: handle error
		}
						
		return true;
		
	}
		
	/**
	 * Report Sco Attempt Failed to LRS (failed)
	 * @param Event $event
	 * @return boolean
	 * Commented out for further development later.
	 */
	public static function scormtincan_sco_attempt_failed(\core\event\base $event){
		global $CFG, $DB;
		$data = $event->get_data();
		$scorm = $event->get_record_snapshot('scorm', $data['other']['instanceid']);

		$lrs = self::scormtincan_setup_lrs();
		$score = self::scormtincan_getscores($data);
		$result = [
			"success" => false,
			"completion" => true,
		];
		if($score){
			$result['score'] = $score;
		}
				
		$statement = array(
			'actor' => self::scormtincan_getactor(),
			'verb' => array(
				'id' => 'http://adlnet.gov/expapi/verbs/failed',
				'display' => array(
					'en-US' => 'failed',
					'en-GB' => 'failed',
				),
			),
			'object' => array(
				'id' =>  $CFG->wwwroot . '/course/view.php?id='. $event->courseid,
				'extensions' => array(
					$CFG->wwwroot . '/course/view.php?id=' => $event->courseid,
				),
				'definition' => array(
					'name' => array(
						'en-US' => $scorm->name,
						'en-GB' => $scorm->name,
					),
					'description' => array(
						'en-US' => $scorm->intro,
						'en-GB' => $scorm->intro,
					),
				),
			),
			"result" => $result,
		);

		try {
			$response = $lrs->saveStatement($statement);
		}
		catch (Exception $e) {
			debug("The lrs recording attempt has failed.");
			debug_print_backtrace();
			//TODO: handle error
		}
			
		return true;
	}
	
	/**
	 * Report Scorm Attempt Completed to LRS (completed)
	 * @param Event $event
	 * @return boolean
	 */
	public static function scormtincan_sco_attempt_completed(\core\event\base $event){
		global $CFG, $DB;
		$data = $event->get_data();
		$scorm = $event->get_record_snapshot('scorm', $data['other']['instanceid']);
		
		$lrs = self::scormtincan_setup_lrs();
		$score = self::scormtincan_getscores($data);
		$result = [
			"completion" => true,
		];
		if($score){
			$result['score'] = $score;
		}
				
		$statement = array(
			'actor' => self::scormtincan_getactor(),
			'verb' => array(
				'id' => 'http://adlnet.gov/expapi/verbs/completed',
				'display' => array(
					'en-US' => 'completed',
					'en-GB' => 'completed',
				),
			),
			'object' => array(
				'id' =>  $CFG->wwwroot . '/course/view.php?id='. $event->courseid,
				'extensions' => array(
					$CFG->wwwroot . '/course/view.php?id=' => $event->courseid,
				),
				'definition' => array(
					'name' => array(
						'en-US' => $scorm->name,
						'en-GB' => $scorm->name,
					),
					'description' => array(
						'en-US' => $scorm->intro,
						'en-GB' => $scorm->intro,
					),
				),
			),
			"result" => $result,
		);

		try {
			$response = $lrs->saveStatement($statement);
		}
		catch (Exception $e) {
			debug("The lrs recording attempt has failed.");
			debug_print_backtrace();
			//TODO: handle error
		}
			
		return true;
	}

	/**
	 * Report Scorm Attempt Scored to LRS (scored)
	 * @param Event $event
	 * @return boolean
	 */
	public static function scormtincan_sco_attempt_scored(\core\event\base $event){
		global $CFG, $DB;
		$data = $event->get_data();
		$scorm = $event->get_record_snapshot('scorm', $data['other']['instanceid']);
		
		$lrs = self::scormtincan_setup_lrs();
		$score = self::scormtincan_getscores($data);
		$result = [];
		if($score){
			$result['score'] = $score;
		}

		$statement = array(
			'actor' => self::scormtincan_getactor(),
			'verb' => array(
				'id' => 'http://adlnet.gov/expapi/verbs/scored',
				'display' => array(
					'en-US' => 'scored',
					'en-GB' => 'scored',
					),
				),
			'object' => array(
   				'id' =>  $CFG->wwwroot . '/course/view.php?id=' . $event->courseid,
   				'extensions' => array(
					$CFG->wwwroot . '/course/view.php?id=' => $event->courseid,
   				),
   				'definition' => array(
					'name' => array(
						'en-US' => $scorm->name,
						'en-GB' => $scorm->name,
					),
					'description' => array(
						'en-US' => $scorm->intro,
						'en-GB' => $scorm->intro,
					),
   				),
    		),
			"result" => $result,
	    );
	     
	    try {
	    	$response = $lrs->saveStatement($statement);
	    }
	    catch (Exception $e) {
	    	debug("The lrs recording attempt has failed.");
	    	debug_print_backtrace();
	    	//TODO: handle error
	    }
	    return true;
	}

	public static function scormtincan_getactor()
	{
		global $USER, $CFG;
		if ($USER->email){
			return new \TinCan\Agent([
				"objectType" => "Agent",
				"account" => array(
					"name" => $USER->username,
					"homePage" => $CFG->wwwroot
				),
				"name" => fullname($USER),
				"mbox" => "mailto:".$USER->email
			]);
		}
		else{
			return new \TinCan\Agent([
				"objectType" => "Agent",
				"account" => array(
					"name" => $USER->id,
					"homePage" => $CFG->wwwroot
				),
				"name" => fullname($USER)
			]);
		}
	}
	
	private static function scormtincan_getscores ($data)
	{
		$score = [];
		if(isset($data['other']['score.min'])){
			$score['min'] = $data['other']['score.min'];
		}
		if(isset($data['other']['score.max'])){
			$score['max'] = $data['other']['score.max'];
		}
		if(isset($data['other']['score.raw'])){
			$score['raw'] = $data['other']['score.raw'];
		}
		if(isset($data['other']['score.scaled'])){
			$score['scaled'] = $data['other']['score.scaled'];
		}
		if(count($score)){
			return $score;
		}
		return false;
	}
}
