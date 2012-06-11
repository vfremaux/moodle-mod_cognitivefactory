<?php

/**
* Module Brainstorm V2
* Operator : order
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once ($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/locallib.php");
// include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

print_heading(get_string('myordering', 'cognitivefactory'));
order_display($cognitivefactory, null, 0);
?>