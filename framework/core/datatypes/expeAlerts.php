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
 * @subpackage Datatypes
 * @author     Adam Kessler <adam@oicgroup.net>
 * @copyright  2004-2009 OIC Group, Inc.
 * @license    GPL: http://www.gnu.org/licenses/gpl.txt
 * @version    Release: @package_version@
 * @link       http://www.exponent-docs.org/api/package/PackageName
 */
 
class expeAlerts extends expRecord {
    public $table = 'expeAlerts';
    
    public function __construct($params=array()) {
        global $db;
        if (isset($params['module']) && isset($params['src'])) {
            $id = $db->selectValue($this->table, 'id', "module='".$params['module']."' AND src='".$params['src']."'");
            parent::__construct($id, false, false);
        } else {
            parent::__construct($params, false, false);
        }
    }
    
    // we are going to override the build and beforeSave functions to
    // make sure the name of the controller is in the right format
    public function build($params=array()) {
        parent::build($params);
        $this->module = getControllerName($this->module);
    }
    
    public function beforeSave() {
        $this->module = getControllerName($this->module);
        parent::beforeSave();
    }
    
    public function getPendingBySubscriber($id) {
        expeAlerts::getBySubscriber($id, true);
    }
    
    public function getBySubscriber($id, $pending=false) {
        global $db;
        
        $enabled = empty($pending) ? 1 : 0;
        
        // find pending subscriptions        
        $sql  = 'SELECT e.* FROM '.DB_TABLE_PREFIX.'_expeAlerts e ';
        $sql .= 'JOIN '.DB_TABLE_PREFIX.'_expeAlerts_subscribers es ON e.id=es.subscribers_id ';
        $sql .= 'WHERE es.enabled='.$enabled.' && es.subscribers_id='.$id;

        return $db->selectObjectsBySql($sql);
    }
}

?>