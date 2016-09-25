<?php

/**
* Module cognitivefactory
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009
*/

if (!defined('MOODLE_INTERNAL')) die('You cannot use this script directly');

$ismanager = has_capability('mod/cognitivefactory:manage', $context);

$allresponses = cognitivefactory_count_responses($cognitivefactory->id, 0, $currentgroup); // all users all groups
$responsesinyourgroup = cognitivefactory_count_responses($cognitivefactory->id, 0, $currentgroup);
$alloperatordata = cognitivefactory_count_operatorinputs($cognitivefactory->id, 0, $currentgroup);
$operatordatainyourgroup = cognitivefactory_count_operatorinputs($cognitivefactory->id, 0, $currentgroup);

echo "<div style=\"padding-left : 200px\">";

$lang = current_language();
include "lang/{$lang}/displayresults.html";

echo '<p>';

if ($groupmode == VISIBLEGROUPS || $ismanager) {
    echo '<b>'.get_string('responsesinallgroups', 'cognitivefactory'). ':</b> '.$allresponses.'<br/>';
}
if ($groupmode) {
    echo '<b>'.get_string('responsesinyourgroup', 'cognitivefactory'). ':</b> '.$responsesinyourgroup.'<br/>';
} else {
    echo '<b>'.get_string('allresponses', 'cognitivefactory'). ':</b> '.$responsesinyourgroup.'<br/>';
}

if ($groupmode == VISIBLEGROUPS || $ismanager) {
    echo '<b>'.get_string('opdatainallgroups', 'cognitivefactory'). ':</b> '.$alloperatordata.'<br/>';
}

if ($groupmode) {
    echo '<b>'.get_string('opdatainyourgroup', 'cognitivefactory'). ':</b> '.$operatordatainyourgroup.'<br/>';
} else {
    echo '<b>'.get_string('allopdata', 'cognitivefactory'). ':</b> '.$operatordatainyourgroup.'<br/>';
}
echo '</p>';
echo '</div>';

