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
ob_start();


if ($user->isLoggedIn()) {
	header('Location: ' . expSession::get('redirecturl'));
} else {
	//expSession::set('redirecturl', expHistory::getLastNotEditable());
	expSession::set('redirecturl', expHistory::getLast());
	expSession::set('redirecturl_error', makeLink(array('module'=>'loginmodule', 'action'=>'loginredirect')));
	expHistory::flowSet(SYS_FLOW_PUBLIC,SYS_FLOW_ACTION);
}

loginmodule::show('Default',null,gt('Log In'));


$template = new template('loginmodule','_login_redirect');

$template->assign('output',ob_get_contents());
ob_end_clean();
$template->output();

?>
