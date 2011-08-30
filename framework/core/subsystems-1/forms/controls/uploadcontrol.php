<?php

##################################################
#
# Copyright (c) 2004-2011 OIC Group, Inc.
# Written and Designed by James Hunt
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
/** @define "BASE" "../../../../.." */

if (!defined('EXPONENT')) exit('');

/**
 * Upload Control
 *
 * @package Subsystems-Forms
 * @subpackage Control
 */
class uploadcontrol extends formcontrol {

	function name() { return "File Upload Field"; }
	function isSimpleControl() { return false; }
	function getFieldDefinition() {
		return array(
			DB_FIELD_TYPE=>DB_DEF_STRING,
			DB_FIELD_LEN=>250,);
	}
	
	function __construct($default = "", $disabled = false) {
		$this->disabled = $disabled;
	}
	
	function onRegister(&$form) {
		$form->enctype = "multipart/form-data";
	}

	function controlToHTML($name) {
		$html = "<input type=\"file\" name=\"$name\" ";
		if(isset($this->class)) $html .=  'class="' . $this->class . '"';
		$html .= ($this->disabled?"disabled ":"");
		$html .= ($this->tabindex>=0?"tabindex=\"".$this->tabindex."\" ":"");
		$html .= ($this->accesskey != ""?"accesskey=\"".$this->accesskey."\" ":"");
		$html .= "/>";
		return $html;
	}

	function form($object) {
		require_once(BASE."framework/core/subsystems-1/forms.php");

		$form = new form();
		if (!isset($object->identifier)) {
			$object->identifier = "";
			$object->caption = "";
			$object->default = "";
		}
		$form->register("identifier",gt('Identifier'),new textcontrol($object->identifier));
		$form->register("caption",gt('Caption'), new textcontrol($object->caption));
		$form->register("default",gt('Default'), new textcontrol($object->default));
		$form->register("submit","",new buttongroupcontrol(gt('Save'),'',gt('Cancel')));
		return $form;
	}

	function update($values, $object) {
        if ($object == null) $object = new uploadcontrol();
        if ($values['identifier'] == "") {
            $post = $_POST;
            $post['_formError'] = gt('Identifier is required.');
            expSession::set("last_POST",$post);
            return null;
        }
        $object->identifier = $values['identifier'];
        $object->caption = $values['caption'];
        $object->default = $values['default'];
        return $object;
    }

	function moveFile($original_name,$formvalues) {
		$dir = 'files/uploads';
		$filename = expFile::fixName(time().'_'.$formvalues[$original_name]['name']);
		$dest = $dir.'/'.$filename;
        //Check to see if the directory exists.  If not, create the directory structure.
        if (!file_exists(BASE.$dir)) expFile::makeDirectory($dir);
        // Move the temporary uploaded file into the destination directory, and change the name.
        expFile::moveUploadedFile($formvalues[$original_name]['tmp_name'],BASE.$dest);
		return $dest;
	}

	static function parseData($original_name,$formvalues) {
		$file = $formvalues[$original_name];
		return '<a href="'.URL_FULL.$file.'">'.basename($file).'</a>';
	}
}

?>
