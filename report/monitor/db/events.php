<?php

$observers = array(

    array(
        'eventname'   => '*',
        'callback'    => '\report_monitor\process_events_observer::process_event',
    )
);