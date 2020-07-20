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
 * Strings for component 'enrol_redsys', language 'en'.
 *
 * @package    enrol_redsys
 * @copyright  2020 Hernan Arregoces
 * @author     Hernan Arregoces harregoces@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['assignrole'] = 'Assign role';
$string['businessemail'] = 'redsys business email';
$string['businessemail_desc'] = 'The email address of your business redsys account';
$string['cost'] = 'Enrol cost';
$string['costerror'] = 'The enrolment cost is not numeric';
$string['costorkey'] = 'Please choose one of the following methods of enrolment.';
$string['currency'] = 'Currency';
$string['defaultrole'] = 'Default role assignment';
$string['defaultrole_desc'] = 'Select role which should be assigned to users during redsys enrolments';
$string['enrolenddate'] = 'End date';
$string['enrolenddate_help'] = 'If enabled, users can be enrolled until this date only.';
$string['enrolenddaterror'] = 'Enrolment end date cannot be earlier than start date';
$string['enrolperiod'] = 'Enrolment duration';
$string['enrolperiod_desc'] = 'Default length of time that the enrolment is valid. If set to zero, the enrolment duration will be unlimited by default.';
$string['enrolperiod_help'] = 'Length of time that the enrolment is valid, starting with the moment the user is enrolled. If disabled, the enrolment duration will be unlimited.';
$string['enrolstartdate'] = 'Start date';
$string['enrolstartdate_help'] = 'If enabled, users can be enrolled from this date onward only.';
$string['errdisabled'] = 'The redsys enrolment plugin is disabled and does not handle payment notifications.';
$string['erripninvalid'] = 'Instant payment notification has not been verified by redsys.';
$string['errredsysconnect'] = 'Could not connect to {$a->url} to verify the instant payment notification: {$a->result}';
$string['expiredaction'] = 'Enrolment expiry action';
$string['expiredaction_help'] = 'Select action to carry out when user enrolment expires. Please note that some user data and settings are purged from course during course unenrolment.';
$string['mailadmins'] = 'Notify admin';
$string['mailstudents'] = 'Notify students';
$string['mailteachers'] = 'Notify teachers';
$string['messageprovider:redsys_enrolment'] = 'redsys enrolment messages';
$string['nocost'] = 'There is no cost associated with enrolling in this course!';
$string['redsys:config'] = 'Configure redsys enrol instances';
$string['redsys:manage'] = 'Manage enrolled users';
$string['redsys:unenrol'] = 'Unenrol users from course';
$string['redsys:unenrolself'] = 'Unenrol self from the course';
$string['redsysaccepted'] = 'redsys payments accepted';
$string['pluginname'] = 'Redsys';
$string['pluginname_desc'] = 'The redsys module allows you to set up paid courses.  If the cost for any course is zero, then students are not asked to pay for entry.  There is a site-wide cost that you set here as a default for the whole site and then a course setting that you can set for each course individually. The course cost overrides the site cost.';
$string['privacy:metadata:enrol_redsys:enrol_redsys'] = 'Information about the redsys transactions for redsys enrolments.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:business'] = 'Email address or redsys account ID of the payment recipient (that is, the merchant).';
$string['privacy:metadata:enrol_redsys:enrol_redsys:courseid'] = 'The ID of the course that is sold.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:instanceid'] = 'The ID of the enrolment instance in the course.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:item_name'] = 'The full name of the course that its enrolment has been sold.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:memo'] = 'A note that was entered by the buyer in redsys website payments note field.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:option_selection1_x'] = 'Full name of the buyer.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:parent_txn_id'] = 'In the case of a refund, reversal, or canceled reversal, this would be the transaction ID of the original transaction.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:payment_status'] = 'The status of the payment.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:payment_type'] = 'Holds whether the payment was funded with an eCheck (echeck), or was funded with redsys balance, credit card, or instant transfer (instant).';
$string['privacy:metadata:enrol_redsys:enrol_redsys:pending_reason'] = 'The reason why payment status is pending (if that is).';
$string['privacy:metadata:enrol_redsys:enrol_redsys:reason_code'] = 'The reason why payment status is Reversed, Refunded, Canceled_Reversal, or Denied (if the status is one of them).';
$string['privacy:metadata:enrol_redsys:enrol_redsys:receiver_email'] = 'Primary email address of the payment recipient (that is, the merchant).';
$string['privacy:metadata:enrol_redsys:enrol_redsys:receiver_id'] = 'Unique redsys account ID of the payment recipient (i.e., the merchant).';
$string['privacy:metadata:enrol_redsys:enrol_redsys:tax'] = 'Amount of tax charged on payment.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:timeupdated'] = 'The time of Moodle being notified by redsys about the payment.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:txn_id'] = 'The merchant\'s original transaction identification number for the payment from the buyer, against which the case was registered';
$string['privacy:metadata:enrol_redsys:enrol_redsys:userid'] = 'The ID of the user who bought the course enrolment.';
$string['privacy:metadata:enrol_redsys:redsys_com'] = 'The redsys enrolment plugin transmits user data from Moodle to the redsys website.';
$string['privacy:metadata:enrol_redsys:redsys_com:address'] = 'Address of the user who is buying the course.';
$string['privacy:metadata:enrol_redsys:redsys_com:city'] = 'City of the user who is buying the course.';
$string['privacy:metadata:enrol_redsys:redsys_com:country'] = 'Country of the user who is buying the course.';
$string['privacy:metadata:enrol_redsys:redsys_com:custom'] = 'A hyphen-separated string that contains ID of the user (the buyer), ID of the course, ID of the enrolment instance.';
$string['privacy:metadata:enrol_redsys:redsys_com:email'] = 'Email address of the user who is buying the course.';
$string['privacy:metadata:enrol_redsys:redsys_com:first_name'] = 'First name of the user who is buying the course.';
$string['privacy:metadata:enrol_redsys:redsys_com:last_name'] = 'Last name of the user who is buying the course.';
$string['privacy:metadata:enrol_redsys:redsys_com:os0'] = 'Full name of the buyer.';
$string['processexpirationstask'] = 'redsys enrolment send expiry notifications task';
$string['sendpaymentbutton'] = 'Send payment via redsys';
$string['status'] = 'Allow redsys enrolments';
$string['status_desc'] = 'Allow users to use redsys to enrol into a course by default.';
$string['transactions'] = 'redsys transactions';
$string['unenrolselfconfirm'] = 'Do you really want to unenrol yourself from course "{$a}"?';


$string['url'] = 'Url';
$string['url_desc'] = 'Url for online payment';
$string['url_recurrent'] = 'Url offline';
$string['url_recurrent_desc'] = 'Url for offline payment';
$string['Ds_Merchant_MerchantCode'] = 'Merchant code';
$string['Ds_Merchant_MerchantCode_desc'] = 'Merchant code';
$string['Ds_Merchant_Terminal'] = 'Terminal';
$string['Ds_Merchant_Terminal_desc'] = '';
$string['Ds_Merchant_Enc'] = 'Ds_Merchant_Enc';
$string['Ds_Merchant_Enc_desc'] = 'Ds_Merchant_Enc_desc';
$string['payment_type'] = 'Payment type';
$string['payment_unique_type'] = 'Pago unico';
$string['payment_recurrent_type'] = 'Pago Recurrente';
$string['payment_type_desc'] = 'Choose payment type unique(One time payment) or recurrent(Several payment)';
