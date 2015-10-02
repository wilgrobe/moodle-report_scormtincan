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
 * The main tincan configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package report_scormtincan
 * @copyright  2015 Walt Disney Company
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/lib.php');

if ($hassiteconfig) {
    // New settings page
    $page = new admin_settingpage('scormtincan', get_string('pluginname', 'report_scormtincan'));
		
	//Add the LRS settings header
	$page->add(new admin_setting_heading('report_scormtincan/scormtincanlrsheader', get_string('scormtincanlrsheader', 'report_scormtincan'), ''));
	
	//Add LRS endpoint
	$page->add(new admin_setting_configtext('report_scormtincan/lrsendpoint',
        get_string('scormtincanlrsendpoint', 'report_scormtincan'), get_string('scormtincanlrsendpoint_help', 'report_scormtincan'), 'http://example.com/endpoint/', PARAM_TEXT, 64));
	
	//Add basic authorisation login. TODO: OAuth
	$page->add(new admin_setting_configtext('report_scormtincan/lrslogin',
        get_string('scormtincanlrslogin', 'report_scormtincan'), get_string('scormtincanlrslogin_help', 'report_scormtincan'), '', PARAM_TEXT, 64));
	
	//Add basic authorisation pass. TODO: OAuth
	$page->add(new admin_setting_configtext('report_scormtincan/lrspass',
        get_string('scormtincanlrspass', 'report_scormtincan'), get_string('scormtincanlrspass_help', 'report_scormtincan'), '', PARAM_TEXT, 64));
	
	$page->add(new admin_setting_configtext('report_scormtincan/lrsversion',
        get_string('scormtincanlrsversion', 'report_scormtincan'), get_string('scormtincanlrsversion_help', 'report_scormtincan'), '1.0.0', PARAM_TEXT, 5));

	// Add settings page to navigation tree
    $ADMIN->add('reports', $page);
}
