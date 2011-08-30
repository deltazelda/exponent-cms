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
 * List Builder Control
 *
 * @package Subsystems-Forms
 * @subpackage Control
 */
class listbuildercontrol extends formcontrol {

	var $source = null;
	var $size = 8;
	var $newList = false;
	var $process = null;

	function name() { return "List Builder"; }

	function __construct($default,$source,$size=8) {
		if (is_array($default)) $this->default = $default;
		else $this->default = array($default);

		$this->size = $size;
        
		if ($source != null) {
			if (is_array($source)) $this->source = $source;
			else $this->source = array($source);
		} else {
			$this->newList = true;
		}
	}

	function controlToHTML($name, $process = null) {
		$this->process = $process;
		$this->_normalize();
		// eDebug($this->source, true);
		$html = '<input type="hidden" name="'.$name.'" id="'.$name.'" value="'.implode("|!|",array_keys($this->default)).'" />';
		$html .= '<table cellpadding="9" border="0" width="30"><tr><td width="10">';
		if (!$this->newList) {
			$html .= "<select id='source_$name' size='".$this->size."'>";
			foreach ($this->source as $key=>$value) {
				$html .= "<option value='$key'>$value</option>";
			}
			$html .= "</select>";
		} else {
			$html .= "<input id='source_$name' type='text' />";
		}
		$html .= "</td>";
		$html .= "<td valign='middle' width='10'>";
		if($process == "copy") {
			$html .= "<input type='image' onclick='addSelectedItem(&quot;$name&quot;,&quot;copy&quot;); return false' src='".ICON_RELATIVE."right.png' />";
		} else {
			$html .= "<input type='image' onclick='addSelectedItem(&quot;$name&quot;); return false' src='".ICON_RELATIVE."right.png' />";
		}
		$html .= "<br />";
		if($process == "copy") {
			$html .= "<input type='image' onclick='removeSelectedItem(&quot;$name&quot;,&quot;copy&quot); return false;' src='".ICON_RELATIVE."left.png' />";
		} else {
			$html .= "<input type='image' onclick='removeSelectedItem(&quot;$name&quot;); return false;' src='".ICON_RELATIVE."left.png' />";
		}
		$html .= "</td>";
		$html .= "<td width='10' valign='top'><select id='dest_$name' size='".$this->size."'>";
		foreach ($this->default as $key=>$value) {
			if (isset($this->source[$key])) $value = $this->source[$key];
			$html .= "<option value='$key'>$value</option>";
		}
		$html .= "</select>";
		$html .= "</td><td width='100%'></td></tr></table>";
		$html .= "<script>newList.$name = ".($this->newList?"true":"false").";</script>";
		return $html;
	}
	
	// Normalizes the $this->source and $this->defaults array
	// This allows us to gracefully recover from _formErrors and programmer error
	function _normalize() {
		if (!$this->newList) { // Only do normalization if we are not creating a list from scratch.
			// First, check to see if our parent has flipped the inError attribute to 1.
			// If so, we need to normalize the $this->default based on the source.
			if ($this->inError == 1) {
				$default = array();
				foreach ($this->default as $id) {
					$default[$id] = $this->source[$id];
					// Might as well normalize $this->source while we are here
					unset($this->source[$id]);
				}
				$this->default = $default;
			} else {
				// No form Error.  Just normalize $this->source
				if($this->process != 'copy') {
					$this->source = array_diff_assoc($this->source,$this->default);
				}
			}
		}
	}

	function onRegister(&$form) {
		$form->addScript("listbuilder",PATH_RELATIVE."framework/core/subsystems-1/forms/controls/listbuildercontrol.js");
	}

	static function parseData($formvalues, $name, $forceindex = false) {
		$values = array();
		if ($formvalues[$name] == "") return array();
		foreach (explode("|!|",$formvalues[$name]) as $value) {
			if ($value != "") {
				if (!$forceindex) {
					$values[] = $value;
				}
				else {
					$values[$value] = $value;
				}
			}
		}
		return $values;
	}

	function form($object) {
		require_once(BASE."framework/core/subsystems-1/forms.php");
		$form = new form();
		if (!isset($object->identifier)) {
			$object->identifier = "";
			$object->caption = "";
		}
		$form->register("identifier",gt('Identifer'),new textcontrol($object->identifier));
		$form->register("caption",gt('Caption'), new textcontrol($object->caption));

		$form->register("submit","",new buttongroupcontrol(gt('Save'),'',gt('Cancel')));
		return $form;
	}

	function update($values, $object) {
		if ($values['identifier'] == "") {
			$post = $_POST;
			$post['_formError'] = gt('Identifier is required.');
			expSession::set("last_POST",$post);
			return null;
		}
		$object->identifier = $values['identifier'];
		$object->caption = $values['caption'];
		return $object;
	}

}

?>
