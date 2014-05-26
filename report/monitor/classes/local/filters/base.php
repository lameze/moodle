<?php

namespace report_monitor\local\filters;

defined('MOODLE_INTERNAL') || die();

abstract class base {

    /**
     * Add elements to the new monitor form.
     *
     * @param $mform
     */
    abstract public function add_form_elements(&$mform);

    /**
     * Return form validation errors.
     *
     * @param $data
     * @param $files
     *
     * @return array errors if any.
     */
    public function validate_data($data, $files) {
        return array();
    }

    /**
     * Called at the end of a request, can be used to update custom flags.
     *
     * @return bool
     */
    public function dispose() {
        return true;
    }

    // More processing apis here.
}
