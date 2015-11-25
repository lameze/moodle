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
 * User class
 *
 * @package    core
 * @copyright  2013 Rajesh Taneja <rajesh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * User class to access user details.
 *
 * @todo       move api's from user/lib.php and depreciate old ones.
 * @package    core
 * @copyright  2013 Rajesh Taneja <rajesh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_user {
    /**
     * No reply user id.
     */
    const NOREPLY_USER = -10;

    /**
     * Support user id.
     */
    const SUPPORT_USER = -20;

    /** @var stdClass keep record of noreply user */
    public static $noreplyuser = false;

    /** @var stdClass keep record of support user */
    public static $supportuser = false;

    /** @var null store the user field definition cache. */
    public static $definitioncache = null;

    /**
     * Return user object from db or create noreply or support user,
     * if userid matches corse_user::NOREPLY_USER or corse_user::SUPPORT_USER
     * respectively. If userid is not found, then return false.
     *
     * @param int $userid user id
     * @param string $fields A comma separated list of user fields to be returned, support and noreply user
     *                       will not be filtered by this.
     * @param int $strictness IGNORE_MISSING means compatible mode, false returned if user not found, debug message if more found;
     *                        IGNORE_MULTIPLE means return first user, ignore multiple user records found(not recommended);
     *                        MUST_EXIST means throw an exception if no user record or multiple records found.
     * @return stdClass|bool user record if found, else false.
     * @throws dml_exception if user record not found and respective $strictness is set.
     */
    public static function get_user($userid, $fields = '*', $strictness = IGNORE_MISSING) {
        global $DB;

        // If noreply user then create fake record and return.
        switch ($userid) {
            case self::NOREPLY_USER:
                return self::get_noreply_user();
                break;
            case self::SUPPORT_USER:
                return self::get_support_user();
                break;
            default:
                return $DB->get_record('user', array('id' => $userid), $fields, $strictness);
        }
    }


    /**
     * Return user object from db based on their username.
     *
     * @param string $username The username of the user searched.
     * @param string $fields A comma separated list of user fields to be returned, support and noreply user.
     * @param int $mnethostid The id of the remote host.
     * @param int $strictness IGNORE_MISSING means compatible mode, false returned if user not found, debug message if more found;
     *                        IGNORE_MULTIPLE means return first user, ignore multiple user records found(not recommended);
     *                        MUST_EXIST means throw an exception if no user record or multiple records found.
     * @return stdClass|bool user record if found, else false.
     * @throws dml_exception if user record not found and respective $strictness is set.
     */
    public static function get_user_by_username($username, $fields = '*', $mnethostid = null, $strictness = IGNORE_MISSING) {
        global $DB, $CFG;

        // Because we use the username as the search criteria, we must also restrict our search based on mnet host.
        if (empty($mnethostid)) {
            // If empty, we restrict to local users.
            $mnethostid = $CFG->mnet_localhost_id;
        }

        return $DB->get_record('user', array('username' => $username, 'mnethostid' => $mnethostid), $fields, $strictness);
    }

    /**
     * Helper function to return dummy noreply user record.
     *
     * @return stdClass
     */
    protected static function get_dummy_user_record() {
        global $CFG;

        $dummyuser = new stdClass();
        $dummyuser->id = self::NOREPLY_USER;
        $dummyuser->email = $CFG->noreplyaddress;
        $dummyuser->firstname = get_string('noreplyname');
        $dummyuser->username = 'noreply';
        $dummyuser->lastname = '';
        $dummyuser->confirmed = 1;
        $dummyuser->suspended = 0;
        $dummyuser->deleted = 0;
        $dummyuser->picture = 0;
        $dummyuser->auth = 'manual';
        $dummyuser->firstnamephonetic = '';
        $dummyuser->lastnamephonetic = '';
        $dummyuser->middlename = '';
        $dummyuser->alternatename = '';
        $dummyuser->imagealt = '';
        return $dummyuser;
    }

    /**
     * Return noreply user record, this is currently used in messaging
     * system only for sending messages from noreply email.
     * It will return record of $CFG->noreplyuserid if set else return dummy
     * user object with hard-coded $user->emailstop = 1 so noreply can be sent to user.
     *
     * @return stdClass user record.
     */
    public static function get_noreply_user() {
        global $CFG;

        if (!empty(self::$noreplyuser)) {
            return self::$noreplyuser;
        }

        // If noreply user is set then use it, else create one.
        if (!empty($CFG->noreplyuserid)) {
            self::$noreplyuser = self::get_user($CFG->noreplyuserid);
        }

        if (empty(self::$noreplyuser)) {
            self::$noreplyuser = self::get_dummy_user_record();
            self::$noreplyuser->maildisplay = '1'; // Show to all.
        }
        self::$noreplyuser->emailstop = 1; // Force msg stop for this user.
        return self::$noreplyuser;
    }

    /**
     * Return support user record, this is currently used in messaging
     * system only for sending messages to support email.
     * $CFG->supportuserid is set then returns user record
     * $CFG->supportemail is set then return dummy record with $CFG->supportemail
     * else return admin user record with hard-coded $user->emailstop = 0, so user
     * gets support message.
     *
     * @return stdClass user record.
     */
    public static function get_support_user() {
        global $CFG;

        if (!empty(self::$supportuser)) {
            return self::$supportuser;
        }

        // If custom support user is set then use it, else if supportemail is set then use it, else use noreply.
        if (!empty($CFG->supportuserid)) {
            self::$supportuser = self::get_user($CFG->supportuserid, '*', MUST_EXIST);
        }

        // Try sending it to support email if support user is not set.
        if (empty(self::$supportuser) && !empty($CFG->supportemail)) {
            self::$supportuser = self::get_dummy_user_record();
            self::$supportuser->id = self::SUPPORT_USER;
            self::$supportuser->email = $CFG->supportemail;
            if ($CFG->supportname) {
                self::$supportuser->firstname = $CFG->supportname;
            }
            self::$supportuser->username = 'support';
            self::$supportuser->maildisplay = '1'; // Show to all.
        }

        // Send support msg to admin user if nothing is set above.
        if (empty(self::$supportuser)) {
            self::$supportuser = get_admin();
        }

        // Unset emailstop to make sure support message is sent.
        self::$supportuser->emailstop = 0;
        return self::$supportuser;
    }

    /**
     * Reset self::$noreplyuser and self::$supportuser.
     * This is only used by phpunit, and there is no other use case for this function.
     * Please don't use it outside phpunit.
     */
    public static function reset_internal_users() {
        if (PHPUNIT_TEST) {
            self::$noreplyuser = false;
            self::$supportuser = false;
        } else {
            debugging('reset_internal_users() should not be used outside phpunit.', DEBUG_DEVELOPER);
        }
    }

    /**
     * Return true is user id is greater than self::NOREPLY_USER and
     * alternatively check db.
     *
     * @param int $userid user id.
     * @param bool $checkdb if true userid will be checked in db. By default it's false, and
     *                      userid is compared with NOREPLY_USER for performance.
     * @return bool true is real user else false.
     */
    public static function is_real_user($userid, $checkdb = false) {
        global $DB;

        if ($userid < 0) {
            return false;
        }
        if ($checkdb) {
            return $DB->record_exists('user', array('id' => $userid));
        } else {
            return true;
        }
    }

    /**
     * Check if the given user is an active user in the site.
     *
     * @param  stdClass  $user         user object
     * @param  boolean $checksuspended whether to check if the user has the account suspended
     * @param  boolean $checknologin   whether to check if the user uses the nologin auth method
     * @throws moodle_exception
     * @since  Moodle 3.0
     */
    public static function require_active_user($user, $checksuspended = false, $checknologin = false) {

        if (!self::is_real_user($user->id)) {
            throw new moodle_exception('invaliduser', 'error');
        }

        if ($user->deleted) {
            throw new moodle_exception('userdeleted');
        }

        if (empty($user->confirmed)) {
            throw new moodle_exception('usernotconfirmed', 'moodle', '', $user->username);
        }

        if (isguestuser($user)) {
            throw new moodle_exception('guestsarenotallowed', 'error');
        }

        if ($checksuspended and $user->suspended) {
            throw new moodle_exception('suspended', 'auth');
        }

        if ($checknologin and $user->auth == 'nologin') {
            throw new moodle_exception('suspended', 'auth');
        }
    }

    /**
     * Validate user data.
     *
     * @param stdClass $data user data object to be validated.
     *
     * @return array|true $errors array of errors found on the user object, true otherwise.
     */
    public static function validate(stdClass $data) {

        // Get all user profile fields definition.
        $properties = static::properties_definition();

        foreach ($data as $property => $value) {
            try {
                if (isset($properties[$property])) {
                    validate_param($value, $properties[$property]['type'], $properties[$property]['null']);
                }

                // Check that the value is part of a list of allowed values.
                if (isset($properties[$property]['choices']) && isset($data->$property) &&
                    !array_key_exists($data->$property, $properties[$property]['choices'])) {

                    throw new invalid_parameter_exception($value);
                }
            } catch (invalid_parameter_exception $e) {
                $errors[$property] = $e->getMessage();
                continue;
            }
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Definition of user profile fields and type of parameter, used for data validation.
     *
     * Example:
     *
     * array(
     *     'property_name' => array(
     *          'null' => NULL_ALLOWED,      // Defaults to NULL_NOT_ALLOWED. Takes NULL_NOT_ALLOWED or NULL_ALLOWED.
     *          'type' => PARAM_TYPE,        // Mandatory.
     *          'choices' => array(1, 2, 3)  // An array of accepted values.
     *     )
     * )
     *
     * @return array list of fields with the expected params and list of choices (if any).
     */
    protected static function properties_definition() {

        if (self::$definitioncache !== null) {
            return self::$definitioncache;
        }

        global $CFG;

        /**
         * Array of user fields properties and expected parameters.
         * Every new field on the user table should be added here otherwise we it won't be validated.
         */
        $field = array();
        $field['auth'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_NOTAGS);
        $field['confirmed'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_BOOL);
        $field['policyagreed'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_BOOL);
        $field['deleted'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_BOOL);
        $field['suspended'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_BOOL);
        $field['mnethostid'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_BOOL);
        $field['username'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_USERNAME);
        $field['password'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_NOTAGS);
        $field['idnumber'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_NOTAGS);
        $field['firstname'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_NOTAGS);
        $field['lastname'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_NOTAGS);
        $field['surname'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_NOTAGS);
        $field['email'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_RAW_TRIMMED);
        $field['emailstop'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_INT);
        $field['icq'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_NOTAGS);
        $field['skype'] = array('null' => NULL_ALLOWED, 'type' => PARAM_NOTAGS);
        $field['aim'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_NOTAGS);
        $field['yahoo'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_NOTAGS);
        $field['msn'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_NOTAGS);
        $field['phone1'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_NOTAGS);
        $field['phone2'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_NOTAGS);
        $field['institution'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_TEXT);
        $field['department'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_TEXT);
        $field['address'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_TEXT);
        $field['city'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_TEXT);
        $field['country'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_TEXT,
            'choices' => array_merge(array('' => ''), get_string_manager()->get_list_of_countries()));
        $field['lang'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_TEXT,
            'choices' => array_merge(array('' => ''), get_string_manager()->get_list_of_translations()));
        $field['calendartype'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_NOTAGS,
            'choices' => array_merge(array('' => ''), \core_calendar\type_factory::get_list_of_calendar_types()));
        $field['theme'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_NOTAGS,
            'choices' => array_merge(array('' => ''), get_list_of_themes()));
        $field['timezones'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_TEXT,
            'choices' => core_date::get_list_of_timezones());
        $field['firstaccess'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_INT);
        $field['lastaccess'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_INT);
        $field['lastlogin'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_INT);
        $field['currentlogin'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_INT);
        $field['lastip'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_NOTAGS);
        $field['secret'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_TEXT);
        $field['picture'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_INT);
        $field['url'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_URL);
        $field['description'] = array('null' => NULL_ALLOWED, 'type' => PARAM_CLEANHTML);
        $field['descriptionformat'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_INT);
        $field['mailformat'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_INT);
        $field['maildigest'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_INT);
        $field['maildisplay'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_INT);
        $field['autosubscribe'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_INT);
        $field['trackforums'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_INT);
        $field['timecreated'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_INT);
        $field['timemodified'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_INT);
        $field['trustbitmask'] = array('null' => NULL_NOT_ALLOWED, 'type' => PARAM_INT);
        $field['imagealt'] = array('null' => NULL_ALLOWED, 'type' => PARAM_TEXT);
        $field['lastnamephonetic'] = array('null' => NULL_ALLOWED, 'type' => PARAM_NOTAGS);
        $field['firstnamephonetic'] = array('null' => NULL_ALLOWED, 'type' => PARAM_NOTAGS);
        $field['middlename'] = array('null' => NULL_ALLOWED, 'type' => PARAM_NOTAGS);
        $field['alternatename'] = array('null' => NULL_ALLOWED, 'type' => PARAM_NOTAGS);


        // Add filters to enabled user name fields if there are any.
        require_once($CFG->dirroot . '/user/editlib.php');

        $enabledusernamefields = useredit_get_enabled_name_fields();
        if (!empty($disabledusernamefields)) {
            foreach ($enabledusernamefields as $fieldname) {
                $field[$fieldname] = array('null' => NULL_ALLOWED, 'type' => PARAM_NOTAGS);
            }
        }

        // Add filters to disabled user name fields if there are any.
        $disabledusernamefields = useredit_get_disabled_name_fields($enabledusernamefields);
        if (!empty($disabledusernamefields)) {
            foreach ($disabledusernamefields as $fieldname) {
                $field[$fieldname] = array('null' => NULL_ALLOWED, 'type' => PARAM_NOTAGS);
            }
        }
        self::$definitioncache = $field;

        return self::$definitioncache;
    }

    /**
     * Clean the definition cache.
     *
     * During unit tests we need to be able to reset all caches so that each new test starts in a known state.
     * Intended for use only for testing, phpunit calls this before every test.
     */
    public static function reset_caches() {
        self::$definitioncache = null;
    }
}
