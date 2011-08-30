<?php

##################################################
#
# Copyright (c) 2004-2011 OIC Group, Inc.
# Created by Adam Kessler @ 05/28/2008
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

class storeController extends expController {
    public $basemodel_name = 'product';
    
    public $useractions = array(
        'showall'=>'All Products and Categories',
        'showall_featured_products'=>'Products - Only show Featured',
        'showallManufacturers'=>'Products - By Manufacturer',
        'showTopLevel'=>'Categories - Show Top Level',
        'showFullTree'=>'Categories - Show Full Tree',
        'showallSubcategories'=>'Categories - Subcategories of current category',
        'upcoming_events'=>'Event Registration - Upcomming Events',
		  'events_calendar'=>'Event Registration - Calendar View',
        'ecom_search'=>'Search - Autocomplete',
        'search_by_model_form'=>'Search - By Model',
        'quicklinks'=>'Links - Users Links',
		  'showall_category_featured_products' => 'Show Featured Products under the current category'
    );
    
    // hide the configs we don't need
    public $remove_configs = array(
        'comments',
        'ealerts',
        'files',
        'rss',
        'aggregation',
        'tags'
    );
    
    //protected $permissions = array_merge(array("test"=>'Test'), array('copyProduct'=>"Copy Product"));
    protected $add_permissions = array('copyProduct'=>"Copy Product",'delete_children'=>"Delete Children", 'import'=>'Import Products', 'reimport'=>'ReImport Products', 'export'=>'Export Products','findDupes'=>'Fix Duplicate SEF Names','manage_sales_reps'=>'Manage Sales Reps', 'batch_process'=>'Batch capture order transactions','process_orders'=>'Batch capture order transactions','import_external_addresses'=>'Import addressess from other sources',
    'showallImpropercategorized'=>'View products in top level categories that should not be',
    'showallUncategorized'=>'View all uncategorized products',
    'nonUnicodeProducts'=>'View all non-unicode charset products',
    'cleanNonUnicodeProducts'=>'Clean all non-unicode charset products',
	'uploadModelAliases'=>'Upload model aliases',
	'processModelAliases'=>'Process uploaded model aliases',
	'saveModelAliases'=>'Save uploaded model aliases',
	'deleteProcessedModelAliases'=>'Delete processed uploaded model aliases',
	'delete_model_alias'=>'Process model aliases',
	'update_model_alias'=>'Save model aliases',
	'edit_model_alias'=>'Delete model aliases'
    );
     
    function displayname() { return "e-Commerce Store Front"; }
    function description() { return "Use this module to display products and categories of you Ecommerce store"; }
    function author() { return "OIC Group, Inc"; }
    function isSearchable() { return true; }
    function canImportData() { return true; }
    function canExportData() { return true; }

    function __construct($src=null,$params=array()) {
        global $db, $router, $section, $user;
        parent::__construct($src=null,$params);
        
        // we're setting the config here globably
        $this->grabConfig();          

        if (expTheme::inAction() && !empty($router->url_parts[1]) && ($router->url_parts[0]=="store"&&$router->url_parts[1]=="showall")) {
            if (isset($router->url_parts[array_search('title',$router->url_parts)+1]) && is_string($router->url_parts[array_search('title',$router->url_parts)+1])) {
                $default_id = $db->selectValue('storeCategories', 'id', "sef_url='".$router->url_parts[array_search('title',$router->url_parts)+1]."'");
                $active = $db->selectValue('storeCategories', 'is_active', "sef_url='".$router->url_parts[array_search('title',$router->url_parts)+1]."'");
                if (empty($active) && $user->is_acting_admin!=1) {
                    redirect_to(array("section"=>SITE_DEFAULT_SECTION));
                }
                expSession::set('catid',$default_id);
            }
        } elseif (expTheme::inAction() && !empty($router->url_parts[1]) && ($router->url_parts[0]=="store" && ($router->url_parts[1]=="show" || $router->url_parts[1]=="showByTitle"))) {
            if (isset($router->url_parts[array_search('id',$router->url_parts)+1])&&($router->url_parts[array_search('id',$router->url_parts)+1]!=0)) {
                $default_id = $db->selectValue('product_storeCategories', 'storecategories_id', "product_id='".$router->url_parts[array_search('id',$router->url_parts)+1]."'");
                expSession::set('catid',$default_id);
            } else {
                $prod_id = $db->selectValue('product', 'id', "sef_url='".$router->url_parts[array_search('title',$router->url_parts)+1]."'");
                $default_id = $db->selectValue('product_storeCategories', 'storecategories_id', "product_id='".$prod_id."'");
                expSession::set('catid',$default_id);
            }
        } elseif (isset($this->config['show_first_category']) || (!expTheme::inAction() && $section==SITE_DEFAULT_SECTION)) {
            if (!empty($this->config['show_first_category'])) {
              $default_id = $db->selectValue('storeCategories', 'id', 'lft=1');
            } else {
              $default_id = 0;
            }
            expSession::set('catid',$default_id);
        } elseif (!isset($this->config['show_first_category']) && !expTheme::inAction()) {
            expSession::set('catid',0);
        } else {
            $default_id = 0;
        }

        // figure out if we need to show all categories and products or default to showing the first category.
        // elseif (!empty($this->config['category'])) {
        //     $default_id = $this->config['category'];
        // } elseif (ecomconfig::getConfig('show_first_category')) {
        //     $default_id = $db->selectValue('storeCategories', 'id', 'lft=1');
        // } else {
        //     $default_id = 0;
        // }
		
        $this->parent = expSession::get('catid');
        $this->category = new storeCategory($this->parent);
        // we're setting the config here for the category
        $this->grabConfig($this->category);          
    }

    function showall() {
        global $db, $user, $router;
        
        expHistory::set('viewable', $this->params);
        
        if (empty($this->category->is_events)) {
            $count_sql_start = 'SELECT COUNT(DISTINCT p.id) FROM '.DB_TABLE_PREFIX.'_product p ';
            
            
            $sql_start  = 'SELECT DISTINCT p.*, IF(base_price > special_price AND use_special_price=1,special_price, base_price) as price FROM '.DB_TABLE_PREFIX.'_product p ';            
            $sql = 'JOIN '.DB_TABLE_PREFIX.'_product_storeCategories sc ON p.id = sc.product_id ';
            $sql .= 'WHERE ';
            if ( !($user->is_admin || $user->is_acting_admin) ) $sql .= '(p.active_type=0 OR p.active_type=1) AND ' ;
            $sql .= 'sc.storecategories_id IN (';
            $sql .= 'SELECT id FROM '.DB_TABLE_PREFIX.'_storeCategories WHERE rgt BETWEEN '.$this->category->lft.' AND '.$this->category->rgt.')';         
            
            $count_sql = $count_sql_start . $sql;
            $sql = $sql_start . $sql;
            
            $order = 'sc.rank'; //$this->config['orderby'];
            $dir = 'ASC'; $this->config['orderby_dir'];
            //eDebug($this->config);
        } else {
            $sql_start  = 'SELECT DISTINCT p.*, er.event_starttime, er.signup_cutoff FROM '.DB_TABLE_PREFIX.'_product p ';
            $count_sql_start = 'SELECT COUNT(DISTINCT p.id), er.event_starttime, er.signup_cutoff FROM '.DB_TABLE_PREFIX.'_product p ';
            $sql .= 'JOIN '.DB_TABLE_PREFIX.'_product_storeCategories sc ON p.id = sc.product_id ';
            $sql .= 'JOIN '.DB_TABLE_PREFIX.'_eventregistration er ON p.product_type_id = er.id ';
            $sql .= 'WHERE sc.storecategories_id IN (';
            $sql .= 'SELECT id FROM '.DB_TABLE_PREFIX.'_storeCategories WHERE rgt BETWEEN '.$this->category->lft.' AND '.$this->category->rgt.')'; 
            if ($this->category->hide_closed_events) {
                $sql .= ' AND er.signup_cutoff > '.time();
            }        
            
            $count_sql = $count_sql_start . $sql;
            $sql = $sql_start . $sql;
                   
            $order = 'event_starttime';
            $dir = 'ASC';
        }
        
        if($this->category->find('count') > 0) {   
            $page = new expPaginator(array(
                'model_field'=>'product_type',
                'sql'=>$sql,
                'count_sql'=>$count_sql,
                'limit'=>$this->config['pagination_default'],
                'order'=>$order,
                'dir'=>$dir,
                'controller'=>$this->params['controller'],
                'action'=>$this->params['action'],
                'columns'=>array('Model #'=>'model','Product Name'=>'title','Price'=>'price'),
                ));
        } else {
            $page = new expPaginator(array(
                'model_field'=>'product_type',
                'sql'=>'SELECT * FROM '.DB_TABLE_PREFIX.'_product WHERE 1',
                'limit'=>$this->config['pagination_default'],
                'order'=>$order,
                'dir'=>$dir,
                'controller'=>$this->params['controller'],
                'action'=>$this->params['action'],
                'columns'=>array('Model #'=>'model','Product Name'=>'title','Price'=>'price'),
                ));
        }

        $ancestors = $this->category->pathToNode();   
        $categories = ($this->parent == 0) ? $this->category->getTopLevel(null,false,true) : $this->category->getChildren(null,false,true);
                
        $rerankSQL = "SELECT DISTINCT p.* FROM ".DB_TABLE_PREFIX."_product p JOIN ".DB_TABLE_PREFIX."_product_storeCategories sc ON  p.id = sc.product_id WHERE sc.storecategories_id=".$this->category->id." ORDER BY rank ASC";
        //eDebug($router);
        $defaultSort = $router->current_url;
		
        assign_to_template(array('page'=>$page, 'defaultSort'=>$defaultSort, 'ancestors'=>$ancestors, 'categories'=>$categories, 'current_category'=>$this->category,'rerankSQL'=>$rerankSQL));
    }
    
    function grabConfig($category=null) {
        
        // grab the configs for the category
        if (is_object($category)) 
        {
            $ctcfg->mod = "storeCategory";
            $ctcfg->src = "@store-".$category->id;
            $ctcfg->int = "";            
            $catConfig = new expConfig($ctcfg);
        }         
      
        // since router maps strip off src and we need that to pull configs, we won't get the configs
        // of the page is router mapped. We'll ensure we do here:
        $cfg->mod = "ecomconfig";
        $cfg->src = "@globalstoresettings";
        $cfg->int = "";
        $config = new expConfig($cfg);
        $this->config = (empty($catConfig->config) || @$catConfig->config['use_global']==1) ? $config->config : $catConfig->config;    

		//This is needed since in the first installation of ecom the value for this will be empty and we are doing % operation for this value
		//So we need to ensure if the value is = 0, then we can as well make it to 1
		if(empty($this->config['images_per_row'])) {
			$this->config['images_per_row'] = 3;
		}
    }
    
    function upcoming_events() {
        $sql  = 'SELECT DISTINCT p.*, er.event_starttime, er.signup_cutoff FROM '.DB_TABLE_PREFIX.'_product p ';
        $sql .= 'JOIN '.DB_TABLE_PREFIX.'_eventregistration er ON p.product_type_id = er.id ';
        $sql .= 'WHERE 1 AND er.signup_cutoff > '.time();
      
        $limit = empty($this->config['event_limit']) ? 10 : $this->config['event_limit'];
        $order = 'event_starttime';
        $dir = 'ASC';
        
        $page = new expPaginator(array(
            'model_field'=>'product_type',
            'sql'=>$sql,
            'limit'=>$limit,
            'order'=>$order,
            'dir'=>$dir,
            'controller'=>$this->params['controller'],
            'action'=>$this->params['action'],
            'columns'=>array('Model #'=>'model','Product Name'=>'title','Price'=>'base_price'),
            ));
        
        assign_to_template(array('page'=>$page));
    }
    
    function events_calendar() {
        global $db;
        
        expHistory::set('viewable', $this->params);
        
        include_once(BASE."framework/core/subsystems-1/datetime.php");

        $time = isset($this->params['time']) ? $this->params['time'] : time();
        assign_to_template(array('time'=>$time));
        
        $monthly = array();
        $counts = array();
        
        $info = getdate($time);
        $nowinfo = getdate(time());
        if ($info['mon'] != $nowinfo['mon']) $nowinfo['mday'] = -10;
        // Grab non-day numbers only (before end of month)
        $week = 0;
        $currentweek = -1;
        
        $timefirst = mktime(12,0,0,$info['mon'],1,$info['year']);
        $infofirst = getdate($timefirst);
        
        if ($infofirst['wday'] == 0) {
            $monthly[$week] = array(); // initialize for non days
            $counts[$week] = array();
        }
        for ($i = 1 - $infofirst['wday']; $i < 1; $i++) {
            $monthly[$week][$i] = array();
            $counts[$week][$i] = -1;
        }
        $weekday = $infofirst['wday']; // day number in grid.  if 7+, switch weeks
        
        // Grab day counts (deprecated, handled by the date function)
        // $endofmonth = exponent_datetime_endOfMonthDay($time);
        
        $endofmonth = date('t', $time);
        
        
        for ($i = 1; $i <= $endofmonth; $i++) {
            $start = mktime(0,0,0,$info['mon'],$i,$info['year']);
            if ($i == $nowinfo['mday']) $currentweek = $week;
           
            $dates = $db->selectObjects("eventregistration","`eventdate` = $start");
            $monthly[$week][$i] = storeController::_getEventsForDates($dates);
            
            $counts[$week][$i] = count($monthly[$week][$i]);
            if ($weekday >= 6) {
                $week++;
                $monthly[$week] = array(); // allocate an array for the next week
                $counts[$week] = array();
                $weekday = 0;
            } else $weekday++;
        }
        // Grab non-day numbers only (after end of month)
        for ($i = 1; $weekday && $i < (8-$weekday); $i++) {
            $monthly[$week][$i+$endofmonth] = array();
            $counts[$week][$i+$endofmonth] = -1;
        }
        
        assign_to_template(array(
            'currentweek'=>$currentweek,
            'monthly'=>$monthly,
            'counts'=>$counts,
            'nextmonth'=>$timefirst+(86400*45),
            'prevmonth'=>$timefirst-(86400*15),
            'now'=>$timefirst
        ));
    }
    
    /*
     * Helper function for the Calendar view
     */
    function _getEventsForDates($edates,$sort_asc = true) {        
//        if ($sort_asc && !function_exists('exponent_sorting_byEventStartAscending')) {
//            function exponent_sorting_byEventStartAscending($a,$b) {
//                return ($a->eventstart < $b->eventstart ? 1 : -1);
//            }
//        }
//        if (!$sort_asc && !function_exists('exponent_sorting_byEventStartDescending')) {
//            function exponent_sorting_byEventStartDescending($a,$b) {
//                return ($a->eventstart < $b->eventstart ? 1 : -1);
//            }
//        }
        
        global $db;
        $events = array();
        foreach ($edates as $edate) {
            if (!isset($this->params['cat'])) {
                if (isset($this->params['title']) && is_string($this->params['title'])) {
                    $default_id = $db->selectValue('storeCategories', 'id', "sef_url='".$this->params['title']."'");
                } elseif (!empty($this->config['category'])) {
                    $default_id = $this->config['category'];
                } elseif (ecomconfig::getConfig('show_first_category')) {
                    $default_id = $db->selectValue('storeCategories', 'id', 'lft=1');
                } else {
                    $default_id = 0;
                }
            }
            
            $parent = isset($this->params['cat']) ? intval($this->params['cat']) : $default_id;
            
            $category = new storeCategory($parent);
            
            $sql  = 'SELECT DISTINCT p.*, er.event_starttime, er.signup_cutoff FROM '.DB_TABLE_PREFIX.'_product p ';
            $sql .= 'JOIN '.DB_TABLE_PREFIX.'_product_storeCategories sc ON p.id = sc.product_id ';
            $sql .= 'JOIN '.DB_TABLE_PREFIX.'_eventregistration er ON p.product_type_id = er.id ';
            $sql .= 'WHERE sc.storecategories_id IN (';
            $sql .= 'SELECT id FROM exponent_storeCategories WHERE rgt BETWEEN '.$category->lft.' AND '.$category->rgt.')'; 
            if ($category->hide_closed_events) {
                $sql .= ' AND er.signup_cutoff > '.time();
            }
            $sql .= ' AND er.id = '.$edate->id;      
                    
            $order = 'event_starttime';
            $dir = 'ASC';
            
            $o = $db->selectObjectBySql($sql);
            $o->eventdate = $edate->eventdate;
            $o->eventstart += $edate->event_starttime;
            $o->eventend += $edate->event_endtime;
            $events[] = $o;
        }
//        if ($sort_asc == true) {
//            usort($events,'exponent_sorting_byEventStartAscending');
//        } else {
//            usort($events,'exponent_sorting_byEventStartDescending');
//        }
        $events = expSorter::sort(array('array'=>$events,'sortby'=>'eventstart', 'order'=>$sort_asc ? 'ASC' : 'DESC'));
        return $events;
    }
    
    function categoryBreadcrumb() {
        global $db, $router;
        
        //eDebug($this->category);

        /*if(isset($router->params['action']))
        {
            $ancestors = $this->category->pathToNode();       
        }else if(isset($router->params['section']))
        {
            $current = $db->selectObject('section',' id= '.$router->params['section']);
            $ancestors[] = $current;
            if( $current->parent != -1 || $current->parent != 0 )
            {                   
                while ($db->selectObject('section',' id= '.$router->params['section']);)
                    if ($section->id == $id) {
                        $current = $section;
                        break;
                    }
                }
            }
            eDebug($sections);
            $ancestors = $this->category->pathToNode();       
        }*/      
        
        $ancestors = $this->category->pathToNode();       
        // eDebug($ancestors);
        assign_to_template(array('ancestors'=>$ancestors));
    }

    function showallUncategorized() {
        expHistory::set('viewable', $this->params);
        
        $sql  = 'SELECT p.* FROM '.DB_TABLE_PREFIX.'_product p JOIN '.DB_TABLE_PREFIX.'_product_storeCategories ';
        $sql .= 'sc ON p.id = sc.product_id WHERE sc.storecategories_id = 0 AND parent_id=0';
        
        expSession::set('product_export_query',$sql);
        
        $page = new expPaginator(array(
            'model_field'=>'product_type',
            'sql'=>$sql,
            'controller'=>$this->params['controller'],
            'action'=>$this->params['action'],
            'columns'=>array('Model #'=>'model','Product Name'=>'title','Price'=>'base_price'),
            ));
            
        assign_to_template(array('page'=>$page, 'moduletitle'=>'Uncategorized Products'));
    }
    
    function manage() {
        expHistory::set('managable', $this->params);
        $page = new expPaginator(array(
            'model'=>'product',
            'where'=>'parent_id=0',
            'order'=>'title',
            'columns'=>array('Type'=>'product_type', 'Model #'=>'model', 'Product Name'=>'title','Price'=>'base_price')
            ));
        assign_to_template(array('page'=>$page));
    }
   
   function showallImpropercategorized() {
        expHistory::set('viewable', $this->params);
        
        $sql  = 'SELECT DISTINCT(p.id),p.product_type FROM '.DB_TABLE_PREFIX.'_product p JOIN '.DB_TABLE_PREFIX.'_product_storeCategories psc ON p.id = psc.product_id ';        
        $sql .= 'JOIN exponent_storeCategories sc ON psc.storecategories_id = sc.parent_id WHERE ';
        $sql .= 'p.parent_id=0 AND sc.parent_id != 0';
                              
        expSession::set('product_export_query',$sql);
        
        $page = new expPaginator(array(
            'model_field'=>'product_type',
            'sql'=>$sql,
            'controller'=>$this->params['controller'],
            'action'=>$this->params['action'],
            'columns'=>array('Model #'=>'model','Product Name'=>'title','Price'=>'base_price'),
            ));
            
        assign_to_template(array('page'=>$page, 'moduletitle'=>'Improperly Categorized Products'));
    }
    
    function exportMe() {
	
		redirect_to(array('controller'=>'report','action'=>'batch_export','applytoall'=>true));
	  
    }
    
    function showallByManufacturer() {
        global $template;
        
        expHistory::set('viewable', $this->params);
        
        $page = new expPaginator(array(
            'model'=>'product',
            'where'=>'companies_id='.$this->params['id'] . ' AND parent_id=0',
            'default'=>'Product Name',
            'columns'=>array('Model #'=>'model','Product Name'=>'title','Price'=>'base_price')
            ));
        
        $company = new company($this->params['id']);
        
        assign_to_template(array('page'=>$page, 'company'=>$company));
    }

    function showallManufacturers() {
        global $db;
        expHistory::set('viewable', $this->params);
        $sql = 'SELECT comp.* FROM '.DB_TABLE_PREFIX.'_companies as comp JOIN '.DB_TABLE_PREFIX.'_product AS prod ON prod.companies_id = comp.id WHERE parent_id=0 GROUP BY comp.title ORDER BY comp.title;';
        $manufacturers = $db->selectObjectsBySql($sql);
        assign_to_template(array('manufacturers'=>$manufacturers));
    }
    
    function show() {
        global $db, $order, $template, $user;
        
        $classname = $db->selectValue('product', 'product_type', 'id='.$this->params['id']);
        $product = new $classname($this->params['id'], true, true);
        
        if ($product->active_type == 1)
        {
            $product_type->user_message = "This product is temporarily unavailable for purchase.";   
        }elseif ($product->active_type == 2 && !($user->is_admin || $user->is_acting_admin))
        {
            flash("error", $product->title ." is curently unavailable.");
            expHistory::back();   
        }elseif ($product->active_type == 2 && ($user->is_admin || $user->is_acting_admin))
        {
            $product_type->user_message = $product->title ." is curently marked as unavailable for purchase or display.  Normal users will not see this product.";
        }
        expHistory::set('viewable', $this->params);    
        
        // $parent = isset($this->params['cat']) ? intval($this->params['cat']) : $default_id;
        // $category = new storeCategory($parent);
        // $this->grabConfig($category);
                                                                      
        $product_type = new $product->product_type($product->id, false, false);
        //eDebug($product_type);    
        //if we're trying to view a child product directly, then we redirect to it's parent show view
        if (!empty($product->parent_id)) redirect_to(array('controller'=>'store','action'=>'showByTitle','title'=>$product->sef_url));
        
        foreach ($product->crosssellItem as &$csi) {
            $csi->getAttachableItems();
        }
         
        $tpl = $product_type->getForm('show');
        
        if (!empty($tpl)) $template = new controllerTemplate($this, $tpl);
        $this->grabConfig();     
		
		assign_to_template(array('config'=>$this->config, 'product'=>$product, 'last_category'=>$order->lastcat));
    }
    
    function showByTitle() {
		
        global $order, $template, $user;
        //need to add a check here for child product and redirect to parent if hit directly by ID
        expHistory::set('viewable', $this->params);
		
        $product = new product(addslashes($this->params['title']));
        $product_type = new $product->product_type($product->id);
        $product_type->title         = expString::parseAndTrim($product_type->title,true);
        $product_type->image_alt_tag = expString::parseAndTrim($product_type->image_alt_tag,true);
         
        //if we're trying to view a child product directly, then we redirect to it's parent show view
        //bunk URL, no product found
        if(empty($product->id))
        {
            redirect_to(array('controller'=>'notfound','action'=>'page_not_found','title'=>$this->params['title']));            
        }
        if (!empty($product->parent_id)) redirect_to(array('controller'=>'store','action'=>'showByTitle','title'=>$product->sef_url));
        if ($product->active_type == 1)
        {
            $product_type->user_message = "This product is temporarily unavailable for purchase.";   
        }elseif ($product->active_type == 2 && !($user->is_admin || $user->is_acting_admin))
        {
            flash("error", $product->title ." is curently unavailable.");
            expHistory::back();   
        }elseif ($product->active_type == 2 && ($user->is_admin || $user->is_acting_admin))
        {
            $product_type->user_message = $product->title ." is curently marked as unavailable for purchase or display.  Normal users will not see this product.";
        }
        foreach ($product_type->crosssellItem as &$csi) {
            $csi->getAttachableItems();
        }
        //eDebug($product->crosssellItem);
        
        $tpl = $product_type->getForm('show');
        //eDebug($product);
        if (!empty($tpl)) $template = new controllerTemplate($this, $tpl);
        $this->grabConfig();     
		
		assign_to_template(array('config'=>$this->config, 'product'=>$product_type, 'last_category'=>$order->lastcat));
    }

    function showByModel() {
        global $order, $template, $db;
        
        expHistory::set('viewable', $this->params);
        $product = new product();
        $model = $product->find("first", 'model="'.$this->params['model'].'"');
        //eDebug($model);
        $product_type = new $model->product_type($model->id);
        //eDebug($product_type);
        $tpl = $product_type->getForm('show');
        if (!empty($tpl)) $template = new controllerTemplate($this, $tpl);
        //eDebug($template);
        $this->grabConfig();     
        assign_to_template(array('config'=>$this->config, 'product'=>$product_type, 'last_category'=>$order->lastcat));
    }

    
    function showallSubcategories() {
        global $db;
        
        expHistory::set('viewable', $this->params);
        $parent = isset($_REQUEST['cat']) ? $_REQUEST['cat'] : expSession::get('last_ecomm_category');
        $category = new storeCategory($parent);
        $categories = $category->getEcomSubcategories();
        $ancestors = $category->pathToNode();
        assign_to_template(array('categories'=>$categories, 'ancestors'=>$ancestors, 'category'=>$category));   
    }

    function showall_featured_products() {
        $order = 'title';
        $dir = 'ASC';
        
        $page = new expPaginator(array(
                'model_field'=>'product_type',
                'sql'=>'SELECT * FROM '.DB_TABLE_PREFIX.'_product WHERE is_featured=1',
                'limit'=>ecomconfig::getConfig('pagination_default'),
                'order'=>$order,
                'dir'=>$dir,
                'controller'=>$this->params['controller'],
                'action'=>$this->params['action'],
                'columns'=>array('Model #'=>'model','Product Name'=>'title','Price'=>'base_price'),
                ));
                
        assign_to_template(array('page'=>$page));   
    }
	
	 function showall_category_featured_products() {
	 
        $curcat = $this->category;
		
        $order = 'title';
        $dir = 'ASC';
        
        $page = new expPaginator(array(
                'model_field'=>'product_type',
                'sql'=>'SELECT * FROM '.DB_TABLE_PREFIX.'_product,'.DB_TABLE_PREFIX.'_product_storeCategories WHERE product_id = id and is_featured=1 and storecategories_id =' . $curcat->id,
                'limit'=>ecomconfig::getConfig('pagination_default'),
                'order'=>$order,
                'dir'=>$dir,
                'controller'=>$this->params['controller'],
                'action'=>$this->params['action'],
                'columns'=>array('Model #'=>'model','Product Name'=>'title','Price'=>'base_price'),
                ));
                
        assign_to_template(array('page'=>$page));   
    }
    
    function showTopLevel() {
        $category = new storeCategory(null,false,false);
        //$categories = $category->getEcomSubcategories();
        $categories = $category->getTopLevel(null, false, true);
        $ancestors = $this->category->pathToNode();   
        $curcat = $this->category;

        assign_to_template(array('categories'=>$categories,'curcat'=>$curcat,'topcat'=>@$ancestors[0]));
    }
    
    function showTopLevel_images() {
        global $user;
        $count_sql_start = 'SELECT COUNT(DISTINCT p.id) FROM '.DB_TABLE_PREFIX.'_product p ';
        $sql_start  = 'SELECT DISTINCT p.* FROM '.DB_TABLE_PREFIX.'_product p ';            
        $sql = 'JOIN '.DB_TABLE_PREFIX.'_product_storeCategories sc ON p.id = sc.product_id ';
        $sql .= 'WHERE ';
        if ( !($user->is_admin || $user->is_acting_admin) ) $sql .= '(p.active_type=0 OR p.active_type=1)';//' AND ' ;
        //$sql .= 'sc.storecategories_id IN (';
        //$sql .= 'SELECT id FROM '.DB_TABLE_PREFIX.'_storeCategories WHERE rgt BETWEEN '.$this->category->lft.' AND '.$this->category->rgt.')';         
        
        $count_sql = $count_sql_start . $sql;
        $sql = $sql_start . $sql;
        
        $order = 'sc.rank'; //$this->config['orderby'];
        $dir = 'ASC'; $this->config['orderby_dir'];
        
        $page = new expPaginator(array(
                'model_field'=>'product_type',
                'sql'=>$sql,
                'count_sql'=>$count_sql,
                'limit'=>$this->config['pagination_default'],
                'order'=>$order,
                'dir'=>$dir,
                'controller'=>$this->params['controller'],
                'action'=>$this->params['action'],
                'columns'=>array('Model #'=>'model','Product Name'=>'title','Price'=>'base_price'),
                ));
       
        $category = new storeCategory(null,false,false);
        //$categories = $category->getEcomSubcategories();
        $categories = $category->getTopLevel(null,false,true);
        $ancestors = $this->category->pathToNode();   
        $curcat = $this->category;

        assign_to_template(array('page'=>$page,'categories'=>$categories));
    }

	function showFullTree() {
        $category = new storeCategory(null,false,false);
        //$categories = $category->getEcomSubcategories();
        $categories = $category->getFullTree();
        $ancestors = $this->category->pathToNode();   
        $curcat = $this->category;

        assign_to_template(array('categories'=>$categories,'curcat'=>$curcat,'topcat'=>@$ancestors[0]));
    }
    
    function ecom_search() {

    }
    
    function billing_config() {

    }
    
    function addContentToSearch() {
        global $db, $router;
        $model = new $this->basemodel_name();
        
        $total = $db->countObjects($model->table);
        
        $count = 1;
        for($i=0;$i<$total;$i+=100) {
            $orderby = 'id LIMIT '.($i+1).', 100';
            $content = $db->selectArrays($model->table, 'parent_id=0', $orderby);            
           
            foreach ($content as $cnt) {
                $origid = $cnt['id'];
                $prod = new product($cnt['id']);
                unset($cnt['id']);
                //$cnt['title'] = $cnt['title'].' - SKU# '.$cnt['model'];
                $cnt['title'] = (isset($prod->expFile['mainimage'][0]) ? '<img src="'.URL_FULL.'thumb.php?id='.$prod->expFile['mainimage'][0]->id.'&w=40&h=40&zc=1" style="float:left;margin-right:5px;" />':'') .$cnt['title']. (!empty($cnt['model']) ? ' - SKU#: '.$cnt['model']:'');
                $search_record = new search($cnt, false, false);
                $search_record->posted = empty($cnt['created_at']) ? null : $cnt['created_at'];
                $search_record->view_link = $router->makeLink(array('controller'=>$this->baseclassname, 'action'=>'showByTitle', 'title'=>$cnt['sef_url']));
                $search_record->ref_type = $this->basemodel_name;
                $search_record->ref_module = 'store';
                $search_record->category = 'Products';
    
                $search_record->original_id = $origid;
                //$search_record->location_data = serialize($this->loc);
                $search_record->save();
                $count += 1;
            }
        }
        return $count;
    }
    
    function search_by_model_form() {
        //do nothing...just show the view.
    }
    
    function edit() {
        global $db;
        expHistory::set('editable', $this->params);
        
        // first we need to figure out what type of ecomm product we are dealing with
        if (!empty($this->params['id'])) {
            // if we have an id lets pull the product type from the products table.
            $product_type = $db->selectValue('product', 'product_type', 'id='.$this->params['id']);
        } else {
            if (empty($this->params['product_type'])) redirect_to(array('controller'=>'store', 'action'=>'picktype')); 
            $product_type = $this->params['product_type'];             
        }
        
        if (!empty($this->params['id']))
        { 
            $record = new $product_type($this->params['id']);     
            if (!empty($this->user_input_fields) && !is_array($record->user_input_fields)) $record->user_input_fields = expUnserialize($record->user_input_fields);   
        }else{ 
            $record = new $product_type(); 
            $record->user_input_fields = array();
        } 
        
//        if (!empty($this->params['parent_id']))
         
        // get the product options and send them to the form
        $editable_options = array();
        //$og = new optiongroup();
        $mastergroups = $db->selectExpObjects('optiongroup_master', null, 'optiongroup_master');
        //eDebug($mastergroups,true);
        foreach($mastergroups as $mastergroup) {
            // if this optiongroup_master has already been made into an option group for this product
            // then we will grab that record now..if not, we will make a new one.
            $grouprec = $db->selectArray('optiongroup', 'optiongroup_master_id='.$mastergroup->id.' AND product_id='.$record->id);
            //if ($mastergroup->id == 9) eDebug($grouprec,true);
            //eDebug($grouprec);
            if (empty($grouprec)) {
                $grouprec['optiongroup_master_id'] = $mastergroup->id;
                $grouprec['title'] = $mastergroup->title;
                $group = new optiongroup($grouprec);
            } else {
                $group = new optiongroup($grouprec['id']);
            }

            $editable_options[$group->title] = $group;
          
            if (empty($group->option)) {
                foreach ($mastergroup->option_master as $optionmaster) {
                    $opt = new option(array('title'=>$optionmaster->title, 'option_master_id'=>$optionmaster->id), false, false);
                    $editable_options[$group->title]->options[] = $opt;
                }                 
            
            } else {
                if (count($group->option) == count($mastergroup->option_master)) {                
                    $editable_options[$group->title]->options = $group->option;
                } else {
                    // check for any new options or deleted since the last time we edited this product
                    foreach ($mastergroup->option_master as $optionmaster) {
                        $opt_id = $db->selectValue('option', 'id', 'option_master_id='.$optionmaster->id." AND product_id=".$record->id);
                        if (empty($opt_id)) {
                            $opt = new option(array('title'=>$optionmaster->title, 'option_master_id'=>$optionmaster->id), false, false);                            
                        } else {
                            $opt = new option($opt_id);
                        }
                        
                        $editable_options[$group->title]->options[] = $opt;
                    }
                }
            }
            //eDebug($editable_options[$group->title]);        
        }
        //die();
        
       uasort($editable_options,  array("optiongroup", "sortOptiongroups"));
                     
        // get the shipping options and their methods
        $shipping = new shipping();
        foreach ($shipping->available_calculators as $calcid=>$name) {
            $calc = new $name($calcid);
            $shipping_services[$calcid] = $calc->title;
            $shipping_methods[$calcid] = $calc->availableMethods();
        }
        
#        eDebug($shipping_services);
#        eDebug($shipping_methods);

//eDebug($record);
        //if new record and it's a child, then well set the child rank to be at the end
        if (empty($record->id) && $record->isChild()) 
        {               
            $record->child_rank = $db->max('product','child_rank',null,'parent_id=' . $record->parent_id) + 1;
        }
        //eDebug($record,true);
        
        $view='';
        $parent = null;
        if ((isset($this->params['parent_id']) && empty($record->id)))
        {
            //NEW child product
            $view = 'child_edit';
            $parent = new $product_type($this->params['parent_id'], false, true); 
            $record->parent_id = $this->params['parent_id'];
        }elseif ((!empty($record->id) && $record->parent_id!=0)) {
             //EDIT child product
            $view = 'child_edit';
            $parent = new $product_type($record->parent_id, false, true); 
        }else{
            $view = 'edit';
        }
        
        assign_to_template(array(
            'record'=>$record, 
            'parent'=>$parent,
            'form'=>$record->getForm($view), 
            'optiongroups'=>$editable_options, 
            'shipping_services'=> isset($shipping_services) ? $shipping_services : '', // Added implication since the shipping_services default value is a null
            'shipping_methods' => isset($shipping_methods)  ? $shipping_methods  : '',   // Added implication since the shipping_methods default value is a null
            //'status_display'=>$status_display->getStatusArray()
        ));
    }
    
    function copyProduct() {
        global $db;
    
        //expHistory::set('editable', $this->params);
        
        // first we need to figure out what type of ecomm product we are dealing with
        if (!empty($this->params['id'])) {
            // if we have an id lets pull the product type from the products table.
            $product_type = $db->selectValue('product', 'product_type', 'id='.$this->params['id']);
        } else {
            if (empty($this->params['product_type'])) redirect_to(array('controller'=>'store', 'action'=>picktype));
            $product_type = $this->params['product_type'];
        }
        
        $record = new $product_type($this->params['id']);
        // get the product options and send them to the form
        $editable_options = array();
        
        $mastergroups = $db->selectExpObjects('optiongroup_master', null, 'optiongroup_master');
        foreach($mastergroups as $mastergroup) {
            // if this optiongroup_master has already been made into an option group for this product
            // then we will grab that record now..if not, we will make a new one.
            $grouprec = $db->selectArray('optiongroup', 'optiongroup_master_id='.$mastergroup->id.' AND product_id='.$record->id);
            //eDebug($grouprec);
            if (empty($grouprec)) {
                $grouprec['optiongroup_master_id'] = $mastergroup->id;
                $grouprec['title'] = $mastergroup->title;
                $group = new optiongroup($grouprec);
            } else {
                $group = new optiongroup($grouprec['id']);
            }

            $editable_options[$group->title] = $group;

            if (empty($group->option)) {
                foreach ($mastergroup->option_master as $optionmaster) {
                    $opt = new option(array('title'=>$optionmaster->title, 'option_master_id'=>$optionmaster->id), false, false);
                    $editable_options[$group->title]->options[] = $opt;
                }
            } else {
                if (count($group->option) == count($mastergroup->option_master)) {                
                    $editable_options[$group->title]->options = $group->option;
                } else {
                    // check for any new options or deleted since the last time we edited this product
                    foreach ($mastergroup->option_master as $optionmaster) {
                        $opt_id = $db->selectValue('option', 'id', 'option_master_id='.$optionmaster->id." AND product_id=".$record->id);
                        if (empty($opt_id)) {
                            $opt = new option(array('title'=>$optionmaster->title, 'option_master_id'=>$optionmaster->id), false, false);                            
                        } else {
                            $opt = new option($opt_id);
                        }
                        
                        $editable_options[$group->title]->options[] = $opt;
                    }
                }
            }
        }
        
        // get the shipping options and their methods
        $shipping = new shipping();
        foreach ($shipping->available_calculators as $calcid=>$name) {
            $calc = new $name($calcid);
            $shipping_services[$calcid] = $calc->title;
            $shipping_methods[$calcid] = $calc->availableMethods();
        }
        
        $record->original_id = $record->id;
        $record->original_model = $record->model;
        $record->id = NULL;
        $record->sef_url = NULL;
        $record->previous_id = NULL; 
        
        if ($record->isChild()) 
        {            
            $record->child_rank = $db->max('product','child_rank',null,'parent_id=' . $record->parent_id) + 1;
        }
        
        assign_to_template(array(
            'record'=>$record, 
            'parent'=>new $product_type($record->parent_id, false, true),
            'form'=>$record->getForm($record->parent_id==0?'edit':'child_edit'),
            'optiongroups'=>$editable_options, 
            'shipping_services'=>$shipping_services,
            'shipping_methods'=>$shipping_methods
        ));
    }
    
    function picktype() {
        $prodfiles = storeController::getProductTypes();
        $products = array();
        foreach($prodfiles as $filepath=>$classname) {
            $prodObj = new $classname();
            $products[$classname] = $prodObj->product_name;
        }
        assign_to_template(array('product_types'=>$products));
    }
    
    function update() {
        global $db;
       // eDebug($this->params['optiongroups'],true);
        //eDebug($this->params,true);
        $product_type = isset($this->params['product_type']) ? $this->params['product_type'] : 'product';
        $record = new $product_type();
        
        
        // find required shipping method if needed
        if ($this->params['required_shipping_calculator_id'] > 0) {
            $record->required_shipping_method = $this->params['required_shipping_methods'][$this->params['required_shipping_calculator_id']];
        } else {
            $this->params['required_shipping_calculator_id'] = 0;
        }
        
        //extra fields
        foreach ($this->params['extra_fields_name'] as $xkey=>$xfield)
        {               
            if (!empty($xfield) /*&& !empty($this->params['extra_fields_value'][$xkey])*/) $record->extra_fields[] = array('name'=>$xfield, 'value'=>$this->params['extra_fields_value'][$xkey]); 
        }
        if (is_array($record->extra_fields)) $record->extra_fields = serialize($record->extra_fields);
        else unset($record->extra_fields);
        
        //user input fields                                                                     
        if (isset($this->params['user_input_use']) && is_array($this->params['user_input_use'])){        
            foreach ($this->params['user_input_use'] as $ukey=>$ufield)
            {  
                //eDebug($ufield);
                $record->user_input_fields[] = array('use'=>$this->params['user_input_use'][$ukey], 'name'=>$this->params['user_input_name'][$ukey], 'is_required'=>$this->params['user_input_is_required'][$ukey], 'min_length'=>$this->params['user_input_min_length'][$ukey],'max_length'=>$this->params['user_input_max_length'][$ukey],'description'=>$this->params['user_input_description'][$ukey]);
            }
            $record->user_input_fields = serialize($record->user_input_fields);
        }else{
            $record->user_input_fields = serialize(array());    
        }
        
        //check if we're saving a newly copied product and if we create children also
        $originalId = isset($this->params['original_id']) && isset($this->params['copy_children']) ? $this->params['original_id'] : 0;
        $originalModel = isset($this->params['original_model']) && isset($this->params['copy_children']) ? $this->params['original_model'] : 0;
        
        if (!empty($record->parent_id)) $record->sef_url = '';  //if child, set sef_url to nada
        $record->update($this->params);
        //eDebug($this->params);
        //eDebug($record, true);
               
        if (isset($record->id)) {
            
            $record->saveCategories($this->params['storeCategory']); 
            //eDebug ($this->params['optiongroups'],true);
            if (!empty($this->params['optiongroups'])) {
                //eDebug("OrigId:" . $originalId);
                foreach ($this->params['optiongroups'] as $title=>$group) {
                    if (isset($this->params['original_id']) && $this->params['original_id'] != 0) $group['id'] = '';  //for copying products  
                    //eDebug($group);
                    $optiongroup = new  optiongroup($group);
                    $optiongroup->product_id = $record->id;                                
                    $optiongroup->save();
                    
                    //eDebug($optiongroup,true);
                    foreach ($this->params['optiongroups'][$title]['options'] as $opt_title=>$opt) {
                        if (isset($this->params['original_id']) && $this->params['original_id'] != 0) $opt['id'] = ''; //for copying products
                       // eDebug($opt);
                        $opt['product_id'] = $record->id;
                        $opt['is_default'] = false;
                        $opt['title'] = $opt_title;
                        $opt['optiongroup_id'] = $optiongroup->id;
                        if (isset($this->params['defaults'][$title]) && $this->params['defaults'][$title] == $opt['title']) {
                            $opt['is_default'] = true;
                        }
                        
                        $option = new option($opt);                    
                        $option->save();
                    }
                }
            }
            
            if (!empty($this->params['relatedProducts']) && (empty($originalId) || !empty($this->params['copy_related']))) {
                $relprods = $db->selectObjects('crosssellItem_product',"product_id=".$record->id);
                $db->delete('crosssellItem_product','product_id='.$record->id);
                foreach ($this->params['relatedProducts'] as $key=>$prodid) {
                    $ptype = new product($prodid);
                    $tmp->product_id = $record->id;
                    $tmp->crosssellItem_id = $prodid;
                    $tmp->product_type = $ptype->product_type;
                    $db->insertObject($tmp,'crosssellItem_product');
                    
                   // if (isset($this->params['relateBothWays']) && in_array($prodid,$this->params['relateBothWays']))
                    if (isset($this->params['relateBothWays'][$prodid])) {
                        $tmp->crosssellItem_id = $record->id;
                        $tmp->product_id = $prodid;
                        $tmp->product_type = $ptype->product_type;
                        $db->insertObject($tmp,'crosssellItem_product');
                    }
                    //}
                }
            }
            
            if (!empty($originalId) && !empty($this->params['copy_children']))
            {
                $origProd = new $product_type($originalId);
                $children = $origProd->find('all', 'parent_id=' . $originalId);
                foreach ($children as $child)
                {
                    unset($child->id);
                    $child->parent_id = $record->id;
                    $child->title = $record->title;
                    $child->sef_url = '';
                    if (isset($this->params['adjust_child_price']) && isset($this->params['new_child_price']) && is_numeric($this->params['new_child_price']))
                    {
                        $child->base_price = $this->params['new_child_price'];
                    }
                    if (!empty($originalModel))
                    {
                        /*eDebug($originalModel);
                        eDebug($record->model);
                        eDebug($child->model);*/
                        $child->model = str_ireplace($originalModel, $record->model, $child->model);    
                        //eDebug($child->model);  
                    }                                                                           
                    $child->save();
                }
            }
        }        
        
        $record->addContentToSearch();
        
        if($record->parent_id != 0 )
        {
            $parent = new $product_type($record->parent_id,false,false);
            flash("message","Child product saved.");                
            redirect_to(array('controller'=>'store','action'=>'showByTitle','title'=>$parent->sef_url));
        }
        else if(isset($this->params['original_id']) )
        {
            flash("message","Product copied and saved. You are now viewing your new product.");                
            redirect_to(array('controller'=>'store','action'=>'showByTitle','title'=>$record->sef_url));
        }
        else
        {            
            flash("message","Product saved.");                
            redirect_to(array('controller'=>'store','action'=>'showByTitle','title'=>$record->sef_url));
        }        
    }
    
    function delete() {
        global $db;
        
        if (empty($this->params['id'])) return false;
        $product_type = $db->selectValue('product', 'product_type', 'id='.$this->params['id']);
        $product = new $product_type($this->params['id'], true, false);
        //eDebug($product_type);  
        //eDebug($product, true);
        //if (!empty($product->product_type_id)) {
        //$db->delete($product_type, 'id='.$product->product_id);
        //}
        
        $db->delete('option','product_id='.$product->id." AND optiongroup_id IN (SELECT id from ".DB_TABLE_PREFIX."_optiongroup WHERE product_id=".$product->id.")");
        $db->delete('optiongroup', 'product_id='.$product->id);
        //die();
        $db->delete('product_storeCategories', 'product_id='.$product->id.' AND product_type="'.$product_type.'"');
        if ($product->hasChildren())
        {
            $this->deleteChildren();    
        }    
        
        $product->delete();
        
        flash('message', 'Product deleted successfully.');
        expHistory::back();
    }
    
    function quicklinks() {
        //we need to get the total items in the cart so that if the user at least 1 item in order to check out.
        
        $itemcount = 1;
        //eDebug($itemcount);
        assign_to_template(array("itemcount"=>$itemcount));
    }
    
    static public function getProductTypes() {        
        $paths = array(
            BASE.'framework/modules/ecommerce/products/datatypes',
        );
    
        $products = array();
        foreach ($paths as $path) {
            if (is_readable($path)) {
                $dh = opendir($path);
                while (($file = readdir($dh)) !== false) {
                    if (is_readable($path.'/'.$file) && substr($file, -4) == '.php') {
                        $classname = substr($file, 0, -4);
                        $products[$path.'/'.$file] = $classname;
                    }
                }
            }
        }
        
        return $products;
    }
    
    function metainfo() {
        global $router;
        if (empty($router->params['action'])) return false;
        
        // figure out what metadata to pass back based on the action we are in.
        $action = $_REQUEST['action'];
        $metainfo = array('title'=>'', 'keywords'=>'', 'description'=>'');
        switch($action) {
            case 'show':
            case 'showall': //category page
                //$cat = new storeCategory(isset($_REQUEST['title']) ? $_REQUEST['title']: $_REQUEST['id']);
                $cat = $this->category;
                if (!empty($cat)) {
                    $metainfo['title'] = empty($cat->meta_title) ? $cat->title : $cat->meta_title;
                    $metainfo['keywords'] = empty($cat->meta_keywords) ? $cat->title : strip_tags($cat->meta_keywords);
                    $metainfo['description'] = empty($cat->meta_description) ? strip_tags($cat->body) : strip_tags($cat->meta_description);
                }              
            break;
            case 'showByTitle':                
                $prod = new product(isset($_REQUEST['title']) ? $_REQUEST['title']: $_REQUEST['id']);
                if (!empty($prod)) {
                    $metainfo['title'] = empty($prod->meta_title) ? $prod->title : $prod->meta_title;
                    $metainfo['keywords'] = empty($prod->meta_keywords) ? $prod->title : strip_tags($prod->meta_keywords);
                    $metainfo['description'] = empty($prod->meta_description) ? strip_tags($prod->body) : strip_tags($prod->meta_description);
                }              
            break;
            default:
                $metainfo = array('title'=>$this->displayname()." - ".SITE_TITLE, 'keywords'=>SITE_KEYWORDS, 'description'=>SITE_DESCRIPTION);
        }
        
        // Remove any quotes if there are any.
        $metainfo['title']       = expString::parseAndTrim($metainfo['title'],true);
        $metainfo['description'] = expString::parseAndTrim($metainfo['description'],true);
        $metainfo['keywords']    = expString::parseAndTrim($metainfo['keywords'],true);
                
        return $metainfo;
    }
       
    public function deleteChildren() {
        //eDebug($data[0],true);
        //if($id!=null) $this->params['id'] = $id;
        //eDebug($this->params,true);        
        $product = new product($this->params['id']);
        //$product = $product->find("first", "previous_id =" . $previous_id);
        //eDebug($product, true);
        if (empty($product->id)) // || empty($product->previous_id)) 
        {
            flash('error', 'There was an error deleting the child products.');
            expHistory::back(); 
        }
        $childrenToDelete = $product->find('all','parent_id='.$product->id);
        foreach ($childrenToDelete as $ctd)
        {
            //fwrite($lfh, "Deleting:" . $ctd->id . "\n");                             
            $ctd->delete();
        }
    }
    
    function search_by_model_old() {
        // get the search terms
        $terms = $this->params['search_string'];

        $sql = "model like '%".$terms."%'";

        $page = new expPaginator(array(
            'model'=>'product',
            'controller'=>$this->params['controller'],
            'action'=>$this->params['action'],
            'where'=>$sql,
            'order'=>'title',
            'dir'=>'DESC',
            'columns'=>array('Model #'=>'model','Product Name'=>'title','Price'=>'base_price'),
            ));
        
        assign_to_template(array('page'=>$page, 'terms'=>$terms));
    }
    
    function search_by_model() {
        global $db, $user;
        
        $sql = "select DISTINCT(p.id) as id, p.title, model from " . $db->prefix . "product as p WHERE ";
        if ( !($user->is_admin || $user->is_acting_admin) ) $sql .= '(p.active_type=0 OR p.active_type=1) AND ' ;

        
        //if first character of search is a -, then we do a wild card, else from beginning
        if($this->params['query'][0] == '-')
        {
            $sql .= " p.model LIKE '%" . $this->params['query'];
        }        
        else
        {
            $sql .= " p.model LIKE '" . $this->params['query'];
        }
        
        $sql .= "%' AND p.parent_id=0 GROUP BY p.id ";    
        $sql .= "order by p.model ASC LIMIT 30";
        $res = $db->selectObjectsBySql($sql);
        //eDebug($sql);
        $ar = new expAjaxReply(200, gt('Here\'s the items you wanted'), $res);
        $ar->send();
    }
    
    public function search() {
        global $db, $user;
        //$this->params['query'] = str_ireplace('-','\-',$this->params['query']);
        $terms = explode(" ",$this->params['query']);
        $sql = "select DISTINCT(p.id) as id, p.title, model, sef_url, f.id as fileid, match (p.title,p.body) against ('" . $this->params['query'] .  "*' IN BOOLEAN MODE) as score ";
        $sql .= "  from " . $db->prefix . "product as p INNER JOIN " . 
        $db->prefix . "content_expFiles as cef ON p.id=cef.content_id INNER JOIN " . $db->prefix . 
        "expFiles as f ON cef.expFiles_id = f.id WHERE ";
        if ( !($user->is_admin || $user->is_acting_admin) ) $sql .= '(p.active_type=0 OR p.active_type=1) AND ' ;
        $sql .= " match (p.title,p.body) against ('" . $this->params['query'] .  "*' IN BOOLEAN MODE) AND p.parent_id=0  GROUP BY p.id "; 
        $sql .= "order by score desc LIMIT 10";
        
        $firstObs = $db->selectObjectsBySql($sql);        
        foreach($firstObs as $set)
        {
            $set->weight = 1;     
            
            unset($set->score);       
            $res[$set->model] = $set;    
        }
        
        $sql = "select DISTINCT(p.id) as id, p.title, model, sef_url, f.id as fileid  from " . $db->prefix . "product as p INNER JOIN " . 
        $db->prefix . "content_expFiles as cef ON p.id=cef.content_id INNER JOIN " . $db->prefix . 
        "expFiles as f ON cef.expFiles_id = f.id WHERE ";
        if ( !($user->is_admin || $user->is_acting_admin) ) $sql .= '(p.active_type=0 OR p.active_type=1) AND ' ;
        $sql .= " (p.model like '%" . $this->params['query'] . "%' ";
        $sql .= " OR p.title like '%" . $this->params['query'] . "%') ";
        $sql .= " AND p.parent_id=0 GROUP BY p.id LIMIT 10"; 
        
        $secondObs = $db->selectObjectsBySql($sql);        
        foreach($secondObs as $set)
        { 
            $set->weight = 2;
            $res[$set->model] = $set;    
        }
                               
        $sql = "select DISTINCT(p.id) as id, p.title, model, sef_url, f.id as fileid  from " . $db->prefix . "product as p INNER JOIN " . 
        $db->prefix . "content_expFiles as cef ON p.id=cef.content_id INNER JOIN " . $db->prefix . 
        "expFiles as f ON cef.expFiles_id = f.id WHERE ";
        if ( !($user->is_admin || $user->is_acting_admin) ) $sql .= '(p.active_type=0 OR p.active_type=1) AND ' ;
        $sql .= " (p.model like '" . $this->params['query'] . "%' ";
        $sql .= " OR p.title like '" . $this->params['query'] . "%') ";
        $sql .= " AND p.parent_id=0 GROUP BY p.id LIMIT 10"; 
        
        $thirdObs = $db->selectObjectsBySql($sql);        
        foreach($thirdObs as $set)
        {
            if(strcmp(strtolower(trim($this->params['query'])),strtolower(trim($set->model))) == 0 ) $set->weight = 10;         
            else if(strcmp(strtolower(trim($this->params['query'])),strtolower(trim($set->title))) == 0 ) $set->weight = 9;
            else $set->weight = 3;
            $res[$set->model] = $set;    
        }
     
        function sortSearch($a,$b) {
            return ($a->weight == $b->weight ? 0 : ($a->weight < $b->weight) ? 1  : -1);
        }
                
        if(count($terms))
        {        
            foreach($res as $r)
            {        
                foreach($terms as $term)
                {        
                    if(stristr($r->title,$term)) $res[$r->model]->weight = $res[$r->model]->weight + 1;    
                }  
            }
        }        
        usort($res,'sortSearch');        
        
        $ar = new expAjaxReply(200, gettext('Here\'s the items you wanted'), $res);
        $ar->send();
    } 
    
     public function searchNew() {
        global $db, $user;
        //$this->params['query'] = str_ireplace('-','\-',$this->params['query']);
        $sql = "select DISTINCT(p.id) as id, p.title, model, sef_url, f.id as fileid, ";
        $sql .= "match (p.title,p.model,p.body) against ('" . $this->params['query'] . "*' IN BOOLEAN MODE) as relevance, ";
        $sql .= "CASE when p.model like '" . $this->params['query'] . "%' then 1 else 0 END as modelmatch, "; 
        $sql .= "CASE when p.title like '%" . $this->params['query'] . "%' then 1 else 0 END as titlematch ";        
        $sql .= "from " . $db->prefix . "product as p INNER JOIN " . 
        $db->prefix . "content_expFiles as cef ON p.id=cef.content_id INNER JOIN " . $db->prefix . 
        "expFiles as f ON cef.expFiles_id = f.id WHERE ";
        if ( !($user->is_admin || $user->is_acting_admin) ) $sql .= '(p.active_type=0 OR p.active_type=1) AND ' ;
        $sql .= " match (p.title,p.model,p.body) against ('" . $this->params['query'] . "*' IN BOOLEAN MODE) AND p.parent_id=0 "; 
        $sql .= " HAVING relevance > 0 ";
        //$sql .= "GROUP BY p.id "; 
        $sql .= "order by modelmatch,titlematch,relevance desc LIMIT 10";
        
        eDebug($sql);
        $res = $db->selectObjectsBySql($sql);
        eDebug($res,true);
        $ar = new expAjaxReply(200, gt('Here\'s the items you wanted'), $res);
        $ar->send();
    }
    
    function batch_process() {
	
        $os = new order_status();
        $oss = $os->find('all');        
        $order_status =  array();
        $order_status[-1] = '';
        foreach ($oss as $status)
        {
            $order_status[$status->id] = $status->title;
        }    
        assign_to_template(array('order_status'=>$order_status));
    }
    
    function process_orders() {
        /*
          Testing
        */
        /*echo "Here?";
        $inv = 30234;
        $req = 'a29f9shsgh32hsf80s7';        
        $amt = 101.00;
        for($count=1;$count<=25;$count+=2)
        {   
            $data[2] = $inv + $count;
            $amt += $count*$count;
            $successSet[$count]['message'] = "Sucessfully imported row " . $count . ", order: " . $data[2] . "<br/>";                
            $successSet[$count]['order_id'] = $data[2];
            $successSet[$count]['amount'] = $amt;
            $successSet[$count]['request_id'] = $req;
            $successSet[$count]['reference_id'] = $req;
            $successSet[$count]['authorization_code'] = $req;
            $successSet[$count]['shipping_tracking_number'] = '1ZNF453937547';    
            $successSet[$count]['carrier'] = 'UPS';
        }
        for($count=2;$count<=25;$count+=2)
        {   
            $data[2] = $inv + $count;                
            $amt += $count*$count;        
            $errorSet[$count]['error_code'] = '42';
            $errorSet[$count]['message'] = "No go for some odd reason. Try again.";
            $errorSet[$count]['order_id'] = $data[2];
            $errorSet[$count]['amount'] = $amt;
        }
        
        assign_to_template(array('errorSet'=>$errorSet, 'successSet'=>$successSet));     
        return;*/
        
        ###########
        
        global $db;
        $template = get_template_for_action(new orderController(), 'setStatus', $this->loc);
         
        //eDebug($_FILES);
        //eDebug($this->params,true); 
        set_time_limit(0);
        //$file = new expFile($this->params['expFile']['batch_process_upload'][0]);
        if(!empty($_FILES['batch_upload_file']['error']))
        {
            flash('error','There was an error uploading your file.  Please try again.');
            redirect_to(array('controller'=>'store','action'=>'batch_process'));        
        }
        
        $file->path = $_FILES['batch_upload_file']['tmp_name'];
        echo "Validating file...<br/>";
        
        $checkhandle = fopen($file->path, "r");
        $checkdata = fgetcsv($checkhandle, 10000, ",");
        $fieldCount = count($checkdata);        
        $count = 1;
        while (($checkdata = fgetcsv($checkhandle, 10000, ",")) !== FALSE) {
            $count++;
            if (count($checkdata) != $fieldCount) 
            {                   
                echo "Line ". $count ." of your CSV import file does not contain the correct number of columns.<br/>";
                echo "Found " . $fieldCount . " header fields, but only " . count($checkdata) ." field in row " . $count . " Please check your file and try again.";
                exit();
            }
        }        
        fclose($checkhandle);
        
        echo "<br/>CSV File passed validation...<br/><br/>Detecting carrier type....<br/>";
        //exit();
        $handle = fopen($file->path, "r");
        $data = fgetcsv($handle, 10000, ",");
        //eDebug($data);      
        $dataset = array();
        $carrier = '';
        if (trim($data[0]) == 'ShipmentInformationShipmentID') 
        {
            echo "Detected UPS file...<br/>";
            $carrier = "UPS";
            $carrierTrackingLink = "http://wwwapps.ups.com/etracking/tracking.cgi?TypeOfInquiryNumber=T&InquiryNumber1=";
        }
        elseif(trim($data[0]) == 'PIC') 
        {
            echo "Detected United States Post Service file...<br/>";
            $carrier = "USPS";       
            $carrierTrackingLink = "http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum=";
        }        
        
        //eDebug($carrier);
        $count = 1;
        $errorSet = array();
        $successSet = array();
               
        $oo = new order();
         
        while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
            $count++;
            $originalOrderId = $data[2];
            $data[2] = intval($data[2]);
            $order = null;  
            $bm = null;
            $transactionState = null;
            
            //check for valid order number - if not present or not order, fail and continue with next record
            if (isset($data[2]) && !empty($data[2]))
            {                
                $order = $oo->findBy('invoice_id',$data[2]);  
                if (empty($order->id)) 
                {
                    $errorSet[$count]['message'] = $originalOrderId . " is not a valid order in this system.";
                    $errorSet[$count]['order_id'] = $originalOrderId;  
                    continue;
                }
            }else{
                $errorSet[$count]['message'] = "Row " . $count . " has no order number.";
                $errorSet[$count]['order_id'] = "N/A";  
                continue;
            }
            
            /*we have a valid order, so let's see what we can do: */
            
            //set status of order to var
            $currentStat = $order->order_status;
            //eDebug($currentStat,true);
            
            //-- check the order for a closed status - if so, do NOT process or set shipping
            if($currentStat->treat_as_closed == true)
            {
                $errorSet[$count]['message'] =  "This is currently a closed order. Not processing.";
                $errorSet[$count]['order_id'] = $data[2];
                continue;
            }
            
            //ok, if we made it here we have a valid order that is "open"
            //we'll try to capture the transaction if it's in an authorized state, but set shipping regardless
            if(isset($order->billingmethod[0]))
            {
                $bm = $order->billingmethod[0];  
                $transactionState = $bm->transaction_state;
            } 
            else 
            {
                $bm = null;   
                $transactionState = '';
            }
            
            if ($transactionState == 'authorized')
            {  
                //eDebug($order,true);
                $calc = $bm->billingcalculator->calculator;
                $calc->config = $bm->billingcalculator->config;
                if (method_exists($calc,'delayed_capture'))
                {                
                    //$result = $calc->delayed_capture($bm,$bm->billing_cost);
                    $result = $calc->delayed_capture($bm,$order->grand_total);                    
                    if ($result->errorCode == 0) 
                    {
                        //we've succeeded.  transaction already created and billing info updated.
                        //just need to set the order shipping info, check and see if we send user an email, and set statuses.  
                        //shipping info:                                      
                        $successSet[$count]['order_id'] = $data[2];                    
                        $successSet[$count]['message'] = "Sucessfully captured order " . $data[2] . " and set shipping information.";
                        $successSet[$count]['amount'] = $order->grand_total;
                        $successSet[$count]['request_id'] = $result->request_id;
                        $successSet[$count]['reference_id'] = $result->PNREF;
                        $successSet[$count]['authorization_code'] = $result->AUTHCODE;                         
                        $successSet[$count]['shipping_tracking_number'] = $data[0];    
                        $successSet[$count]['carrier'] = $carrier;
                    }
                    else
                    {   
                        //failed capture, so we report the error but still set the shipping information
                        //because it's already out the door
                        //$failMessage = "Attempted to delay capture order " . $data[2] . " and it failed with the following error: " . $result->errorCode . " - " .$result->message;   
                        //if the user seelected to set a different status for failed orders, set it here.
                        /*if(isset($this->params['order_status_fail'][0]) && $this->params['order_status_fail'][0] > -1)
                        {
                            $change = new order_status_changes();
                            // save the changes
                            $change->from_status_id = $order->order_status_id;
                            //$change->comment = $this->params['comment'];
                            $change->to_status_id = $this->params['order_status_fail'][0];
                            $change->orders_id = $order->id;
                            $change->save();
                            
                            // update the status of the order
                            $order->order_status_id = $this->params['order_status_fail'][0];
                            $order->save();                             
                        }*/
                        $errorSet[$count]['error_code'] = $result->errorCode;
                        $errorSet[$count]['message'] = "Capture failed: " . $result->message . "<br/>Setting shipping information.";
                        $errorSet[$count]['order_id'] = $data[2];
                        $errorSet[$count]['amount'] = $order->grand_total;
                        $errorSet[$count]['shipping_tracking_number'] = $data[0];    
                        $errorSet[$count]['carrier'] = $carrier;
                        //continue;   
                    }
                }
                else
                {
                    //dont suppose we do anything here, as it may be set to approved manually 
                    //$errorSet[$count] = "Order " . $data[2] . " does not use a billing method with delayed capture ability.";  
                    $successSet[$count]['message'] = 'No capture processing available for order:' . $data[2] . '. Setting shipping information.';
                    $successSet[$count]['order_id'] = $data[2];
                    $successSet[$count]['amount'] = $order->grand_total;                        
                    $successSet[$count]['shipping_tracking_number'] = $data[0];    
                    $successSet[$count]['carrier'] = $carrier;            
                }
            }
            //if we hit this else, it means we have an order that is not in an authorized state
            //so we do not try to process it = still set shipping though.
            else
            {
                $successSet[$count]['message'] = 'No processing necessary for order:' . $data[2] . '. Setting shipping information.';
                $successSet[$count]['order_id'] = $data[2];
                $successSet[$count]['amount'] = $order->grand_total;
                $successSet[$count]['shipping_tracking_number'] = $data[0];    
                $successSet[$count]['carrier'] = $carrier;                                    
            }                
            
            $order->shipped = time();
            $order->shipping_tracking_number = $data[0];                     
            $order->save();
                        
            $s = array_pop($order->shippingmethods);
            $sm = new shippingmethod($s->id);
            $sm->carrier = $carrier;
            $sm->save();
                                      
            //statuses and email
            if(isset($this->params['order_status_success'][0]) && $this->params['order_status_success'][0] > -1)
            {
                $change = new order_status_changes();
                // save the changes
                $change->from_status_id = $order->order_status_id;
                //$change->comment = $this->params['comment'];
                $change->to_status_id = $this->params['order_status_success'][0];
                $change->orders_id = $order->id;
                $change->save();
                
                // update the status of the order
                $order->order_status_id = $this->params['order_status_success'][0];
                $order->save();                        
                       
                // email the user if we need to
                if (!empty($this->params['email_customer'])) {
                    $email_addy = $order->billingmethod[0]->email;
                    if (!empty($email_addy)) 
                    {
                        $from_status = $db->selectValue('order_status', 'title', 'id='.$change->from_status_id);
                        $to_status = $db->selectValue('order_status', 'title', 'id='.$change->to_status_id);
                        $template->assign(
                        //assign_to_template(
                            array(
                                'comment'=>$change->comment, 
                                'to_status'=>$to_status, 
                                'from_status'=>$from_status, 
                                'order'=>$order, 
                                'date'=>date("F j, Y, g:i a"),
                                'storename'=>ecomconfig::getConfig('storename'),
                                'include_shipping'=>true,
                                'tracking_link'=>$carrierTrackingLink . $order->shipping_tracking_number,
                                'carrier'=>$carrier
                                )
                         );
                        
                        $html = $template->render();
                        $html .= ecomconfig::getConfig('footer');
                        
                        try{
                            $mail = new expMail();
                            $mail->quickSend(array(
                                'html_message'=>$html,
                                'text_message'=>str_replace("<br>", "\r\n", $template->render()),
                                'to'=>$email_addy,
                                'from'=>ecomconfig::getConfig('from_address'),
                                'subject'=>'Your Order Has Been Shipped (#'.$order->invoice_id.') - '.ecomconfig::getConfig('storename')
                            ));
                        }
                        catch (Exception $e)
                        {
                            //do nothing for now
                            eDebug("Email error:");
                            eDebug($e);
                        }
                    } 
                    //else {
                    //    $errorSet[$count]['message'] .= "<br/>Order " . $data[2] . " was captured successfully, however the email notification was not successful.";
                    //}
                }
            }
                       
            //eDebug($product);        
        }   
        
        assign_to_template(array('errorSet'=>$errorSet, 'successSet'=>$successSet));
    }
    
    function manage_sales_reps() {
        
    }
    
    function showHistory() {
        $h = new expHistory();
        echo "<xmp>";
        print_r($h);
        echo "</xmp>";
    }
    
    function import_external_addresses() {
        $sources = array('mc'=>'MilitaryClothing.com','nt'=>'NameTapes.com','am'=>'Amazon');
        assign_to_template(array('sources'=>$sources));
    }
    
    function process_external_addresses() {
        global $db;
         set_time_limit(0);
        //$file = new expFile($this->params['expFile']['batch_process_upload'][0]);
        eDebug($this->params);
//        eDebug($_FILES,true);
        if(!empty($_FILES['address_csv']['error']))
        {
            flash('error','There was an error uploading your file.  Please try again.');
            redirect_to(array('controller'=>'store','action'=>'import_external_addresses'));        
        }
        
        $file->path = $_FILES['address_csv']['tmp_name'];
        echo "Validating file...<br/>";
        
        //replace tabs with commas
        /*if($this->params['type_of_address'][0] == 'am')
        {
            $checkhandle = fopen($file->path, "w");
            $oldFile = file_get_contents($file->path);
            $newFile = str_ireplace(chr(9),',',$oldFile);
            fwrite($checkhandle,$newFile);
            fclose($checkhandle);
        }*/
        
        $checkhandle = fopen($file->path, "r");
        if($this->params['type_of_address'][0] == 'am')
        {
            $checkdata = fgetcsv($checkhandle, 10000, "\t");
            $fieldCount = count($checkdata);        
        }
        else
        {
            $checkdata = fgetcsv($checkhandle, 10000, ",");
            $fieldCount = count($checkdata);            
        }
        
        $count = 1;
         if($this->params['type_of_address'][0] == 'am')
         {
            while (($checkdata = fgetcsv($checkhandle, 10000, "\t")) !== FALSE) 
            {
                $count++;
                //eDebug($checkdata);
                if (count($checkdata) != $fieldCount) 
                {                   
                    echo "Line ". $count ." of your CSV import file does not contain the correct number of columns.<br/>";
                    echo "Found " . $fieldCount . " header fields, but only " . count($checkdata) ." field in row " . $count . " Please check your file and try again.";
                    exit();
                }
            }
         }
         else
         {
            while (($checkdata = fgetcsv($checkhandle, 10000, ",")) !== FALSE) 
            {
                $count++;
                if (count($checkdata) != $fieldCount) 
                {                   
                    echo "Line ". $count ." of your CSV import file does not contain the correct number of columns.<br/>";
                    echo "Found " . $fieldCount . " header fields, but only " . count($checkdata) ." field in row " . $count . " Please check your file and try again.";
                    exit();
                }
            }     
         }
                
        fclose($checkhandle);
        
        echo "<br/>CSV File passed validation...<br/><br/>Importing....<br/><br/>";
        //exit();
        $handle = fopen($file->path, "r");
        $data = fgetcsv($handle, 10000, ",");
        //eDebug($data);      
        $dataset = array();
        
        
        //mc=1, nt=2, amm=3
              
         if($this->params['type_of_address'][0] == 'mc')
         {
             //militaryclothing
             $db->delete('external_addresses','source=1');
                                 
         }
         else if($this->params['type_of_address'][0] == 'nt')
         {
             //nametapes
             $db->delete('external_addresses','source=2');
         }
         else if($this->params['type_of_address'][0] == 'am')
         {
             //amazon
             $db->delete('external_addresses','source=3');
         }
         
         if($this->params['type_of_address'][0] == 'am')
         {
            while (($data = fgetcsv($handle, 10000, "\t")) !== FALSE) 
             {
                //eDebug($data,true);
                $extAddy = new external_address();               
                            
                //eDebug($data);
                $extAddy->source = 3;
                $extAddy->user_id = 0;
                $name = explode(' ',$data[15]);
                $extAddy->firstname = $name[0];
                if(isset($name[3]))
                {
                    $extAddy->firstname .= ' ' . $name[1];
                    $extAddy->middlename = $name[2];   
                    $extAddy->lastname = $name[3];
                }
                else if(isset($name[2]))
                {
                    $extAddy->middlename = $name[1];
                    $extAddy->lastname = $name[2];
                }
                else
                {
                    $extAddy->lastname = $name[1];
                }                
                $extAddy->organization = $data[15];
                $extAddy->address1 = $data[16];
                $extAddy->address2 = $data[17];
                $extAddy->city = $data[19];
                $state = new geoRegion();
                $state = $state->findBy('code',trim($data[20]));
                if(empty($state->id)) {
                    $state = new geoRegion();
                    $state = $state->findBy('name',trim($data[20]));   
                }
                $extAddy->state = $state->id;
                $extAddy->zip = str_ireplace("'",'',$data[21]);
                $extAddy->phone = $data[6];
                $extAddy->email = $data[4];
                //eDebug($extAddy);
                $extAddy->save();
             }
         } 
         else
         {
             while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) 
             {
                eDebug($data);
                $extAddy = new external_address();
                if($this->params['type_of_address'][0] == 'mc')
                {             
                    $extAddy->source = 1;
                    $extAddy->user_id = 0;
                    $name = explode(' ',$data[3]);
                    $extAddy->firstname = $name[0];
                    if(isset($name[2]))
                    {
                        $extAddy->middlename = $name[1];
                        $extAddy->lastname = $name[2];
                    }
                    else
                    {
                        $extAddy->lastname = $name[1];
                    }
                    $extAddy->organization = $data[4];
                    $extAddy->address1 = $data[5];
                    $extAddy->address2 = $data[6];
                    $extAddy->city = $data[7];
                    $state = new geoRegion();
                    $state = $state->findBy('code',$data[8]);
                    $extAddy->state = $state->id;
                    $extAddy->zip = str_ireplace("'",'',$data[9]);
                    $extAddy->phone = $data[20];
                    $extAddy->email = $data[21];
                    //eDebug($extAddy);
                    $extAddy->save();
					
					//Check if the shipping add is same as the billing add
					if($data[5] != $data[14]) {
						$extAddy = new external_address();
						$extAddy->source = 1;
						$extAddy->user_id = 0;
						$name = explode(' ',$data[12]);
						$extAddy->firstname = $name[0];
						if(isset($name[2]))
						{
							$extAddy->middlename = $name[1];
							$extAddy->lastname = $name[2];
						}
						else
						{
							$extAddy->lastname = $name[1];
						}
						$extAddy->organization = $data[13];
						$extAddy->address1 = $data[14];
						$extAddy->address2 = $data[15];
						$extAddy->city = $data[16];
						$state = new geoRegion();
						$state = $state->findBy('code',$data[17]);
						$extAddy->state = $state->id;
						$extAddy->zip = str_ireplace("'",'',$data[18]);
						$extAddy->phone = $data[20];
						$extAddy->email = $data[21];
						// eDebug($extAddy, true);
						$extAddy->save();
					}
                }
                if($this->params['type_of_address'][0] == 'nt')
                {             
                    //eDebug($data,true);
                    $extAddy->source = 2;
                    $extAddy->user_id = 0;
                    $extAddy->firstname = $data[16];
                    $extAddy->lastname = $data[17];                
                    $extAddy->organization = $data[15];
                    $extAddy->address1 = $data[18];
                    $extAddy->address2 = $data[19];
                    $extAddy->city = $data[20];
                    $state = new geoRegion();
                    $state = $state->findBy('code',$data[21]);
                    $extAddy->state = $state->id;
                    $extAddy->zip = str_ireplace("'",'',$data[22]);
                    $extAddy->phone = $data[23];
                    $extAddy->email = $data[13];
                    //eDebug($extAddy);
                    $extAddy->save();
                }
             }
         }       
         echo "Done!";
    }
	
	function nonUnicodeProducts() {
		global $db, $user;
		
		$products = $db->selectObjectsIndexedArray('product');
		$affected_fields = array();
		$listings = array();
		$listedProducts = array();
		$count = 0;
		//Get all the columns of the product table
		$columns = $db->getTextColumns('product');
		foreach($products as $item) {
		
			foreach($columns as $column) {
				if($column != 'body' && $column != 'summary' && $column != 'featured_body') {
					if(!expString::validUTF($item->$column) || strrpos($item->$column, '?')) {
						$affected_fields[] = $column;
					}
				} else {
					if(!expString::validUTF($item->$column)) {
						$affected_fields[] = $column;
					}
				}
			}
			
			if(isset($affected_fields)) {
				if(count($affected_fields) > 0) {
					//Hard coded fields since this is only for displaying
					$listedProducts[$count]['id'] = $item->id;
					$listedProducts[$count]['title'] = $item->title;
					$listedProducts[$count]['model'] = $item->model;
					$listedProducts[$count]['sef_url'] = $item->sef_url;
					$listedProducts[$count]['nonunicode'] = implode(', ', $affected_fields);
					$count++;
				}
			}
			unset($affected_fields);
		}
		
		assign_to_template(array( 'products' => $listedProducts, 'count' => $count ));
	}
	
	function cleanNonUnicodeProducts() {
		global $db, $user;
		
		$products = $db->selectObjectsIndexedArray('product');
		//Get all the columns of the product table
		$columns = $db->getTextColumns('product');
		foreach($products as $item) {
			//Since body, summary, featured_body can have a ? intentionally such as a link with get parameter. 
			//TO Improved
			foreach($columns as $column) {
				if($column != 'body' && $column != 'summary' && $column != 'featured_body') {
					if(!expString::validUTF($item->$column) || strrpos($item->$column, '?')) {
						$item->$column = expString::convertUTF($item->$column); 
					}
				} else {
					if(!expString::validUTF($item->$column)) {
						$item->$column = expString::convertUTF($item->$column); 
					}
				}
			}
			
			$db->updateObject($item, 'product');
		}
		
		redirect_to(array('controller'=>'store', 'action'=>'nonUnicodeProducts'));
	}
	
	//This function is being used in the uploadModelaliases page for showing the form upload
	function uploadModelAliases() {
		 global $db;
         set_time_limit(0);
        
		if(isset($_FILES['modelaliases']['tmp_name'])) {
			if(!empty($_FILES['modelaliases']['error']))
			{
				flash('error','There was an error uploading your file.  Please try again.');
				redirect_to(array('controller'=>'store','action'=>'uploadModelAliases'));        
			}
			
			$file->path = $_FILES['modelaliases']['tmp_name'];
			echo "Validating file...<br/>";
			
			$checkhandle = fopen($file->path, "r");
			$checkdata = fgetcsv($checkhandle, 10000, ",");
			$fieldCount = count($checkdata);      
			$count = 1;
			
			while (($checkdata = fgetcsv($checkhandle, 10000, ",")) !== FALSE) 
			{
				$count++;
				if (count($checkdata) != $fieldCount) 
				{                   
					echo "Line ". $count ." of your CSV import file does not contain the correct number of columns.<br/>";
					echo "Found " . $fieldCount . " header fields, but only " . count($checkdata) ." field in row " . $count . " Please check your file and try again.";
					exit();
				}
			}     
					
			fclose($checkhandle);
			
			echo "<br/>CSV File passed validation...<br/><br/>Importing....<br/><br/>";
			$handle = fopen($file->path, "r");
			$data = fgetcsv($handle, 10000, ",");
			
			//clear the db
			$db->delete('model_aliases_tmp');
			while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
				
				$tmp->field1 = expString::onlyReadables($data[0]);
				$tmp->field2 = expString::onlyReadables($data[1]);
				$db->insertObject($tmp,'model_aliases_tmp');
			}
			redirect_to(array('controller'=>'store','action'=>'processModelAliases'));
			echo "Done!";
		}        
		
		//check if there are interrupted model alias in the db
		$res = $db->selectObjectsBySql("SELECT * FROM exponent_model_aliases_tmp WHERE is_processed = 0");
		if(!empty($res)) {
			assign_to_template(array('continue' => '1'));
		}
	}
	
	// This function process the uploading of the model aliases in the uploadModelAliases page
	function processModelAliases($index = 0, $error = '') {
		global $db;
		
		//Going next and delete the previous one
		if(isset($this->params['index'])) {
			$index = $this->params['index'];
			
			//if go to the next processs
			if(isset($this->params['next'])) {
				$res = $db->selectObjectBySql("SELECT * FROM exponent_model_aliases_tmp LIMIT " . ($index - 1) . ", 1");
				//Update the record in the tmp table to mark it as process
				$res->is_processed = 1;
				$db->updateObject($res, 'model_aliases_tmp');
			}
		}
		
		$product_id = '';
		$autocomplete = '';
		
		do {
			$count = $db->countObjects('model_aliases_tmp', 'is_processed=0'); 
			$res = $db->selectObjectBySql("SELECT * FROM exponent_model_aliases_tmp LIMIT {$index}, 1");
			//Validation
			//Check the field one
			if(!empty($res)) {
				$product_field1 = $db->selectObject("product", "model='{$res->field1}'");
				$product_field2 = $db->selectObject("product", "model='{$res->field2}'");
			}
			if(!empty($product_field1)) {
				$product_id = $product_field1->id;
				//check the other field if it also being used by another product
				if(!empty($product_field2) && $product_field1->id != $product_field2->id) {
					$error = "Both {$res->field1} and {$res->field2} are models of a product. <br />";
				} else {
					//Check the field2 if it is already in the model alias
					$model_alias = $db->selectObject("model_aliases", "model='{$res->field2}'");
					if(empty($model_alias) && @$model_alias->product_id != $product_field1->id) {
						//Add the first field
						$tmp->model = $res->field1;
						$tmp->product_id = $product_field1->id;
						$db->insertObject($tmp,'model_aliases');
						//Add the second field
						$tmp->model = $res->field2;
						$tmp->product_id = $product_field1->id;
						$db->insertObject($tmp,'model_aliases');
						//Update the record in the tmp table to mark it as process
						$res->is_processed = 1;
						$db->updateObject($res, 'model_aliases_tmp');
						
					} else {
						$error = "{$res->field2} has already a product alias. <br />";
					}
				}
			} elseif(!empty($product_field2)) {
				$product_id = $product_field2->id;
				$model_alias = $db->selectObject("model_aliases", "model='{$res->field1}'");
				if(empty($model_alias) && @$model_alias->product_id != $product_field2->id) {
					//Add the first field
					$tmp->model = $res->field1;
					$tmp->product_id = $product_field2->id;
					$db->insertObject($tmp,'model_aliases');
					//Add the second field
					$tmp->model = $res->field2;
					$tmp->product_id = $product_field2->id;
					$db->insertObject($tmp,'model_aliases');
					//Update the record in the tmp table to mark it as process
					$res->is_processed = 1;
					$db->updateObject($res, 'model_aliases_tmp');
				} else {
					$error = "{$res->field1} has already a product alias. <br />";
				}
			} else {
				$model_alias1 = $db->selectObject("model_aliases", "model='{$res->field1}'");
				$model_alias2 = $db->selectObject("model_aliases", "model='{$res->field2}'");
				
				if(!empty($model_alias1) || !empty($model_alias2)) {
					$error = "The {$res->field1} and {$res->field2} are already being used by another product.<br />";
				} else {
					$error = "No product match found, please choose a product to be alias in the following models below:<br />";
					$error .= $res->field1 . "<br />";
					$error .= $res->field2 . "<br />";
					$autocomplete = 1;
				}
			}
			$index++;
		} while(empty($error));
		assign_to_template(array('count' => $count, 'alias' => $res, 'index' => $index, 'product_id' => $product_id, 'autocomplete' => $autocomplete, 'error' => $error));
	}
	
	// This function save the uploaded processed model aliases in the uploadModelAliases page
	function saveModelAliases() {
		global $db;
		
		$index = $this->params['index'];
		$title = mysql_real_escape_string($this->params['product_title']);
		$product = $db->selectObject("product", "title='{$title}'");
		
		if(!empty($product->id)) {
			$res = $db->selectObjectBySql("SELECT * FROM exponent_model_aliases_tmp LIMIT "  . ($index - 1)  . ", 1");
			//Add the first field
			$tmp->model = $res->field1;
			$tmp->product_id = $product->id;
			$db->insertObject($tmp,'model_aliases');
			//Add the second field
			$tmp->model = $res->field2;
			$tmp->product_id = $product->id;
			$db->insertObject($tmp,'model_aliases');
			
			//if the model is empty, update the product table so that it will used the field 1 as its primary model
			if(empty($product->model)) {
				$product->model = $res->field1;
				$db->updateObject($product, 'product');
			}
			
			//Update the record in the tmp table to mark it as process
			$res->is_processed = 1;
			$db->updateObject($res, 'model_aliases_tmp');
			flash("message", "Product succesfully Saved.");
			redirect_to(array('controller'=>'store','action'=>'processModelAliases', 'index' => $index));
		} else {
			flash("error", "Product title is invalid.");
			redirect_to(array('controller'=>'store','action'=>'processModelAliases', 'index' => $index - 1, 'error' => 'Product title is invalid.'));
		}
	}	
	
	// This function delete all the already processed model aliases in the uploadModelAliases page
	function deleteProcessedModelAliases() {
		global $db;
		
		$db->delete('model_aliases_tmp','is_processed=1');
		redirect_to(array('controller' => 'store','action' => 'processModelAliases'));
	}
	
	// This function show the form of model alias to be edit or add in the product edit page
	function edit_model_alias() {
        global $db;
		
		if(isset($this->params['id'])) {
			$model_alias = $db->selectObject('model_aliases', 'id =' .$this->params['id']);
			assign_to_template(array('model_alias'=>$model_alias));
		} else {
			assign_to_template(array('product_id'=>$this->params['product_id']));
		}
    }

	// This function update or add the model alias in the product edit page
    function update_model_alias() {
        global $db;
			
		if(empty($this->params['id'])) {
			$obj->model = $this->params['model'];
			$obj->product_id = $this->params['product_id'];
			$db->insertObject($obj,'model_aliases');
			
		} else {
			$model_alias        = $db->selectObject('model_aliases', 'id =' .$this->params['id']);
			$model_alias->model  = $this->params['model'];
			$db->updateObject($model_alias, 'model_aliases');
		}
		
        expHistory::back();
    }
	
	// This function delete the model alias in the product edit page
	function delete_model_alias() {
		global $db;
		
        if (empty($this->params['id'])) return false;
        $db->delete('model_aliases', 'id =' .$this->params['id']);
		
        expHistory::back();
    }
}

?>
