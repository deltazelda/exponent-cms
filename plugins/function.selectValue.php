<?php

##################################################
#
# Copyright (c) 2004-2006 OIC Group, Inc.
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

function smarty_function_selectValue($params,&$smarty) {
	global $db;
	
	if (empty($params['field']) || empty($params['table'])) return false;
	
	$where = isset($params['where']) ? $params['where'] : null;	
	return $db->selectValue($params['table'], $params['field'], $params['where']);
}

?>