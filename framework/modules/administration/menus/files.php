<?php

##################################################
#
# Copyright (c) 2004-2011 OIC Group, Inc.
#
# This file is part of Exponent
#
# Exponent is free software; you can redistribute
# it and/or modify it under the terms of the GNU
# General Public License as published by the Free
# Software Foundation; either version 2 of the
# License, or (at your option) any later version.
#
# GPL: http://www.gnu.org/licenses/gpl.txt
#
##################################################

if (!defined('EXPONENT')) exit('');

$script = "
YUI(EXPONENT.YUI3_CONFIG).use('node', function(Y) {
    EXPONENT.filepickerwindow = function (){
        win = window.open('".makeLink($params=array('controller'=>'file','action'=>'picker','ajax_action'=>'1','update'=>'noupdate'))."', 'IMAGE_BROWSER','left=0,top=0,scrollbars=yes,width=1024,height=600,toolbar=no,resizable=yes,status=0');
        if (!win) {
            //Catch the popup blocker
            alert(\"Your popup blocker has prevented the file manager from opening\");
        }
    }
    var fileuploaderwindow = function (){
        win = window.open('".makeLink($params=array('controller'=>'file','action'=>'uploader','ajax_action'=>'1','update'=>'noupdate'))."', 'IMAGE_BROWSER','left=0,top=0,scrollbars=yes,width=1024,height=600,toolbar=no,resizable=yes,status=0');
        if (!win) {
            //Catch the popup blocker
            alert(\"Your popup blocker has prevented the file manager from opening\");
        }
    }
    
    // Y.one('#filemanager').on('click',filepickerwindow);
    // Y.one('#fileuploader').on('click',fileuploaderwindow);
});
";

//exponent_javascript_toFoot('zadminfilemanager', '', null, $script);
expJavascript::pushToFoot(array(
    "unique"=>'zadminfilemanager',
    "yui3mods"=>1,
    "content"=>$script,
    "src"=>""
 ));

return array(
    'text'=>gt('Files'),
    'classname'=>'files',
    'submenu'=>array(
        'id'=>'file-functions',
        'itemdata'=>array(
            array(
                'text'=>gt("File Manager"),
                'url'=>'javascript:"EXPONENT.filepickerwindow(); "',
                'classname'=>'filemanager',
                'id'=>'filemanager',
            ),
            array(
                'text'=>gt("Upload Files"),
                'url'=>'#',
                'classname'=>'fileuploader',
                'id'=>'fileuploader',
            )
        ),
    )
);

?>
