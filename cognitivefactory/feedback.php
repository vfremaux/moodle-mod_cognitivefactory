<?php

print_heading(get_string('report', 'cognitivefactory'));

print_simple_box($feedback);
$options = array('id' => $cm->id, 'what' => 'editreport');
print_single_button($options);
?>
