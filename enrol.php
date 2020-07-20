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

require_once("$CFG->dirroot/enrol/redsys/lib/redsysHMAC256_API_PHP_7.0.0/apiRedsys.php");

$miObj = new RedsysAPI;

$order = new stdClass();
$order->user = $USER->id;
$order->course = $course->id;
$order->instance = $instance->id;
$order_number = $this->create_order_number($USER->id,$course->id);

$SESSION->{$order_number} = $order;

$miObj->setParameter("DS_MERCHANT_AMOUNT",number_format($localisedcost,2,'',''));
$miObj->setParameter("DS_MERCHANT_ORDER",$order_number);
$miObj->setParameter("DS_MERCHANT_MERCHANTCODE",$this->get_config('Ds_Merchant_MerchantCode'));
$miObj->setParameter("DS_MERCHANT_CURRENCY",$instance->currency);
$miObj->setParameter("DS_MERCHANT_TRANSACTIONTYPE","0");
$miObj->setParameter("DS_MERCHANT_TERMINAL",$this->get_config('Ds_Merchant_Terminal'));
$miObj->setParameter("DS_MERCHANT_MERCHANTURL","$CFG->wwwroot/enrol/redsys/ipn.php");
$miObj->setParameter("DS_MERCHANT_URLOK","$CFG->wwwroot/enrol/redsys/return.php?id=$course->id");
$miObj->setParameter("DS_MERCHANT_IDENTIFIER","REQUIRED");

//Datos de configuración
$version="HMAC_SHA256_V1";
$kc = $this->get_config('Ds_Merchant_Enc');
// Se generan los parámetros de la petición
$request = "";
$params = $miObj->createMerchantParameters();
$signature = $miObj->createMerchantSignature($kc);
$currency = $this->get_currencies();
$currency = isset($currency[$instance->currency]) ? $currency[$instance->currency] : 'Euro';
?>
<div align="center">
    <p><?php print_string("paymentrequired") ?></p>
    <p><b><?php echo $instancename; ?></b></p>
    <p><b><?php echo get_string("cost").": ".$currency." {$cost}"; ?></b></p>
    <p><img alt="<?php print_string('redsysaccepted', 'enrol_redsys') ?>" src="<?php echo $CFG->wwwroot."/enrol/redsys/pix/logo.png";?>" /></p>
    <p><?php print_string("paymentinstant") ?></p>

    <form action="<?php echo $this->get_config('url') ?>" method="post" target="_blank">
        <input type="hidden" name="Ds_SignatureVersion" value="<?php echo $version; ?>"/></br>
        <input type="hidden" name="Ds_MerchantParameters" value="<?php echo $params; ?>"/></br>
        <input type="hidden" name="Ds_Signature" value="<?php echo $signature; ?>"/></br>
        <input type="submit" value="<?php print_string("sendpaymentbutton", "enrol_redsys") ?>" />
    </form>

</div>
