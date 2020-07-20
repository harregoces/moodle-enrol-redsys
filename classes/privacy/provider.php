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


namespace enrol_redsys\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\core_userlist_provider {

    public static function get_metadata(collection $collection) : collection {
        $collection->add_external_location_link(
            'redsys.com',
            [
                'os0'        => 'privacy:metadata:enrol_redsys:redsys_com:os0',
                'custom'     => 'privacy:metadata:enrol_redsys:redsys_com:custom',
                'first_name' => 'privacy:metadata:enrol_redsys:redsys_com:first_name',
                'last_name'  => 'privacy:metadata:enrol_redsys:redsys_com:last_name',
                'address'    => 'privacy:metadata:enrol_redsys:redsys_com:address',
                'city'       => 'privacy:metadata:enrol_redsys:redsys_com:city',
                'email'      => 'privacy:metadata:enrol_redsys:redsys_com:email',
                'country'    => 'privacy:metadata:enrol_redsys:redsys_com:country',
            ],
            'privacy:metadata:enrol_redsys:redsys_com'
        );

        // The enrol_redsys has a DB table that contains user data.
        $collection->add_database_table(
                'enrol_redsys',
                [
                    'date'            => 'privacy:metadata:enrol_redsys:enrol_redsys:date',
                    'hour'      => 'privacy:metadata:enrol_redsys:enrol_redsys:hour',
                    'secure_payment'         => 'privacy:metadata:enrol_redsys:enrol_redsys:secure_payment',
                    'amount'           => 'privacy:metadata:enrol_redsys:enrol_redsys:amount',
                    'currency'            => 'privacy:metadata:enrol_redsys:enrol_redsys:currency',
                    'order_id'              => 'privacy:metadata:enrol_redsys:enrol_redsys:order_id',
                    'merchant_code'          => 'privacy:metadata:enrol_redsys:enrol_redsys:merchant_code',
                    'terminal'                => 'privacy:metadata:enrol_redsys:enrol_redsys:terminal',
                    'response'                 => 'privacy:metadata:enrol_redsys:enrol_redsys:response',
                    'transaction_type' => 'privacy:metadata:enrol_redsys:enrol_redsys:transaction_type',
                    'merchant_data'      => 'privacy:metadata:enrol_redsys:enrol_redsys:merchant_data',
                    'authorisation_code'      => 'privacy:metadata:enrol_redsys:enrol_redsys:authorisation_code',
                    'expiry_date'         => 'privacy:metadata:enrol_redsys:enrol_redsys:expiry_date',
                    'merchant_identifier'              => 'privacy:metadata:enrol_redsys:enrol_redsys:merchant_identifier',
                    'consumer_language'       => 'privacy:metadata:enrol_redsys:enrol_redsys:consumer_language',
                    'card_country'        => 'privacy:metadata:enrol_redsys:enrol_redsys:card_country',
                    'card_brand'         => 'privacy:metadata:enrol_redsys:enrol_redsys:card_brand',
                    'courseid'         => 'privacy:metadata:enrol_redsys:enrol_redsys:courseid',
                    'userid'         => 'privacy:metadata:enrol_redsys:enrol_redsys:userid',
                    'instanceid'         => 'privacy:metadata:enrol_redsys:enrol_redsys:instanceid'
                ],
                'privacy:metadata:enrol_redsys:enrol_redsys'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();

        // Values of ep.receiver_email and ep.business are already normalised to lowercase characters by redsys,
        // therefore there is no need to use LOWER() on them in the following query.
        $sql = "SELECT ctx.id
                  FROM {enrol_redsys} ep
                  JOIN {enrol} e ON ep.instanceid = e.id
                  JOIN {context} ctx ON e.courseid = ctx.instanceid AND ctx.contextlevel = :contextcourse
                  JOIN {user} u ON u.id = ep.userid OR LOWER(u.email) = ep.receiver_email OR LOWER(u.email) = ep.business
                 WHERE u.id = :userid";
        $params = [
            'contextcourse' => CONTEXT_COURSE,
            'userid'        => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_course) {
            return;
        }

        // Values of ep.receiver_email and ep.business are already normalised to lowercase characters by redsys,
        // therefore there is no need to use LOWER() on them in the following query.
        $sql = "SELECT u.id
                  FROM {enrol_redsys} ep
                  JOIN {enrol} e ON ep.instanceid = e.id
                  JOIN {user} u ON ep.userid = u.id OR LOWER(u.email) = ep.receiver_email OR LOWER(u.email) = ep.business
                 WHERE e.courseid = :courseid";
        $params = ['courseid' => $context->instanceid];

        $userlist->add_from_sql('id', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        // Values of ep.receiver_email and ep.business are already normalised to lowercase characters by redsys,
        // therefore there is no need to use LOWER() on them in the following query.
        $sql = "SELECT ep.*
                  FROM {enrol_redsys} ep
                  JOIN {enrol} e ON ep.instanceid = e.id
                  JOIN {context} ctx ON e.courseid = ctx.instanceid AND ctx.contextlevel = :contextcourse
                  JOIN {user} u ON u.id = ep.userid OR LOWER(u.email) = ep.receiver_email OR LOWER(u.email) = ep.business
                 WHERE ctx.id {$contextsql} AND u.id = :userid
              ORDER BY e.courseid";

        $params = [
            'contextcourse' => CONTEXT_COURSE,
            'userid'        => $user->id,
            'emailuserid'   => $user->id,
        ];
        $params += $contextparams;

        // Reference to the course seen in the last iteration of the loop. By comparing this with the current record, and
        // because we know the results are ordered, we know when we've moved to the redsys transactions for a new course
        // and therefore when we can export the complete data for the last course.
        $lastcourseid = null;

        $strtransactions = get_string('transactions', 'enrol_redsys');
        $transactions = [];
        $redsysrecords = $DB->get_recordset_sql($sql, $params);
        foreach ($redsysrecords as $redsysrecord) {
            if ($lastcourseid != $redsysrecord->courseid) {
                if (!empty($transactions)) {
                    $coursecontext = \context_course::instance($redsysrecord->courseid);
                    writer::with_context($coursecontext)->export_data(
                            [$strtransactions],
                            (object) ['transactions' => $transactions]
                    );
                }
                $transactions = [];
            }

            $transaction = (object) [
                'receiver_id'         => $redsysrecord->receiver_id,
                'item_name'           => $redsysrecord->item_name,
                'userid'              => $redsysrecord->userid,
                'memo'                => $redsysrecord->memo,
                'tax'                 => $redsysrecord->tax,
                'option_name1'        => $redsysrecord->option_name1,
                'option_selection1_x' => $redsysrecord->option_selection1_x,
                'option_name2'        => $redsysrecord->option_name2,
                'option_selection2_x' => $redsysrecord->option_selection2_x,
                'payment_status'      => $redsysrecord->payment_status,
                'pending_reason'      => $redsysrecord->pending_reason,
                'reason_code'         => $redsysrecord->reason_code,
                'txn_id'              => $redsysrecord->txn_id,
                'parent_txn_id'       => $redsysrecord->parent_txn_id,
                'payment_type'        => $redsysrecord->payment_type,
                'timeupdated'         => \core_privacy\local\request\transform::datetime($redsysrecord->timeupdated),
            ];
            if ($redsysrecord->userid == $user->id) {
                $transaction->userid = $redsysrecord->userid;
            }
            if ($redsysrecord->business == \core_text::strtolower($user->email)) {
                $transaction->business = $redsysrecord->business;
            }
            if ($redsysrecord->receiver_email == \core_text::strtolower($user->email)) {
                $transaction->receiver_email = $redsysrecord->receiver_email;
            }

            $transactions[] = $redsysrecord;

            $lastcourseid = $redsysrecord->courseid;
        }
        $redsysrecords->close();

        // The data for the last activity won't have been written yet, so make sure to write it now!
        if (!empty($transactions)) {
            $coursecontext = \context_course::instance($redsysrecord->courseid);
            writer::with_context($coursecontext)->export_data(
                    [$strtransactions],
                    (object) ['transactions' => $transactions]
            );
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_course) {
            return;
        }

        $DB->delete_records('enrol_redsys', array('courseid' => $context->instanceid));
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        $contexts = $contextlist->get_contexts();
        $courseids = [];
        foreach ($contexts as $context) {
            if ($context instanceof \context_course) {
                $courseids[] = $context->instanceid;
            }
        }

        list($insql, $inparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);

        $select = "userid = :userid AND courseid $insql";
        $params = $inparams + ['userid' => $user->id];
        $DB->delete_records_select('enrol_redsys', $select, $params);

        // We do not want to delete the payment record when the user is just the receiver of payment.
        // In that case, we just delete the receiver's info from the transaction record.

        $select = "business = :business AND courseid $insql";
        $params = $inparams + ['business' => \core_text::strtolower($user->email)];
        $DB->set_field_select('enrol_redsys', 'business', '', $select, $params);

        $select = "receiver_email = :receiver_email AND courseid $insql";
        $params = $inparams + ['receiver_email' => \core_text::strtolower($user->email)];
        $DB->set_field_select('enrol_redsys', 'receiver_email', '', $select, $params);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        $userids = $userlist->get_userids();

        list($usersql, $userparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        $params = ['courseid' => $context->instanceid] + $userparams;

        $select = "courseid = :courseid AND userid $usersql";
        $DB->delete_records_select('enrol_redsys', $select, $params);

        // We do not want to delete the payment record when the user is just the receiver of payment.
        // In that case, we just delete the receiver's info from the transaction record.

        $select = "courseid = :courseid AND business IN (SELECT LOWER(email) FROM {user} WHERE id $usersql)";
        $DB->set_field_select('enrol_redsys', 'business', '', $select, $params);

        $select = "courseid = :courseid AND receiver_email IN (SELECT LOWER(email) FROM {user} WHERE id $usersql)";
        $DB->set_field_select('enrol_redsys', 'receiver_email', '', $select, $params);
    }
}
