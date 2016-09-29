<?php

class mod_cognitivefactory_renderer extends plugin_renderer_base {

    function operator_pix_url($pix, $operator) {
        global $CFG;

        return $CFG->wwwroot.'/mod/cognitivefactory/operators/'.$operator.'/pix/'.$pix.'.gif';
    }

}