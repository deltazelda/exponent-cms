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
/** @define "BASE" ".." */

if (!defined('EXPONENT')) exit('');

	/* exdoc
	 * In PHP5, the autoloader function will check these
	 * directories when it tries to load a class definition
	 * file.  Other parts of the system should append to this
	 * directory as needed, in order to take full advantage
	 * of autoloading
	 * @node Subsystems:Autoloader
	 */
	//$auto_dirs = array(BASE.'datatypes', BASE.'framework/core/subsystems-1/forms', BASE.'framework/core/subsystems-1/forms/controls');
	$auto_dirs = array(
			BASE.'framework/core/models-1',  // old 1.0 /datatypes
			BASE.'framework/core/subsystems-1/forms',
			BASE.'framework/core/subsystems-1/forms/controls',
//			BASE.'framework/datatypes',  // moved to framework/core/models
			BASE.'framework/core/controllers',
			BASE.'framework/core/models',  // used to be framework/core/datatypes & framework/datatypes
			BASE.'framework/core/subsystems',
			BASE.'framework/modules/ecommerce/billingcalculators',
			BASE.'framework/modules/ecommerce/shippingcalculators',
			BASE.'framework/modules/ecommerce/products/controllers',  //FIXME does NOT exist
			BASE.'framework/modules/ecommerce/products/datatypes',  // models
	);

	$auto_dirs2 = array(
			BASE.'themes/'.DISPLAY_THEME_REAL.'/modules',
			BASE.'framework/modules'
	);

	/* exdoc
	 * This function overrides the default PHP5 autoloader,
	 * and instead looks at the $auto_dirs global to look
	 * for class files.  This function is automatically
	 * invoked in PHP5
	 *
	 * @param string $class The name of the class to look for.
	 * @node Subsystems:Autoloader
	 */
	function expLoadClasses($class) {
		global $auto_dirs, $auto_dirs2;
		foreach ($auto_dirs as $auto_dir) {
			if (is_readable($auto_dir.'/'.$class.'.php')) {
				include_once($auto_dir.'/'.$class.'.php');
				return;
			}
		}
	}
	
	spl_autoload_register('expLoadClasses');

?>
