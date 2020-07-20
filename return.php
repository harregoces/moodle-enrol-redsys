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

require("../../config.php");
require_once("$CFG->dirroot/enrol/redsys/lib.php");
require_once("$CFG->dirroot/enrol/redsys/lib/redsysHMAC256_API_PHP_7.0.0/apiRedsys.php");

$id = required_param('id', PARAM_INT);
$miObj = new RedsysAPI;
$version = $_REQUEST["Ds_SignatureVersion"];
$datos = $_REQUEST["Ds_MerchantParameters"];
$signatureRecibida = $_REQUEST["Ds_Signature"];
$kc = get_config('enrol_redsys','Ds_Merchant_Enc');
$firma = $miObj->createMerchantSignatureNotif($kc,$datos);

if (!($firma === $signatureRecibida)){
    die('Firma no valida');
}

$decodec = json_decode($miObj->decodeMerchantParameters($datos));

if (!$course = $DB->get_record("course", array("id"=>$id))) {
    redirect($CFG->wwwroot);
}

$context = context_course::instance($course->id, MUST_EXIST);
$PAGE->set_context($context);

require_login();

if (!empty($SESSION->wantsurl)) {
    $destination = $SESSION->wantsurl;
    unset($SESSION->wantsurl);
} else {
    $destination = "$CFG->wwwroot/course/view.php?id=$course->id";
}

$fullname = format_string($course->fullname, true, array('context' => $context));

$data = new stdClass();
$data->Ds_SignatureVersion = $version;
$data->Ds_MerchantParameters = $datos;
$data->Ds_Signature = $signatureRecibida;

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

if (is_enrolled($context, NULL, '', true)) { // TODO: use real redsys check
    redirect($destination, get_string('paymentthanks', '', $fullname));

} else {   /// Somehow they aren't enrolled yet!  :-(
    $PAGE->set_url($destination);
    echo $OUTPUT->header();
    $a = new stdClass();
    $a->teacher = get_string('defaultcourseteacher');
    $a->fullname = $fullname;
    notice(get_string('paymentsorry', '', $a), $destination);
}


