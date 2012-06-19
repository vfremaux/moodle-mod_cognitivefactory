<?php

/**
* Module Brainstorm V2
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009
*/

if (!defined('MOODLE_INTERNAL')) die('You cannot use this script directly');

$allresponses = cognitivefactory_count_responses($cognitivefactory->id, 0, 0);
$responsesinyourgroup = cognitivefactory_count_responses($cognitivefactory->id, 0, $currentgroup);
$lang = current_language();
$alloperatordata = cognitivefactory_count_operatorinputs($cognitivefactory->id, 0, 0);
$operatordatainyourgroup = cognitivefactory_count_operatorinputs($cognitivefactory->id, 0, $currentgroup);
?>
<div style="padding-left : 200px">
<?php 
include "lang/{$lang}/organiseideas.html";

echo '<p>';
if ($groupmode == VISIBLEGROUPS || has_capability('mod/cognitivefactory:manage', $context)){
    echo '<b>'.get_string('responsesinallgroups', 'cognitivefactory'). ':</b> '.$allresponses.'<br/>';
}
if ($groupmode){
    echo '<b>'.get_string('responsesinyourgroup', 'cognitivefactory'). ':</b> '.$responsesinyourgroup.'<br/>';
}
else{
    echo '<b>'.get_string('allresponses', 'cognitivefactory'). ':</b> '.$responsesinyourgroup.'<br/>';
}
if ($groupmode == VISIBLEGROUPS || has_capability('mod/cognitivefactory:manage', $context)){
    echo '<b>'.get_string('opdatainallgroups', 'cognitivefactory'). ':</b> '.$alloperatordata.'<br/>';
}
if ($groupmode){
    echo '<b>'.get_string('opdatainyourgroup', 'cognitivefactory'). ':</b> '.$operatordatainyourgroup.'<br/>';
}
else{
    echo '<b>'.get_string('allopdata', 'cognitivefactory'). ':</b> '.$operatordatainyourgroup.'<br/>';
}
echo '</p>';
?>
</div>
