<?php 

/**
* Module Brainstorm V2
* Operator : order
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
/********************************** Save operator config ********************************/
if ($action == 'saveconfig'){
    $operator = required_param('operator', PARAM_CLEANHTML);
    cognitivefactory_save_operatorconfig($cognitivefactory->id, $operator);
}
?>