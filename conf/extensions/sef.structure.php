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

if (!defined('EXPONENT')) exit('');

$ctl = new checkboxcontrol(false,true);
$ctl->disabled = 0;

return array(
	gt('Search Engine Friendly URLs'),
	array(
		'SEF_URLS'=>array(
			'title'=>gt('Enable SEF URLs'),
			'description'=>gt('Enabling SEF URLs will make your URL strings easier to read and more attractive to the search engines.<br />'),
			'control'=>$ctl
		)
	)
);

?>
