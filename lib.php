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
 * @copyright  2020 Hernan Arregoces harregoces@gmail.com
 * @author     Hernan Arregoces
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class enrol_redsys_plugin extends enrol_plugin {

    public function get_currencies() {
        $codes = array('000'=>'USD', '978'=>'EUR');
        $currencies = array();
        foreach ($codes as $key => $val) {
            $currencies[$key] = new lang_string($val, 'core_currencies');;
        }

        return $currencies;
    }

    /**
     * Returns optional enrolment information icons.
     *
     * This is used in course list for quick overview of enrolment options.
     *
     * We are not using single instance parameter because sometimes
     * we might want to prevent icon repetition when multiple instances
     * of one type exist. One instance may also produce several icons.
     *
     * @param array $instances all enrol instances of this type in one course
     * @return array of pix_icon
     */
    public function get_info_icons(array $instances) {
        $found = false;
        foreach ($instances as $instance) {
            if ($instance->enrolstartdate != 0 && $instance->enrolstartdate > time()) {
                continue;
            }
            if ($instance->enrolenddate != 0 && $instance->enrolenddate < time()) {
                continue;
            }
            $found = true;
            break;
        }
        if ($found) {
            return array(new pix_icon('icon', get_string('pluginname', 'enrol_redsys'), 'enrol_redsys'));
        }
        return array();
    }

    public function roles_protected() {
        // users with role assign cap may tweak the roles later
        return true;
    }

    public function allow_unenrol(stdClass $instance) {
        // users with unenrol cap may unenrol other users manually - requires enrol/redsys:unenrol
        return true;
    }

    public function allow_manage(stdClass $instance) {
        // users with manage cap may tweak period and status - requires enrol/redsys:manage
        return true;
    }

    public function show_enrolme_link(stdClass $instance) {
        return ($instance->status == ENROL_INSTANCE_ENABLED);
    }

    /**
     * Returns true if the user can add a new instance in this course.
     * @param int $courseid
     * @return boolean
     */
    public function can_add_instance($courseid) {
        $context = context_course::instance($courseid, MUST_EXIST);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/redsys:config', $context)) {
            return false;
        }

        // multiple instances supported - different cost for different roles
        return true;
    }

    /**
     * We are a good plugin and don't invent our own UI/validation code path.
     *
     * @return boolean
     */
    public function use_standard_editing_ui() {
        return true;
    }

    /**
     * Add new instance of enrol plugin.
     * @param object $course
     * @param array $fields instance fields
     * @return int id of new instance, null if can not be created
     */
    public function add_instance($course, array $fields = null) {
        if ($fields && !empty($fields['cost'])) {
            $fields['cost'] = unformat_float($fields['cost']);
        }
        return parent::add_instance($course, $fields);
    }

    /**
     * Update instance of enrol plugin.
     * @param stdClass $instance
     * @param stdClass $data modified instance fields
     * @return boolean
     */
    public function update_instance($instance, $data) {
        if ($data) {
            $data->cost = unformat_float($data->cost);
        }
        return parent::update_instance($instance, $data);
    }

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $instance
     * @return string html text, usually a form in a text box
     */
    function enrol_page_hook(stdClass $instance) {
        global $CFG, $USER, $OUTPUT, $PAGE, $DB, $SESSION;

        ob_start();

        if ($DB->record_exists('user_enrolments', array('userid'=>$USER->id, 'enrolid'=>$instance->id))) {
            return ob_get_clean();
        }

        if ($instance->enrolstartdate != 0 && $instance->enrolstartdate > time()) {
            return ob_get_clean();
        }

        if ($instance->enrolenddate != 0 && $instance->enrolenddate < time()) {
            return ob_get_clean();
        }

        $course = $DB->get_record('course', array('id'=>$instance->courseid));
        $context = context_course::instance($course->id);

        $shortname = format_string($course->shortname, true, array('context' => $context));
        $strloginto = get_string("loginto", "", $shortname);
        $strcourses = get_string("courses");

        // Pass $view=true to filter hidden caps if the user cannot see them
        if ($users = get_users_by_capability($context, 'moodle/course:update', 'u.*', 'u.id ASC',
                                             '', '', '', '', false, true)) {
            $users = sort_by_roleassignment_authority($users, $context);
            $teacher = array_shift($users);
        } else {
            $teacher = false;
        }

        if ( (float) $instance->cost <= 0 ) {
            $cost = (float) $this->get_config('cost');
        } else {
            $cost = (float) $instance->cost;
        }

        if (abs($cost) < 0.01) { // no cost, other enrolment methods (instances) should be used
            echo '<p>'.get_string('nocost', 'enrol_redsys').'</p>';
        } else {

            // Calculate localised and "." cost, make sure we send redsys the same value,
            // please note redsys expects amount with 2 decimal places and "." separator.
            $localisedcost = $instance->cost;
            $cost = format_float($cost, 2, false);

            if (isguestuser()) { // force login only for guest user, not real users with guest role
                $wwwroot = $CFG->wwwroot;
                echo '<div class="mdl-align"><p>'.get_string('paymentrequired').'</p>';
                echo '<p><b>'.get_string('cost').": $instance->currency $localisedcost".'</b></p>';
                echo '<p><a href="'.$wwwroot.'/login/">'.get_string('loginsite').'</a></p>';
                echo '</div>';
            } else {
                //Sanitise some fields before building the redsys form
                $coursefullname  = format_string($course->fullname, true, array('context'=>$context));
                $courseshortname = $shortname;
                $userfullname    = fullname($USER);
                $userfirstname   = $USER->firstname;
                $userlastname    = $USER->lastname;
                $useraddress     = $USER->address;
                $usercity        = $USER->city;
                $instancename    = $this->get_instance_name($instance);

                include($CFG->dirroot.'/enrol/redsys/enrol.php');
            }

        }

        return $OUTPUT->box(ob_get_clean());
    }

    /**
     * Restore instance and map settings.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $course
     * @param int $oldid
     */
    public function restore_instance(restore_enrolments_structure_step $step, stdClass $data, $course, $oldid) {
        global $DB;
        if ($step->get_task()->get_target() == backup::TARGET_NEW_COURSE) {
            $merge = false;
        } else {
            $merge = array(
                'courseid'   => $data->courseid,
                'enrol'      => $this->get_name(),
                'roleid'     => $data->roleid,
                'cost'       => $data->cost,
                'currency'   => $data->currency,
            );
        }
        if ($merge and $instances = $DB->get_records('enrol', $merge, 'id')) {
            $instance = reset($instances);
            $instanceid = $instance->id;
        } else {
            $instanceid = $this->add_instance($course, (array)$data);
        }
        $step->set_mapping('enrol', $oldid, $instanceid);
    }

    /**
     * Restore user enrolment.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $instance
     * @param int $oldinstancestatus
     * @param int $userid
     */
    public function restore_user_enrolment(restore_enrolments_structure_step $step, $data, $instance, $userid, $oldinstancestatus) {
        $this->enrol_user($instance, $userid, null, $data->timestart, $data->timeend, $data->status);
    }

    /**
     * Gets an array of the user enrolment actions
     *
     * @param course_enrolment_manager $manager
     * @param stdClass $ue A user enrolment object
     * @return array An array of user_enrolment_actions
     */
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ($this->allow_unenrol($instance) && has_capability("enrol/redsys:unenrol", $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $strunenrol = get_string('unenrol', 'enrol');
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', $strunenrol),
                $strunenrol, $url, array('class' => 'unenrollink', 'rel' => $ue->id));
        }
        if ($this->allow_manage($instance) && has_capability("enrol/redsys:manage", $context)) {
            $url = new moodle_url('/enrol/editenrolment.php', $params);
            $stredit = get_string('editenrolment', 'enrol');
            $actions[] = new user_enrolment_action(new pix_icon('t/edit', $stredit, 'moodle', array('title' => $stredit)),
                $stredit, $url, array('class' => 'editenrollink', 'rel' => $ue->id));
        }
        return $actions;
    }

    public function cron() {
        $trace = new text_progress_trace();
        $this->process_expirations($trace);
    }

    /**
     * Return an array of valid options for the status.
     *
     * @return array
     */
    protected function get_status_options() {
        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        return $options;
    }

    /**
     * Return an array of valid options for the roleid.
     *
     * @param stdClass $instance
     * @param context $context
     * @return array
     */
    protected function get_roleid_options($instance, $context) {
        if ($instance->id) {
            $roles = get_default_enrol_roles($context, $instance->roleid);
        } else {
            $roles = get_default_enrol_roles($context, $this->get_config('roleid'));
        }
        return $roles;
    }


    /**
     * Add elements to the edit instance form.
     *
     * @param stdClass $instance
     * @param MoodleQuickForm $mform
     * @param context $context
     * @return bool
     */
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {

        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));
        $mform->setType('name', PARAM_TEXT);

        $options = $this->get_status_options();
        $mform->addElement('select', 'status', get_string('status', 'enrol_redsys'), $options);
        $mform->setDefault('status', $this->get_config('status'));

        $mform->addElement('text', 'cost', get_string('cost', 'enrol_redsys'), array('size' => 4));
        $mform->setType('cost', PARAM_RAW);
        $mform->setDefault('cost', format_float($this->get_config('cost'), 2, true));

        $redsyscurrencies = $this->get_currencies();
        $mform->addElement('select', 'currency', get_string('currency', 'enrol_redsys'), $redsyscurrencies);
        $mform->setDefault('currency', $this->get_config('currency'));

        $mform->addElement('select', 'customchar1', get_string('payment_type', 'enrol_redsys'), ['unique' => get_string('payment_unique_type', 'enrol_redsys'), 'recurrent' => get_string('payment_recurrent_type', 'enrol_redsys')]);
        $mform->setDefault('customchar1', $this->get_config('customchar1'));

        $roles = $this->get_roleid_options($instance, $context);
        $mform->addElement('select', 'roleid', get_string('assignrole', 'enrol_redsys'), $roles);
        $mform->setDefault('roleid', $this->get_config('roleid'));

        $options = array('optional' => true, 'defaultunit' => 86400);
        $mform->addElement('duration', 'enrolperiod', get_string('enrolperiod', 'enrol_redsys'), $options);
        $mform->setDefault('enrolperiod', $this->get_config('enrolperiod'));
        $mform->addHelpButton('enrolperiod', 'enrolperiod', 'enrol_redsys');

        $options = array('optional' => true);
        $mform->addElement('date_time_selector', 'enrolstartdate', get_string('enrolstartdate', 'enrol_redsys'), $options);
        $mform->setDefault('enrolstartdate', 0);
        $mform->addHelpButton('enrolstartdate', 'enrolstartdate', 'enrol_redsys');

        $options = array('optional' => true);
        $mform->addElement('date_time_selector', 'enrolenddate', get_string('enrolenddate', 'enrol_redsys'), $options);
        $mform->setDefault('enrolenddate', 0);
        $mform->addHelpButton('enrolenddate', 'enrolenddate', 'enrol_redsys');

        if (enrol_accessing_via_instance($instance)) {
            $warningtext = get_string('instanceeditselfwarningtext', 'core_enrol');
            $mform->addElement('static', 'selfwarn', get_string('instanceeditselfwarning', 'core_enrol'), $warningtext);
        }
    }

    /**
     * Perform custom validation of the data used to edit the instance.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @param object $instance The instance loaded from the DB
     * @param context $context The context of the instance we are editing
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK.
     * @return void
     */
    public function edit_instance_validation($data, $files, $instance, $context) {
        $errors = array();

        if (!empty($data['enrolenddate']) and $data['enrolenddate'] < $data['enrolstartdate']) {
            $errors['enrolenddate'] = get_string('enrolenddaterror', 'enrol_redsys');
        }

        $cost = str_replace(get_string('decsep', 'langconfig'), '.', $data['cost']);
        if (!is_numeric($cost)) {
            $errors['cost'] = get_string('costerror', 'enrol_redsys');
        }

        $validstatus = array_keys($this->get_status_options());
        $validcurrency = array_keys($this->get_currencies());
        $validroles = array_keys($this->get_roleid_options($instance, $context));
        $tovalidate = array(
            'name' => PARAM_TEXT,
            'status' => $validstatus,
            'currency' => $validcurrency,
            'roleid' => $validroles,
            'enrolperiod' => PARAM_INT,
            'enrolstartdate' => PARAM_INT,
            'enrolenddate' => PARAM_INT
        );

        $typeerrors = $this->validate_param_types($data, $tovalidate);
        $errors = array_merge($errors, $typeerrors);

        return $errors;
    }

    /**
     * Execute synchronisation.
     * @param progress_trace $trace
     * @return int exit code, 0 means ok
     */
    public function sync(progress_trace $trace) {
        $this->process_expirations($trace);
        return 0;
    }

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/redsys:config', $context);
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/redsys:config', $context);
    }

    /**
     * Do any enrolments need expiration processing.
     *
     * Plugins that want to call this functionality must implement 'expiredaction' config setting.
     *
     * @param progress_trace $trace
     * @param int $courseid one course, empty mean all
     * @return bool true if any data processed, false if not
     */
    public function process_expirations(progress_trace $trace, $courseid = null) {
        global $DB,$CFG;

        $name = $this->get_name();
        if (!enrol_is_enabled($name)) {
            $trace->finished();
            return false;
        }

        $processed = false;
        $params = array();
        $coursesql = "";
        if ($courseid) {
            $coursesql = "AND e.courseid = :courseid";
        }

        // Deal with expired accounts.
        $action = $this->get_config('expiredaction', ENROL_EXT_REMOVED_KEEP);

        if ($action == ENROL_EXT_REMOVED_UNENROL) {
            $instances = array();
            $sql = "SELECT ue.*, e.courseid, c.id AS contextid
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = :enrol)
                      JOIN {context} c ON (c.instanceid = e.courseid AND c.contextlevel = :courselevel)
                     WHERE ue.timeend > 0 AND ue.timeend < :now $coursesql";
            $params = array('now'=>time(), 'courselevel'=>CONTEXT_COURSE, 'enrol'=>$name, 'courseid'=>$courseid);

            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $ue) {
                if (!$processed) {
                    $trace->output("Starting processing of enrol_$name expirations...");
                    $processed = true;
                }
                if (empty($instances[$ue->enrolid])) {
                    $instances[$ue->enrolid] = $DB->get_record('enrol', array('id'=>$ue->enrolid));
                }
                $instance = $instances[$ue->enrolid];
                if (!$this->roles_protected()) {
                    // Let's just guess what extra roles are supposed to be removed.
                    if ($instance->roleid) {
                        role_unassign($instance->roleid, $ue->userid, $ue->contextid);
                    }
                }
                // The unenrol cleans up all subcontexts if this is the only course enrolment for this user.
                $this->unenrol_user($instance, $ue->userid);
                $trace->output("Unenrolling expired user $ue->userid from course $instance->courseid", 1);
            }
            $rs->close();
            unset($instances);

        }
        else if ($action == ENROL_EXT_REMOVED_SUSPENDNOROLES or $action == ENROL_EXT_REMOVED_SUSPEND) {
            $instances = array();
            $sql = "SELECT ue.*, e.courseid, co.id AS contextid, c.fullname as coursename, u.firstname as firstname, u.lastname as lastname
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = :enrol)
                      JOIN {course} c ON (c.id = e.courseid)
                      JOIN {user} u ON (u.id = ue.userid AND u.deleted = 0 AND u.suspended = 0)
                      JOIN {context} co ON (co.instanceid = e.courseid AND co.contextlevel = :courselevel)
                     WHERE ue.timeend > 0 AND ue.timeend < :now
                           AND ue.status = :useractive $coursesql";
            $params = array('now'=>time(), 'courselevel'=>CONTEXT_COURSE, 'useractive'=>ENROL_USER_ACTIVE, 'enrol'=>$name, 'courseid'=>$courseid);
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $ue) {
                if (!$processed) {
                    $trace->output("Starting processing of enrol_$name expirations : ".userdate(time(), '', $CFG->timezone));
                    $processed = true;
                }

                $trace->output($name.' enrolment expiry process COURSE :  '.$ue->coursename.", User:".$ue->firstname." ".$ue->lastname.", Enrolment end : ".userdate($ue->timeend, '', $CFG->timezone).'.');

                if (empty($instances[$ue->enrolid])) {
                    $instances[$ue->enrolid] = $DB->get_record('enrol', array('id'=>$ue->enrolid));
                }
                $instance = $instances[$ue->enrolid];


                //if payment_type == recurrent, then try to pay again
                if($instance->customchar1=='recurrent' && $this->process_payment_offline($instance,$ue,$trace))
                {
                    //process succesfull
                    if ($instance->enrolperiod) {
                        $timeend   = time() + $instance->enrolperiod;
                    }
                    $trace->output('New time Enrollment :  '.userdate($timeend, '', $CFG->timezone).'.');
                    $result = $this->edit_user_enrolment($ue->courseid, $ue->id, ENROL_USER_ACTIVE, $ue->timestart, $timeend);
                    $trace->output('Result New Enrollment :  '.$result);
                }
                else {

                    if ($action == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                        if (!$this->roles_protected()) {
                            // Let's just guess what roles should be removed.
                            $count = $DB->count_records('role_assignments', array('userid'=>$ue->userid, 'contextid'=>$ue->contextid));
                            if ($count == 1) {
                                role_unassign_all(array('userid'=>$ue->userid, 'contextid'=>$ue->contextid, 'component'=>'', 'itemid'=>0));

                            } else if ($count > 1 and $instance->roleid) {
                                role_unassign($instance->roleid, $ue->userid, $ue->contextid, '', 0);
                            }
                        }
                        // In any case remove all roles that belong to this instance and user.
                        role_unassign_all(array('userid'=>$ue->userid, 'contextid'=>$ue->contextid, 'component'=>'enrol_'.$name, 'itemid'=>$instance->id), true);
                        // Final cleanup of subcontexts if there are no more course roles.
                        if (0 == $DB->count_records('role_assignments', array('userid'=>$ue->userid, 'contextid'=>$ue->contextid))) {
                            role_unassign_all(array('userid'=>$ue->userid, 'contextid'=>$ue->contextid, 'component'=>'', 'itemid'=>0), true);
                        }
                    }

                    $this->update_user_enrol($instance, $ue->userid, ENROL_USER_SUSPENDED);
                    $trace->output("Suspending expired user $ue->userid in course $instance->courseid", 1);

                }

            }
            $rs->close();
            unset($instances);

        }
        else {
            // ENROL_EXT_REMOVED_KEEP means no changes.
        }

        if ($processed) {
            $trace->output("...finished processing of enrol_$name expirations");
        } else {
            $trace->output("No expired enrol_$name enrolments detected");
        }
        $trace->finished();

        return $processed;
    }

    /**
     * @param $data
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function process_request($data){
        global $DB,$PAGE,$CFG,$SESSION;

        $miObj = new RedsysAPI;
        $version = $data->Ds_SignatureVersion;
        $datos = $data->Ds_MerchantParameters;
        $signatureRecibida = $data->Ds_Signature;
        $kc = get_config('enrol_redsys','Ds_Merchant_Enc');
        $firma = $miObj->createMerchantSignatureNotif($kc,$datos);

        if (!($firma === $signatureRecibida)){
            die('Firma no valida');
        }

        $decodec = json_decode($miObj->decodeMerchantParameters($datos));

        $order = $SESSION->{$decodec->Ds_Order};
        $decodec->userid = $order->user;
        $decodec->courseid = $order->course;
        $decodec->instanceid = $order->instance;

        $user = $DB->get_record("user", array("id" => $decodec->userid), "*", MUST_EXIST);
        $course = $DB->get_record("course", array("id" => $decodec->courseid), "*", MUST_EXIST);
        $context = context_course::instance($course->id, MUST_EXIST);

        $PAGE->set_context($context);

        $plugin_instance = $DB->get_record("enrol", array("id" => $decodec->instanceid, "enrol" => "redsys", "status" => 0), "*", MUST_EXIST);
        $plugin = enrol_get_plugin('redsys');


        //Check the currency
        if ($decodec->Ds_Currency != $plugin_instance->currency) {
            \enrol_redsys\util::message_redsys_error_to_admin(
                "Currency does not match course settings, received: ".$decodec->Ds_Currency,
                $decodec);
            die;
        }

        // Make sure this transaction doesn't exist already.
        if ($existing = $DB->get_record("enrol_redsys", array("order_id" => $decodec->Ds_Order), "*", IGNORE_MULTIPLE)) {
            \enrol_redsys\util::message_redsys_error_to_admin("Transaction $decodec->Ds_Order is being repeated!", $decodec);
            die;
        }

        if (!$user = $DB->get_record('user', array('id'=>$decodec->userid))) {   // Check that user exists
            \enrol_redsys\util::message_redsys_error_to_admin("User $decodec->userid doesn't exist", $decodec);
            die;
        }

        if (!$course = $DB->get_record('course', array('id'=>$decodec->courseid))) { // Check that course exists
            \enrol_redsys\util::message_redsys_error_to_admin("Course $decodec->courseid doesn't exist", $decodec);
            die;
        }

        $coursecontext = context_course::instance($course->id, IGNORE_MISSING);


        // Check that amount paid is the correct amount
        if ( (float) $plugin_instance->cost <= 0 ) {
            $cost = (float) $plugin->get_config('cost');
        } else {
            $cost = (float) $plugin_instance->cost;
        }

        // Use the same rounding of floats as on the enrol form.
        $cost = number_format($cost,2,'','');

        if ($decodec->Ds_Amount < $cost) {
            \enrol_redsys\util::message_redsys_error_to_admin("Amount paid is not enough ($decodec->Ds_Amount < $cost))", $decodec);
            die;

        }

        $data = new stdClass();
        $data->date             = urldecode($decodec->Ds_Date);
        $data->hour             = urldecode($decodec->Ds_Hour);
        $data->secure_payment   = $decodec->Ds_SecurePayment;
        $data->amount           = $decodec->Ds_Amount;
        $data->currency         = $decodec->Ds_Currency;
        $data->order_id         = $decodec->Ds_Order;
        $data->merchant_code    = $decodec->Ds_MerchantCode;
        $data->terminal         = $decodec->Ds_Terminal;
        $data->response         = $decodec->Ds_Response;
        $data->transaction_type = $decodec->Ds_TransactionType;
        $data->merchant_data    = $decodec->Ds_MerchantData;
        $data->authorisation_code   = $decodec->Ds_AuthorisationCode;
        $data->expiry_date      = $decodec->Ds_ExpiryDate;
        $data->merchant_identifier  = $decodec->Ds_Merchant_Identifier;
        $data->consumer_language    = $decodec->Ds_ConsumerLanguage;
        $data->card_country     = $decodec->Ds_Card_Country;
        $data->card_brand       = $decodec->Ds_Card_Brand;
        $data->courseid         = $decodec->courseid;
        $data->userid           = $decodec->userid;
        $data->instanceid       = $decodec->instanceid;


        $DB->insert_record("enrol_redsys", $data);

        if ($plugin_instance->enrolperiod) {
            $timestart = time();
            $timeend   = $timestart + $plugin_instance->enrolperiod;
        } else {
            $timestart = 0;
            $timeend   = 0;
        }

        // Enrol user
        $plugin->enrol_user($plugin_instance, $user->id, $plugin_instance->roleid, $timestart, $timeend);


        // Pass $view=true to filter hidden caps if the user cannot see them
        if ($users = get_users_by_capability($context, 'moodle/course:update', 'u.*', 'u.id ASC',
            '', '', '', '', false, true)) {
            $users = sort_by_roleassignment_authority($users, $context);
            $teacher = array_shift($users);
        } else {
            $teacher = false;
        }

        $mailstudents = $plugin->get_config('mailstudents');
        $mailteachers = $plugin->get_config('mailteachers');
        $mailadmins   = $plugin->get_config('mailadmins');
        $shortname = format_string($course->shortname, true, array('context' => $context));

        if (!empty($mailstudents)) {
            $a = new stdClass();
            $a->coursename = format_string($course->fullname, true, array('context' => $coursecontext));
            $a->profileurl = "$CFG->wwwroot/user/view.php?id=$user->id";

            $eventdata = new \core\message\message();
            $eventdata->courseid          = $course->id;
            $eventdata->modulename        = 'moodle';
            $eventdata->component         = 'enrol_redsys';
            $eventdata->name              = 'redsys_enrolment';
            $eventdata->userfrom          = empty($teacher) ? core_user::get_noreply_user() : $teacher;
            $eventdata->userto            = $user;
            $eventdata->subject           = get_string("enrolmentnew", 'enrol', $shortname);
            $eventdata->fullmessage       = get_string('welcometocoursetext', '', $a);
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml   = '';
            $eventdata->smallmessage      = '';
            message_send($eventdata);

        }

        if (!empty($mailteachers) && !empty($teacher)) {
            $a->course = format_string($course->fullname, true, array('context' => $coursecontext));
            $a->user = fullname($user);

            $eventdata = new \core\message\message();
            $eventdata->courseid          = $course->id;
            $eventdata->modulename        = 'moodle';
            $eventdata->component         = 'enrol_redsys';
            $eventdata->name              = 'redsys_enrolment';
            $eventdata->userfrom          = $user;
            $eventdata->userto            = $teacher;
            $eventdata->subject           = get_string("enrolmentnew", 'enrol', $shortname);
            $eventdata->fullmessage       = get_string('enrolmentnewuser', 'enrol', $a);
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml   = '';
            $eventdata->smallmessage      = '';
            message_send($eventdata);
        }

        if (!empty($mailadmins)) {
            $a->course = format_string($course->fullname, true, array('context' => $coursecontext));
            $a->user = fullname($user);
            $admins = get_admins();
            foreach ($admins as $admin) {
                $eventdata = new \core\message\message();
                $eventdata->courseid          = $course->id;
                $eventdata->modulename        = 'moodle';
                $eventdata->component         = 'enrol_redsys';
                $eventdata->name              = 'redsys_enrolment';
                $eventdata->userfrom          = $user;
                $eventdata->userto            = $admin;
                $eventdata->subject           = get_string("enrolmentnew", 'enrol', $shortname);
                $eventdata->fullmessage       = get_string('enrolmentnewuser', 'enrol', $a);
                $eventdata->fullmessageformat = FORMAT_PLAIN;
                $eventdata->fullmessagehtml   = '';
                $eventdata->smallmessage      = '';
                message_send($eventdata);
            }
        }
    }

    /**
     * External function that updates a given user enrolment.
     *
     * @param int $courseid The course ID.
     * @param int $ueid The user enrolment ID.
     * @param int $status The enrolment status.
     * @param int $timestart Enrolment start timestamp.
     * @param int $timeend Enrolment end timestamp.
     * @return array An array consisting of the processing result, errors and form output, if available.
     */
    public static function edit_user_enrolment($courseid, $ueid, $status, $timestart = 0, $timeend = 0) {
        global $CFG, $DB, $PAGE;
        require_once("$CFG->libdir/externallib.php");

        $course = get_course($courseid);
        $context = context_course::instance($course->id);
        external_api::validate_context($context);

        $userenrolment = $DB->get_record('user_enrolments', ['id' => $ueid ], '*', MUST_EXIST);
        $userenroldata = [
            'status' => $status,
            'timestart' => $timestart,
            'timeend' => $timeend,
        ];

        $result = false;
        $errors = [];

        // Validate data against the edit user enrolment form.
        $instance = $DB->get_record('enrol', ['id' => $userenrolment->enrolid], '*', MUST_EXIST);
        $plugin = enrol_get_plugin($instance->enrol);
        require_once("$CFG->dirroot/enrol/editenrolment_form.php");
        $customformdata = [
            'ue' => $userenrolment,
            'modal' => true,
            'enrolinstancename' => $plugin->get_instance_name($instance)
        ];
        $mform = new \enrol_user_enrolment_form(null, $customformdata, 'post', '', null, true, $userenroldata);
        $mform->set_data($userenroldata);
        $validationerrors = $mform->validation($userenroldata, null);
        if (empty($validationerrors)) {
            require_once($CFG->dirroot . '/enrol/locallib.php');
            $manager = new course_enrolment_manager($PAGE, $course);
            $result = $manager->edit_enrolment($userenrolment, (object)$userenroldata);
        } else {
            foreach ($validationerrors as $key => $errormessage) {
                $errors[] = (object)[
                    'key' => $key,
                    'message' => $errormessage
                ];
            }
        }

        return $result;
    }

    public function process_payment_offline($instance,$ue,$trace)
    {
        global $DB,$CFG;
        require_once($CFG->dirroot."/enrol/redsys/lib/redsysHMAC256_API_WS_PHP_7.0.0/apiRedsysWs.php");
        require_once($CFG->dirroot."/lib/soaplib.php");
        $TPV = new RedsysAPIWs;

        $sql = "SELECT merchant_identifier,expiry_date FROM {enrol_redsys} WHERE courseid = :courseid AND userid = :userid AND instanceid = :instanceid ORDER BY id LIMIT 1";
        $params = array('courseid' => $ue->courseid, 'userid' => $ue->userid, 'instanceid' => $instance->id);
        $merchant = $DB->get_record_sql($sql, $params);

        $order_number = $this->create_order_number($ue->userid,$ue->courseid);

        $DATOS_ENTRADA = "<DATOSENTRADA>";
        $DATOS_ENTRADA .= "<DS_MERCHANT_MERCHANTCODE>".$this->get_config('Ds_Merchant_MerchantCode')."</DS_MERCHANT_MERCHANTCODE>";
        $DATOS_ENTRADA .= "<DS_MERCHANT_TERMINAL>".$this->get_config('Ds_Merchant_Terminal')."</DS_MERCHANT_TERMINAL>";
        $DATOS_ENTRADA .= "<DS_MERCHANT_TRANSACTIONTYPE>A</DS_MERCHANT_TRANSACTIONTYPE>";
        $DATOS_ENTRADA .= "<DS_MERCHANT_AMOUNT>".number_format($instance->cost,2,'','')."</DS_MERCHANT_AMOUNT>";
        $DATOS_ENTRADA .= "<DS_MERCHANT_CURRENCY>$instance->currency</DS_MERCHANT_CURRENCY>";
        $DATOS_ENTRADA .= "<DS_MERCHANT_ORDER>".$order_number."</DS_MERCHANT_ORDER>";
        $DATOS_ENTRADA .= "<DS_MERCHANT_IDENTIFIER>$merchant->merchant_identifier</DS_MERCHANT_IDENTIFIER>";
        $DATOS_ENTRADA .= "</DATOSENTRADA>";

        $XML = "<REQUEST>";
        $XML .= $DATOS_ENTRADA;
        $XML .= "<DS_SIGNATUREVERSION>HMAC_SHA256_V1</DS_SIGNATUREVERSION>";
        $XML .= '<DS_SIGNATURE>'.$TPV->createMerchantSignatureHostToHost($this->get_config('Ds_Merchant_Enc'), $DATOS_ENTRADA).'</DS_SIGNATURE>';
        $XML .= '</REQUEST>';

        try {
            $CLIENTE = new soapClient( $this->get_config('url_recurrent'));
        }
        catch (SoapFault $body)
        {
        	throw new \Exception('SOAP FAULT');
        }

        $RESPONSE = $CLIENTE->trataPeticion(array("datoEntrada"=>$XML));

        if(isset($RESPONSE->trataPeticionReturn)) {
            $XML_RETORNO = new SimpleXMLElement($RESPONSE->trataPeticionReturn);
            if(isset($XML_RETORNO->OPERACION->Ds_Response)) {
                $RESPUESTA = (int) $XML_RETORNO->OPERACION->Ds_Response;
                if(($RESPUESTA >= 0) && ($RESPUESTA <= 99)) {

                    // Procesamos el cobro aceptado
                    $data_xml = (object)(array)$XML_RETORNO->OPERACION;

                    $data = new stdClass();
                    $data->date             = date("m/d/Y");
                    $data->hour             = date("h:i:s");
                    $data->secure_payment   = $data_xml->Ds_SecurePayment;
                    $data->amount           = $data_xml->Ds_Amount;
                    $data->currency         = $data_xml->Ds_Currency;
                    $data->order_id         = $data_xml->Ds_Order;
                    $data->merchant_code    = $data_xml->Ds_MerchantCode;
                    $data->terminal         = $data_xml->Ds_Terminal;
                    $data->response         = $data_xml->Ds_Response;
                    $data->transaction_type = $data_xml->Ds_TransactionType;
                    //$data->merchant_data    = $data_xml->Ds_MerchantData;
                    $data->authorisation_code   = $data_xml->Ds_AuthorisationCode;
                    $data->expiry_date      = $merchant->expiry_date;
                    $data->merchant_identifier  = $data_xml->Ds_Merchant_Identifier;
                    $data->consumer_language    = $data_xml->Ds_Language;
                    $data->card_country     = $data_xml->Ds_Card_Country;
                    $data->card_brand       = $data_xml->Ds_Card_Brand;
                    $data->courseid         = $ue->courseid;
                    $data->userid           = $ue->userid;
                    $data->instanceid       = $instance->id;

                    $trace->output('Payment Succesfull Order:  '.$data->order_id);

                    $DB->insert_record("enrol_redsys", $data);


                    return true;
                }
                else{
                    $trace->output('Payment Respuesta diferente');
                }
            }
        }
        else {
            $trace->output('No trata peticion');
        }
        return false;
    }

    public function create_order_number($user_id,$course_id){
        $order_number = "{$user_id}-{$course_id}-";
        $len = str_pad("", 12- strlen($order_number), '9', STR_PAD_LEFT);
        $ran_number = rand(1, $len);
        $return = $order_number.$ran_number;
        return $return;

    }
}
