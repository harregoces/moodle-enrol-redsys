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
 * Redsys enrolment plugin
 *
 * @package    enrol_redsys
 * @copyright  2020 Hernan Arregoces
 * @author     Hernan Arregoces harregoces@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


define('NO_DEBUG_DISPLAY', true);

require("../../config.php");
require_once("lib.php");
require_once($CFG->libdir.'/enrollib.php');
require_once($CFG->libdir . '/filelib.php');

set_exception_handler(\enrol_redsys\util::get_exception_handler());

// Make sure we are enabled in the first place.
if (!enrol_is_enabled('redsys')) {
    http_response_code(503);
    throw new moodle_exception('errdisabled', 'enrol_redsys');
}

/// Keep out casual intruders
if (empty($_POST) or !empty($_GET)) {
    http_response_code(400);
    throw new moodle_exception('invalidrequest', 'core_error');
}

$data = new stdClass();

foreach ($_POST as $key => $value) {
    if ($key !== clean_param($key, PARAM_ALPHANUMEXT)) {
        throw new moodle_exception('invalidrequest', 'core_error', '', null, $key);
    }
    if (is_array($value)) {
        throw new moodle_exception('invalidrequest', 'core_error', '', null, 'Unexpected array param: '.$key);
    }
    $data->$key = fix_utf8($value);
}

if (empty($data->Ds_SignatureVersion)) {
    throw new moodle_exception('invalidrequest', 'core_error', '', null, 'Missing request param: Ds_SignatureVersion');
}

if (empty($data->Ds_MerchantParameters)) {
    throw new moodle_exception('invalidrequest', 'core_error', '', null, 'Missing request param: Ds_MerchantParameters');
}

if (empty($data->Ds_Signature)) {
    throw new moodle_exception('invalidrequest', 'core_error', '', null, 'Missing request param: Ds_Signature');
}

enrol_redsys_plugin::process_request($data);
