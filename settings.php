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


defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    //--- settings ------------------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_redsys_settings', '', get_string('pluginname_desc', 'enrol_redsys')));

    $settings->add(new admin_setting_configtext('enrol_redsys/url', get_string('url', 'enrol_redsys'), get_string('url_desc', 'enrol_redsys'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('enrol_redsys/url_recurrent', get_string('url_recurrent', 'enrol_redsys'), get_string('url_recurrent_desc', 'enrol_redsys'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('enrol_redsys/Ds_Merchant_MerchantCode', get_string('Ds_Merchant_MerchantCode', 'enrol_redsys'), get_string('Ds_Merchant_MerchantCode_desc', 'enrol_redsys'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('enrol_redsys/Ds_Merchant_Terminal', get_string('Ds_Merchant_Terminal', 'enrol_redsys'), get_string('Ds_Merchant_Terminal_desc', 'enrol_redsys'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('enrol_redsys/Ds_Merchant_Enc', get_string('Ds_Merchant_Enc', 'enrol_redsys'), get_string('Ds_Merchant_Enc_desc', 'enrol_redsys'), '', PARAM_TEXT));


    $options = array('unique'  => get_string('payment_unique_type', 'enrol_redsys'),
        'recurrent' => get_string('payment_recurrent_type', 'enrol_redsys'));
    $settings->add(new admin_setting_configselect('enrol_redsys/customchar1',
        get_string('payment_type', 'enrol_redsys'), get_string('payment_type_desc', 'enrol_redsys'), 'unique', $options));



    $settings->add(new admin_setting_configcheckbox('enrol_redsys/mailstudents', get_string('mailstudents', 'enrol_redsys'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_redsys/mailteachers', get_string('mailteachers', 'enrol_redsys'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_redsys/mailadmins', get_string('mailadmins', 'enrol_redsys'), '', 0));

    $options = array(
        ENROL_EXT_REMOVED_KEEP           => get_string('extremovedkeep', 'enrol'),
        ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'),
        ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
    );
    $settings->add(new admin_setting_configselect('enrol_redsys/expiredaction', get_string('expiredaction', 'enrol_redsys'), get_string('expiredaction_help', 'enrol_redsys'), ENROL_EXT_REMOVED_SUSPENDNOROLES, $options));

    //--- enrol instance defaults ----------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_redsys_defaults',
        get_string('enrolinstancedefaults', 'admin'), get_string('enrolinstancedefaults_desc', 'admin')));

    $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                     ENROL_INSTANCE_DISABLED => get_string('no'));
    $settings->add(new admin_setting_configselect('enrol_redsys/status',
        get_string('status', 'enrol_redsys'), get_string('status_desc', 'enrol_redsys'), ENROL_INSTANCE_DISABLED, $options));

    $settings->add(new admin_setting_configtext('enrol_redsys/cost', get_string('cost', 'enrol_redsys'), '', 0, PARAM_FLOAT, 4));

    $redsyscurrencies = enrol_get_plugin('redsys')->get_currencies();
    $settings->add(new admin_setting_configselect('enrol_redsys/currency', get_string('currency', 'enrol_redsys'), '', 'USD', $redsyscurrencies));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_redsys/roleid',
            get_string('defaultrole', 'enrol_redsys'), get_string('defaultrole_desc', 'enrol_redsys'), $student->id, $options));
    }

    $settings->add(new admin_setting_configduration('enrol_redsys/enrolperiod',
        get_string('enrolperiod', 'enrol_redsys'), get_string('enrolperiod_desc', 'enrol_redsys'), 0));
}
