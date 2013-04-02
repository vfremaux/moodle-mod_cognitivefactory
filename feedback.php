<?php

if (!defined('MOODLE_INTERNAL')) die('You cannot use this script directly');

print_heading(get_string('report', 'cognitivefactory'));

print_box($feedback);
$options = array('id' => $cm->id, 'what' => 'editreport');
print_single_button($options);

?>
