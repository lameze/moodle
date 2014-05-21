<?php

namespace report_trigger\local\filters;

defined('MOODLE_INTERNAL') || die();

class frequency extends base {

    /**
     * Add elements to the new trigger form.
     *
     * @param $mform
     */
    public function add_form_elements(&$mform) {
    }

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

    // More processing apis here.
}
