<?php

if (!defined('MOODLE_INTERNAL')) {
    die("You cannot access this script this way");
}

if ($COURSE->groupmode == NOGROUPS) {
    $users = get_users_by_capability($context, 'mod/cognitivefactory:gradable', 'u.id, lastname, firstname', 'lastname,firstname');
} else {
    $groupid = groups_get_activity_group($cm, true);
    groups_print_activity_menu($cm, $returnurl);
    $users = get_users_by_capability($context, 'mod/cognitivefactory:gradable', 'u.id, lastname, firstname', 'lastname,firstname', '', '', $groupid);
}

// Make user menu.

$returnurl = new moodle_url('/mod/cognitivefactory/view.php', array('id' => $cm->id));
$usermenu = array();
foreach ($users as $u) {
    if (isset($userformincludes)) {
        if (!in_array($u->id, array_keys($userformincludes))) {
            continue;
        }
    }
    $usermenu[$u->id] = fullname($u);
}
if (count($usermenu) >= 2) {
    echo $OUTPUT->single_select($returnurl, 'userid', $usermenu, $userid);
}
