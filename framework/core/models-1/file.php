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
/** @define "BASE" "../../.." */

class file {
	static function update($name,$dest,$object,$destname = null,$force=false) {
		// Get the filename, if it was passed in the update() call.  Otherwise, fallback
		if ($destname == null) {
			$object->filename = $_FILES[$name]['name'];
		} else {
			$object->filename = $destname;
		}
		// General error message.  This will be made more explicit later on.
		$err = sprintf(gt('Unable to upload "%s" to the server'),$object->filename) .'<br />';
		
		switch($_FILES[$name]['error']) {
			case UPLOAD_ERR_OK:
				// Everything looks good.  Continue with the update.
				break;
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				// This is a tricky one to catch.  If the file is too large for POST, then the script won't even run.
				// But if its between post_max_size and upload_file_max_size, we will get here.
				return $err.gt('The file you uploaded exceeded the size limits for the server.');
			case UPLOAD_ERR_PARTIAL:
				return $err.gt('The file you uploaded was only partially uploaded.');
			case UPLOAD_ERR_NO_FILE:
				return $err.gt('No file was uploaded.');
			default:
				return $err.gt('An unknown error has occurred.');
				break;
		}
		
		// Fix the filename, so that we don't have funky characters screwing with out attempt to create the destination file.
		$object->filename = expFile::fixName($object->filename);
	
			
		if (file_exists(BASE.$dest.'/'.$object->filename) && $force == false) {
			return $err.gt('The file already exists on the server');
		}
	
		//Check to see if the directory exists.  If not, create the directory structure.
		if (!file_exists(BASE.$dest)) {
			expFile::makeDirectory($dest);
		}	

		// Move the temporary uploaded file into the destination directory, and change the name.
		expFile::moveUploadedFile($_FILES[$name]['tmp_name'],BASE.$dest.'/'.$object->filename);
		
		if (!file_exists(BASE.$dest.'/'.$object->filename)) {
			return $err.gt('The destination file could not be created.');
		}
		
		// At this point, we are good to go.
		
		$object->mimetype = $_FILES[$name]['type'];
		$object->directory = $dest;
		//$object->accesscount = 0;
		$object->filesize = $_FILES[$name]['size'];
		$object->posted = time();
		global $user;
		if ($user) {
			$object->poster = $user->id;
		}
		$object->last_accessed = time();
		
		$object->is_image = 0;
		// Get image width and height:
		$size = @getimagesize(BASE.$object->directory.'/'.$object->filename);
		if ($size !== false) {
			$object->is_image = 1;
			$object->image_width = $size[0];
			$object->image_height = $size[1];
		}
		
		return $object;
	}
	
	function delete($file) {
		if ($file == null) {
			return true;
		}
		
		if (is_readable(BASE.$file->directory) && !file_exists(BASE.$file->directory.'/'.$file->filename)) {
			return true;
		}
		
		if (expUtil::isReallyWritable(BASE.$file->directory)) {
			unlink($file->directory.'/'.$file->filename);
			if (!file_exists(BASE.$file->directory.'/'.$file->filename)) {
				return true;
			}
		}
		return false;
	}
	
	function findByType($item_type) {
                global $db;
                return $db->selectObjects('file', 'id IN (SELECT file_id FROM '.DB_TABLE_PREFIX.'_file_details WHERE item_type="'.$item_type.'")');
        }

	static function findFilesForItem($item_type, $item_id) {
		global $db;
		return $db->selectObjects('file', 'id IN (SELECT file_id FROM '.DB_TABLE_PREFIX.'_file_details WHERE item_type="'.$item_type.'" AND item_id='.$item_id.')');
	}
}

?>
