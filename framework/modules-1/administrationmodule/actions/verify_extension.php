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
/** @define "BASE" "../../../.." */

// Part of the Extensions category.

if (!defined('EXPONENT')) exit('');

if (exponent_permissions_check('extensions',exponent_core_makeLocation('administrationmodule'))) {

	$sessid = session_id();
	$files = array();
//	foreach (expFile::listFlat(BASE.'extensionuploads/'.$sessid,true,null,array(),BASE.'extensionuploads/'.$sessid) as $key=>$f) {
	foreach (expFile::listFlat(BASE.'tmp/extensionuploads/'.$sessid,true,null,array(),BASE.'tmp/extensionuploads/'.$sessid) as $key=>$f) {
		if ($key != '/archive.tar' && $key != '/archive.tar.gz' && $key != '/archive.tar.bz2' && $key != '/archive.zip') {
			$files[] = array(
				'absolute'=>$key,
				'relative'=>$f,
				'canCreate'=>expFile::canCreate(BASE.substr($key,1)),
				'ext'=>substr($f,-3,3)
			);
		}
	}
	
	$template = new template('administrationmodule','_upload_filesList',$loc);
//	$template->assign('relative','extensionuploads/'.$sessid);
	$template->assign('relative','tmp/extensionuploads/'.$sessid);
	$template->assign('files',$files);
	$template->output();
} else {
	echo SITE_403_HTML;
}

?>