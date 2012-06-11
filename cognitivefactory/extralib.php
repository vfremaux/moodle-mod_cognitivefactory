<?php

/**
* this is a direct clone from the WebLib.php choose_from_menu, patched 
* for accepting multiple selection within a list.
* @param array $options the array of options in the list
* @param string $name the name of the select field
* @param $selected
*/
if (!function_exists('choose_multiple_from_menu')){
    function choose_multiple_from_menu($options, $name, $selected=null, $nothing='choose', $script='',
                               $nothingvalue='0', $return=false, $disabled=false, $tabindex=0, $id='', $size=1){
    
        if ($nothing == 'choose') {
            $nothing = get_string('choose') .'...';
        }
    
        if (!$selected) {
            $selected = array();
        }
        else{
            if (!is_array($selected)){
                $selected = explode(',', $selected);
            }
        }
    
        $attributes = "multiple=\"multiple\" size=\"$size\" ";
        
        $attributes .= ($script) ? 'onchange="'. $script .'"' : '';
        if ($disabled) {
            $attributes .= ' disabled="disabled"';
        }
    
        if ($tabindex) {
            $attributes .= ' tabindex="'.$tabindex.'"';
        }
    
        if ($id ==='') {
            $id = 'menu'.$name;
             // name may contaion [], which would make an invalid id. e.g. numeric question type editing form, assignment quickgrading
            $id = str_replace('[', '', $id);
            $id = str_replace(']', '', $id);
        }
    
        $output = '<select id="'.$id.'" name="'. $name .'" '. $attributes .'>' . "\n";
        if ($nothing) {
            $output .= '   <option value="'. s($nothingvalue) .'"'. "\n";
            if ($nothingvalue === $selected) {
                $output .= ' selected="selected"';
            }
            $output .= '>'. $nothing .'</option>' . "\n";
        }
        if (!empty($options)) {
            foreach ($options as $value => $label) {
                $output .= '   <option value="'. s($value) .'"';
                if (in_array($value, $selected)) {
                    $output .= ' selected="selected"';
                }
                if ($label === '') {
                    $output .= '>'. $value .'</option>' . "\n";
                } else {
                    $output .= '>'. $label .'</option>' . "\n";
                }
            }
        }
        $output .= '</select>' . "\n";
    
        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }
}

/**
* adds an error css marker in case of matching error
* @param array $errors the current error set
* @param string $errorkey 
*/
if (!function_exists('print_error_class')){
    function print_error_class($errors, $errorkeylist){
        if ($errors){
            foreach($errors as $anError){
                if ($anError->on == '') continue;
                if (preg_match("/\\b{$anError->on}\\b/" ,$errorkeylist)){
                    echo " class=\"formerror\" ";
                    return;
                }
            }        
        }
    }
}

if (!function_exists('print_error_box')){
    function print_error_box($errors){
        if (!empty($errors)){
            $errorstr = '';
            foreach($errors as $anError){
                $errorstr .= $anError->message;
            }
            print_simple_box($errorstr, 'center', '70%', '', 5, 'errorbox');
        }
    }
}

/**
* A small utility function for making scale menus
*
*/
if (!function_exists('make_grading_menu')){
    function make_grading_menu(&$brainstorm, $id, $selected = '', $return = false){
        if (!$brainstorm->scale) return '';
    
        if ($brainstorm->scale > 0){
            for($i = 0 ; $i <= $brainstorm->scale ; $i++)
                $scalegrades[$i] = $i; 
        }
        else {
            $scaleid = - ($brainstorm->scale);
            if ($scale = get_record('scale', 'id', $scaleid)) {
                $scalegrades = make_menu_from_list($scale->scale);
            }
        }
        return choose_from_menu($scalegrades, $id, $selected, 'choose', '', '', $return);
    }
}


?>