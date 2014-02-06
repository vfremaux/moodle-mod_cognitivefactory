<?php 

/**
* Module Brainstorm V2
* Operator : locate
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/

/********************************** Save operator config ********************************/
if ($action == 'saveconfig'){
    $operator = required_param('operator', PARAM_ALPHA);
    
    $errors = array();

    /// make some controls
    $xminrange = required_param('config_xminrange', PARAM_RAW);
    $xmaxrange = required_param('config_xmaxrange', PARAM_RAW);
    if ($xminrange >= $xmaxrange){
        $error->message = get_string('invertedrange', 'cognitivefactory');
        $error->on = 'xrange';
        $errors[] = $error;
    }

    /// make some controls
    $yminrange = required_param('config_yminrange', PARAM_RAW);
    $ymaxrange = required_param('config_ymaxrange', PARAM_RAW);
    if ($yminrange >= $ymaxrange){
        unset($error);
        $error->message = get_string('invertedrange', 'cognitivefactory');
        $error->on = 'yrange';
        $errors[] = $error;
    }
    
    if (empty($errors)){
        cognitivefactory_save_operatorconfig($cognitivefactory->id, $operator);
    }
    else{
        print_error_box($errors);
    }
}
?>