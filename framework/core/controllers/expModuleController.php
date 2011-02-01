<?php
/**
 * This file is part of Exponent Content Management System
 *
 * Exponent is free software; you can redistribute
 * it and/or modify it under the terms of the GNU
 * General Public License as published by the Free
 * Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * @category   Exponent CMS
 * @package    Framework
 * @subpackage Controllers
 * @author     Adam Kessler <adam@oicgroup.net>
 * @copyright  2004-2009 OIC Group, Inc.
 * @license    GPL: http://www.gnu.org/licenses/gpl.txt
 * @version    Release: @package_version@
 * @link       http://www.exponent-docs.org/api/package/PackageName
 */

class expModuleController extends expController {

	function name() { return $this->displayname(); }
    function displayname() { return "Modules"; }
    function description() { return "Manages all things about Exponent Modules"; }
    function author() { return "Phillip Ball"; }
    function hasSources() { return false; }
    function hasViews() { return true; }
	function hasContent() { return false; }
    
    function manage () {
        $controllers = expModules::listActiveControllers();
        $old_school_mods = expModules::listActiveOSMods();
        assign_to_template(array('controllers'=>$controllers,'old_school_mods'=>$old_school_mods));
    }

    function update () {
        global $db;
        //$db->delete('modstate');
        
        $aMods = $db->selectObjects('modstate',1);
        		        
        foreach ($aMods as $key => $value) {
            if (!empty($this->params['mods']) && array_key_exists($value->module,$this->params['mods'])) {
                $aMods[$key]->active = $this->params['mods'][$value->module];
                $db->updateObject($aMods[$key],'modstate',"module='".$value->module."'");
            } else {
                $aMods[$key]->active = 0;
                $db->updateObject($aMods[$key],'modstate',"module='".$value->module."'");
            }
            unset($this->params['mods'][$value->module]);
        }
        
        if (!empty($this->params['mods'])) {
            foreach ($this->params['mods'] as $key => $value) {
                $aMod->module = $key;
                $aMod->active = $value;
                $db->insertObject($aMod,'modstate');
            }
        }
        flash("message", "Active Modules have been updated.");
        expHistory::returnTo('editable');
    }

}

?>