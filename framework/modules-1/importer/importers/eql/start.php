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

include_once(BASE.'framework/core/subsystems-1/forms.php');

$form = new form();
$form->meta('module','importer');
$form->meta('action','page');
$form->meta('importer','eql');
$form->meta('page','process');

$form->register('file',gt('EQL File'),new uploadcontrol());
$form->register('submit','',new buttongroupcontrol(gt('Restore'),'',''));

$template = new template('importer','_eql_restoreForm',$loc);
$template->assign('form_html',$form->toHTML());
$template->output();

?>