<?php
##################################################
#
# Copyright (c) 2004-2012 OIC Group, Inc.
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

/**
 * This is the class expController
 *
 * @package Core
 * @subpackage Controllers
 */

abstract class expController {
    protected $classname = '';      // full controller name w/ 'Controller' suffix
    public $baseclassname = '';     // root controller name w/o 'Controller' suffix
    public $classinfo = null;       // holds reflection class of class
//    public $module_name = '';       //FIXME not used and not actually set right index needed of -3 instead of -2 below
//    protected $basemodel = null;    //FIXME never used, $basemodel_name replaced?
    public $basemodel_name = '';    // holds classname of base module associated w/ this controller
    public $model_table = '';       // holds table name for base model

    public $useractions = array();  // available user actions (methods) for this controller
    public $remove_configs = array(); // all options: ('aggregation','categories','comments','ealerts','files','module_title','pagination','rss','tags')
    protected $permissions = array(
        'manage'=>'Manage',
        'configure'=>'Configure',
        'create'=>'Create',
        'edit'=>'Edit', 
        'delete'=>'Delete', 
        //'perms'=>'Manage Permissions',
        );
    protected $remove_permissions = array();    
    protected $add_permissions = array();

    public $filepath = '';          // location of this controller's files
    public $viewpath = '';          // location of this controllers views
    public $relative_viewpath = ''; // relative location of controller's views
    public $asset_path = '';        // location of this controller's assets

    public $requires_login = array();   // actions (methods) which require user be logged in to access
    public $config = array();       // holds module configuration settings
    public $params = array();       // holds parameters passed to module
    public $loc = null;             // module location object

	public $codequality = 'stable'; // code's level of stability

	/**
	 * @param null $src
	 * @param array $params
	 * @return expController
	 *
	 */
	function __construct($src=null, $params=array()) {
        // setup some basic information about this class
        $this->classinfo = new ReflectionClass($this);
        $this->classname = $this->classinfo->getName();
        $this->baseclassname = substr($this->classinfo->getName(), 0, -10);
        $this->filepath = __realpath($this->classinfo->getFileName());

        // figure out which "module" we belong to and setup view path information
        $controllerpath = explode('/', $this->filepath);
//        $this->module_name = $controllerpath[(count($controllerpath)-3)];
       
        // set up the path to this module view files
        array_pop($controllerpath);  // remove 'controllers' from array
        $controllerpath[count($controllerpath)-1] = 'views';
        array_push($controllerpath, $this->baseclassname);
        $this->relative_viewpath = implode('/', array_slice($controllerpath, -3, 3));
//        $this->viewpath = BASE.'framework/modules/'.$this->relative_viewpath;
        //FIXME this requires we move the 'core' controllers into the modules folder or use this hack
        $depth = array_search('core',$controllerpath);
        if ($depth) {
            $this->viewpath = BASE.'framework/modules/'.$this->relative_viewpath;
        } else {
            $this->viewpath = implode('/',$controllerpath);
        }

        //grab the path to the module's assets
        array_pop($controllerpath);
        $controllerpath[count($controllerpath)-1] = 'assets';
//        $this->asset_path = PATH_RELATIVE.'framework/'.implode('/', array_slice($controllerpath, -3, 3))."/";
        $depth = array_search('framework',$controllerpath);
        if (!$depth) $depth = array_search('themes',$controllerpath);
        $this->asset_path = PATH_RELATIVE.implode('/', array_slice($controllerpath, $depth))."/";

        // figure out which model we're using and setup some info about it
        if (empty($this->basemodel_name)) $this->basemodel_name = get_model_for_controller($this->classname);
        $modelname = $this->basemodel_name;
        if (class_exists($modelname)) {
            $this->$modelname = new $modelname(null, false, false);
            $this->model_table = $this->$modelname->tablename;
        } else {
            $this->basemodel_name = 'expRecord';
            $this->$modelname = new expRecord(null, false, false);
            $this->model_table = null;
        }
        
        // set the location data
        $this->loc = expCore::makeLocation($this->baseclassname, $src, null);

        // get this controllers config data if there is any
        $config = new expConfig($this->loc);
        $this->config = $config->config;
        
        $this->params = $params;

    }

	/**
	 * name of module for backwards compat with old modules
	 * @return string
	 */
    function name() { return $this->displayname(); }
	
	/**
	 * name of module
	 * @return string
	 */
    static function displayname() { return gt("Exponent Base Controller"); }

    /**
   	 * description of module
   	 * @return string
   	 */
    static function description() { return gt("This is the base controller that most Exponent modules will inherit from."); }

	/**
	 * author of module
	 * @return string
	 */
    static function author() { return "OIC Group, Inc"; }

	/**
	 * does module have sources available?
	 * @return bool
	 */
	static function hasSources() { return true; }

	/**
	 * does module have views available?
	 * @return bool
	 */
    static function hasViews() { return true; }

	/**
	 * does module have content available?
	 * @return bool
	 */
    static function hasContent() { return true; }

	/**
	 * does module support workflow?
	 * @return bool
	 */
    static function supportsWorkflow() { return false; }

	/**
	 * is module content searchable?
	 * @return bool
	 */
    static function isSearchable() { return false; }

    /**
   	 * can this module import data?
   	 * @return bool
   	 */
    static function canImportData() { return false; }

   	/**
   	 * can this module export data?
   	 * @return bool
   	 */
    static function canExportData() { return false; }

   	/**
   	 * does this module require configuration?
   	 * @return bool
   	 */
    static function requiresConfiguration() { return false; }

	/**
	 * glue to make module aware of itself
	 */
	function moduleSelfAwareness() {
        assign_to_template(array(
            'asset_path'=>$this->asset_path,
            'model_name'=>$this->basemodel_name,
            'table'=>$this->model_table,
            'controller'=>$this->baseclassname
        ));
    }

	/**
	 * default module view method for all items
	 */
	function showall() {
        expHistory::set('viewable', $this->params);

        $page = new expPaginator(array(
            'model'=>$this->basemodel_name,
            'where'=>$this->hasSources() ? $this->aggregateWhereClause() : null,
            'limit'=>(isset($this->params['limit']) && $this->params['limit'] != '') ? $this->params['limit'] : 10,
            'order'=>isset($this->params['order']) ? $this->params['order'] : null,
            'page'=>(isset($this->params['page']) ? $this->params['page'] : 1),
            'controller'=>$this->baseclassname,
            'action'=>$this->params['action'],
            'src'=>$this->hasSources() == true ? $this->loc->src : null,
            'columns'=>array(
                gt('ID#')=>'id',
                gt('Title')=>'title',
                gt('Body')=>'body'
            ),
        ));
        
        assign_to_template(array(
            'page'=>$page,
            'items'=>$page->records
        ));
    }

    /**
   	 * default module view method for all items with a specific tag
   	 */
    public function showall_by_tags() {
        global $db;

        $modelname = $this->basemodel_name;
        // set history
        expHistory::set('viewable', $this->params);

        // get the tag being passed
        $tag = new expTag($this->params['tag']);

        // find all the id's of the portfolios for this module
        $item_ids = $db->selectColumn($modelname, 'id', $this->aggregateWhereClause());

        // find all the items that this tag is attached to
        $items = $tag->findWhereAttachedTo($modelname);

        // loop the items for this tag and find out which ones belong to this module
        $items_by_tags = array();
        foreach($items as $item) {
            if (in_array($item->id, $item_ids)) $items_by_tags[] = $item;
        }

        // create a pagination object for the model and render the action
        $order = 'created_at DESC';
        $page = new expPaginator(array(
            'records'=>$items_by_tags,
            'limit'=>(isset($this->config['limit']) && $this->config['limit'] != '') ? $this->config['limit'] : 10,
            'order'=>$order,
            'page'=>(isset($this->params['page']) ? $this->params['page'] : 1),
            'controller'=>$this->baseclassname,
            'action'=>$this->params['action'],
            'columns'=>array(
                gt('Title')=>'title'
            ),
        ));
//        $page->records = expSorter::sort(array('array'=>$page->records, 'sortby'=>'rank', 'order'=>'ASC', 'ignore_case'=>true));
        $page->records = expSorter::sort(array('array'=>$page->records, 'sortby'=>'created_at', 'order'=>'DESC', 'ignore_case'=>true));

        assign_to_template(array(
            'page'=>$page,
            'items'=>$page->records,
            'moduletitle'=>ucfirst($modelname).' '.gt('items tagged with').' "'.expString::sanitize($this->params['tag']).'"',
            'rank'=>($order==='rank')?1:0
        ));
    }

    public function tags() {

        $modelname = $this->basemodel_name;

        $items = $this->$modelname->find('all', $this->aggregateWhereClause());
        $used_tags = array();
        foreach ($items as $item) {
            foreach($item->expTag as $tag) {
                if (isset($used_tags[$tag->id])) {
                    $used_tags[$tag->id]->count += 1;
                } else {
                    $exptag = new expTag($tag->id);
                    $used_tags[$tag->id] = $exptag;
                    $used_tags[$tag->id]->count = 1;
                }
            }
        }

//        $order = isset($this->config['order']) ? $this->config['order'] : 'rank';
//        $used_tags = expSorter::sort(array('array'=>$used_tags,'sortby'=>'title', 'order'=>'ASC', 'ignore_case'=>true, 'rank'=>($order==='rank')?1:0));
//        $order = isset($this->config['order']) ? $this->config['order'] : 'title ASC';
//        $used_tags = expSorter::sort(array('array'=>$used_tags, 'order'=>$order, 'ignore_case'=>true, 'rank'=>($order==='rank')?1:0));
        $used_tags = expSorter::sort(array('array'=>$used_tags, 'order'=>'count DESC', 'type'=>'a'));
        if (!empty($this->config['limit'])) $used_tags = array_slice($used_tags,0,$this->config['limit']);
        $order = isset($this->config['order']) ? $this->config['order'] : 'title ASC';
        if ($order != 'hits') {
            $used_tags = expSorter::sort(array('array'=>$used_tags, 'order'=>$order, 'ignore_case'=>true, 'rank'=>($order==='rank')?1:0));
        }

        assign_to_template(array(
            'tags'=>$used_tags
        ));
    }

	/**
	 * default view for individual item
	 */
	function show() {
        global $db;

        expHistory::set('viewable', $this->params);
        $modelname = $this->basemodel_name;
        
        // figure out if we're looking this up by id or title
        $id = null;
        if (isset($this->params['id'])) {
            $id = $this->params['id'];
        } elseif (isset($this->params['title'])) {
            $id = $this->params['title'];
        }
        
        $record = new $modelname($id);
        $config = expUnserialize($db->selectValue('expConfigs','config',"location_data='".$record->location_data."'"));

        assign_to_template(array(
            'record'=>$record,
            'config'=>$config
        ));
    }

	/**
	 * view the item by referring to its title
	 */
	function showByTitle() {
        expHistory::set('viewable', $this->params);
        $modelname = $this->basemodel_name;
        // first we'll check to see if this matches the sef_url field...if not then we'll look for the 
        // title field
        $record = $this->$modelname->find('first', "sef_url='".$this->params['title']."'");
        if (!is_object($record)) {
            $record = $this->$modelname->find('first', "title='".$this->params['title']."'");
        }
        $this->loc = unserialize($record->location_data);
        
        // adding src to template's __loc var so that our links get build correct when linking to controller actions.
//        global $template;
        assign_to_template(array(
            'record'=>$record,
//            "__loc"=>$this->loc
        ));
    }

	/**
	 * view a random item
	 */
	public function showRandom() {
		$where = $this->hasSources() ? $this->aggregateWhereClause() : null;
		$limit = isset($this->params['limit']) ? $this->params['limit'] : 1;
		$order = 'RAND()';
		assign_to_template(array(
            'items'=>$this->text->find('all', $where, $order, $limit)
        ));
	}

	/**
	 * view items referenced by tags
	 */
	function showByTags() {
        global $db;

        // set the history point for this action
        expHistory::set('viewable', $this->params);
        
        // setup some objects
        $tagobj = new expTag();
        $modelname = empty($this->params['model']) ? $this->basemodel_name : $this->params['model'];
        $model = new $modelname();

        // start building the sql query
        $sql  = 'SELECT DISTINCT m.id FROM '.DB_TABLE_PREFIX.'_'.$model->tablename.' m ';
        $sql .= 'JOIN '.DB_TABLE_PREFIX.'_'.$tagobj->attachable_table.' ct '; 
        $sql .= 'ON m.id = ct.content_id WHERE (';
        $first = true;

        if (isset($this->params['tags'])) {
            $tags = is_array($this->params['tags']) ? $this->params['tags'] : array($this->params['tags']); 
        } elseif (isset($this->config['expTags'])) {
            $tags = $this->config['expTags'];
        } else {
            $tags = array();
        }

        foreach ($tags as $tagid) {
            $sql .= ($first) ? 'exptags_id='.intval($tagid) : ' OR exptags_id='.intval($tagid);
            $first = false;
        }
        $sql .= ") AND content_type='".$model->classname."'";

        // get the objects and render the template
        $tag_assocs = $db->selectObjectsBySql($sql);
        $records = array();
        foreach ($tag_assocs as $assoc) {
            $records[] = new $modelname($assoc->id);
        }

        assign_to_template(array(
            'items'=>$records
        ));
    }

	/**
	 * create an item in this module
	 */
	function create() {
        $args = array('controller'=>$this->params['controller'], 'action'=>'edit');
        //if (!empty($this->params['instance'])) $args['instance'] = $this->params['instance'];
        if (!empty($this->params['src'])) $args['src'] = $this->params['src'];
        redirect_to($args);
    }

	/**
	 * edit item in module
	 */
	function edit() {
        global $db;

        expHistory::set('editable', $this->params);
        $tags = $db->selectObjects('expTags','1','title ASC');
   		$taglist = '';
        foreach ($tags as $tag) {
            $taglist .= "'".$tag->title."',";
        }
        $modelname = $this->basemodel_name;
        $record = isset($this->params['id']) ? $this->$modelname->find($this->params['id']) : new $modelname($this->params);
        assign_to_template(array(
            'record'=>$record,
            'table'=>$this->$modelname->tablename,
            'controller'=>$this->params['controller'],
            'taglist'=>$taglist
        ));
    }

    /**
     * merge/move aggregated item into this module
     */
    function merge() {
        global $db;

        expHistory::set('editable', $this->params);
        $modelname = $this->basemodel_name;
        $record = $this->$modelname->find($this->params['id']);

        $loc = expUnserialize($record->location_data);
        $loc->src = $this->loc->src;
        $record->location_data = serialize($loc);
        $this->$modelname->update($record);

        expHistory::back();
    }

	/**
	 * update item in module
	 */
	function update() {
        global $db;
                
        //check for and handle tags
        if (array_key_exists('expTag',$this->params)) {
	        if (isset($this->params['id'])) {
    	        $db->delete('content_expTags', 'content_type="'.(!empty($this->params['content_type'])?$this->params['content_type']:$this->basemodel_name).'" AND content_id='.$this->params['id']);
    	    }
	        $tags = explode(",", trim($this->params['expTag']));
	        unset($this->params['expTag']);
	        
	        foreach($tags as $tag) {
                if (!empty($tag)) {
                    $tag = strtolower(trim($tag));
                    $tag = str_replace('"', "", $tag); // strip double quotes
                    $tag = str_replace("'", "", $tag); // strip single quotes
                    $expTag = new expTag($tag);
                    if (empty($expTag->id)) $expTag->update(array('title'=>$tag));
                    $this->params['expTag'][] = $expTag->id;
                }
	        }
        }

        //check for and handle cats
        if (array_key_exists('expCat',$this->params)&&!empty($this->params['expCat'])) {
            $catid = $this->params['expCat'];
            unset($this->params['expCat']);
            $this->params['expCat'][] = $catid;
        }

        $modelname = $this->basemodel_name;
        $this->$modelname->update($this->params);
        
        if ($this->isSearchable()) {
            $this->addContentToSearch($this->params);
        }

	    // check for eAlerts
	    if (!empty($this->params['send_ealerts'])) {
            redirect_to(array('controller'=>'ealert','action'=>'send_confirm','model'=>$modelname,'id'=>$this->$modelname->id, 'src'=>$this->loc->src,'orig_controller'=>expModules::getControllerName($this->classname)));
        } else {
            expHistory::back();
        }
    }

	/**
	 * delete item in module
	 */
	function delete() {
        $modelname = $this->basemodel_name;
        if (empty($this->params['id'])) {
	        flash('error', gt('Missing id for the').' '.$modelname.' '.gt('you would like to delete'));
	        expHistory::back();
	    }
        
        $obj = new $modelname($this->params['id']);
        $rows = $obj->delete();
        
        // if this module is searchable lets delete spidered content
        if ($this->isSearchable()) {
            $search = new search();
            $content = $search->find('first', 'original_id='.$this->params['id']." AND ref_module='".$this->classname."'");
            if (!empty($content->id)) $content->delete();
        }
        
        expHistory::back();
    }

	/**
	 * rerank items in module
	 */
	function rerank() {
        $modelname = $this->basemodel_name;
        $obj = new $modelname($this->params['id']);
        $obj->rerank($this->params['push']);
        expHistory::back();
    }

	/**
	 * display module management view
	 */
	function manage() {
        expHistory::set('manageable', $this->params);

        $page = new expPaginator(array(
            'model'=>$this->basemodel_name,
            'where'=>$this->hasSources() ? $this->aggregateWhereClause() : null,
            'limit'=>isset($this->params['limit']) ? $this->params['limit'] : 10,
            'order'=>isset($this->params['order']) ? $this->params['order'] : null,
            'page'=>(isset($this->params['page']) ? $this->params['page'] : 1),
            'controller'=>$this->baseclassname,
            'action'=>$this->params['action'],
            'src'=>$this->hasSources() == true ? $this->loc->src : null,
            'columns'=>array(
                gt('ID#')=>'id',
                gt('Title')=>'title',
                gt('Body')=>'body'
            ),
        ));
        
        assign_to_template(array(
            'page'=>$page,
            'items'=>$page->records
        ));
    }

	/**
	 * display view to rerank items
	 */
	function manage_ranks() {
        $rank = 1;
        foreach($this->params['rerank'] as $key=>$id) {
            $modelname = $this->params['model'];
            $obj = new $modelname($id);
            $obj->rank = $rank;
            $obj->save();
            $rank += 1;
        }
        
        redirect_to($this->params['lastpage']);
        
    }
    
	/**
	 * generic config action
	 */
	function configure() {
        expHistory::set('editable', $this->params);
        $pullable_modules = expModules::listInstalledControllers($this->classname, $this->loc);
        $views = get_config_templates($this, $this->loc);
        $page = new expPaginator(array(
            'records'=>$pullable_modules,
//                    'limit'=>count($pullable_modules),
            'order'=>isset($this->params['order']) ? $this->params['order'] : 'section',
            'dir'=>isset($this->params['dir']) ? $this->params['dir'] : '',
            'page'=>(isset($this->params['page']) ? $this->params['page'] : 1),
            'columns'=>array(
                gt('Title')=>'title',
                gt('Page')=>'section'
            ),
        ));
        assign_to_template(array(
            'config'=>$this->config,
            'pullable_modules'=>$pullable_modules,
            'page'=>$page,
            'views'=>$views,
            'title'=>$this->displayname()
        ));
    }

	/**
	 * get the items in an rss feed format
     *
     * this function is very general and will most of the time need to be overwritten and customized
     *
	 * @return array
	 */
	function getRSSContent() {
        global $db;
    
        // setup the where clause for looking up records.
        $where = $this->aggregateWhereClause();
//        $where = empty($where) ? '1' : $where;
		
        $order = isset($this->config['order']) ? $this->config['order'] : 'created_at DESC';

        $class = new $this->basemodel_name;
        $items = $class->find('all', $where, $order);

        //Convert the items to rss items
        $rssitems = array();
        foreach ($items as $key => $item) { 
            $rss_item = new FeedItem();
            $rss_item->title = expString::convertSmartQuotes($item->title);
            $rss_item->link = makeLink(array('controller'=>$this->baseclassname, 'action'=>'show', 'title'=>$item->sef_url));
            $rss_item->description = expString::convertSmartQuotes($item->body);
            $rss_item->author = user::getUserById($item->poster)->firstname.' '.user::getUserById($item->poster)->lastname;
            $rss_item->date = isset($item->publish_date) ? date('r',$item->publish_date) : date('r', $item->created_at);
            if (!empty($item->expCat[0]->title)) $rss_item->category = array($item->expCat[0]->title);
            $comment_count = expCommentController::findComments(array('content_id'=>$item->id,'content_type'=>$this->basemodel_name));
            if ($comment_count) {
                $rss_item->comments = makeLink(array('controller'=>$this->baseclassname, 'action'=>'show', 'title'=>$item->sef_url)).'#exp-comments';
//                $rss_item->commentsRSS = makeLink(array('controller'=>$this->baseclassname, 'action'=>'show', 'title'=>$item->sef_url)).'#exp-comments';
                $rss_item->commentsCount = $comment_count;
            }
            $rssitems[$key] = $rss_item;
        }
        return $rssitems;
    }

    /**
     *
     */
    function rss() {
        require_once(BASE.'external/feedcreator.class.php');

        $id = isset($this->params['title']) ? $this->params['title'] : (isset($this->params['id']) ? $this->params['id'] : null);
        if (empty($id)) {
            $module = !empty($this->params['module']) ? $this->params['module'] : $this->params['controller'];
            $id = array('module'=>$module,'src'=>$this->params['src']);
        }
        $site_rss = new expRss($id);
        if (!empty($site_rss->id) && $site_rss->enable_rss == true) {
            $site_rss->title = empty($site_rss->title) ? gt('RSS for').' '.URL_FULL : $site_rss->title;
            $site_rss->feed_desc = empty($site_rss->feed_desc) ? gt('This is an RSS syndication from').' '.HOSTNAME : $site_rss->feed_desc;
            if (isset($site_rss->rss_cachetime)) { $ttl = $site_rss->rss_cachetime; }
            if ($site_rss->rss_cachetime == 0) { $site_rss->rss_cachetime = 1440; }

            if (!empty($site_rss->itunes_cats)) {
                $ic = explode(";", $site_rss->itunes_cats);
                $x = 0;
                $itunes_cats = array();
                foreach($ic as $cat){
                    $cat_sub = explode(":", $cat);
                    $itunes_cats[$x]->category = $cat_sub[0];
                    if(isset($cat_sub[1])) {
                        $itunes_cats[$x]->subcategory = $cat_sub[1];
                    }
                    $x++;
                }
            }

            // NO buffering from here on out or things break unexpectedly. - RAM
            ob_end_clean();

            header('Content-Type: ' . 'application/rss+xml');
//            header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
//            header('Content-Transfer-Encoding: binary');
            header('Content-Encoding:');
            // IE need specific headers
            if (EXPONENT_USER_BROWSER == 'IE') {
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Vary: User-Agent');
            } else {
                header('Pragma: no-cache');
            }

        	$rss = new UniversalFeedCreator();
        	$rss->cssStyleSheet = "";
        //	$rss->useCached("PODCAST");
        	$rss->useCached();
            $rss->title = $site_rss->title;
        	$rss->description = $site_rss->feed_desc;
            $rss->image = new FeedImage();
            $rss->image->url = URL_FULL.'themes/'.DISPLAY_THEME.'/images/logo.png';
            $rss->image->title = $site_rss->title;
            $rss->image->link = URL_FULL;
        //    $rss->image->width = 64;
        //    $rss->image->height = 64;
        	$rss->ttl = $site_rss->rss_cachetime;
        	$rss->link = "http://".HOSTNAME.PATH_RELATIVE;
        	$rss->syndicationURL = "http://".HOSTNAME.$_SERVER['PHP_SELF'].'?module='.$site_rss->module.'&src='.$site_rss->src;
        	if ($site_rss->module == "filedownload") {
                $rss->itunes = new iTunes();
        //		$rss->itunes->summary = $site_rss->feed_desc;
        		$rss->itunes->author = ORGANIZATION_NAME;
                if (!empty($itunes_cats)) {
                    $rss->itunes->category = $itunes_cats[0]->category;
                    $rss->itunes->subcategory = $itunes_cats[0]->subcategory;
                }
        		$rss->itunes->image = URL_FULL.'themes/'.DISPLAY_THEME.'/images/logo.png';
        //		$rss->itunes->explicit = 0;
                $rss->itunes->subtitle = $site_rss->title;
        //		$rss->itunes->keywords = 0;
        		$rss->itunes->owner_email = SMTP_FROMADDRESS;
                $rss->itunes->owner_name = ORGANIZATION_NAME;
        	}

        	$pubDate = '';
        	foreach ($site_rss->getFeedItems() as $item) {
        		if ($item->date > $pubDate) { $pubDate = $item->date; }
        		$rss->addItem($item);
        	}
        	if (!empty($site_rss->rss_limit)) {
        		$rss->items = array_slice($rss->items, 0, $site_rss->rss_limit);
        	}
        	$rss->pubDate = $pubDate;

//        	header("Content-type: text/xml");
        	if ($site_rss->module == "filedownload") {
        		echo $rss->createFeed("PODCAST");
        	} else {
        		echo $rss->createFeed("RSS2.0");
        	}
        } else {
        	echo gt("This RSS feed is not available.");
        }

		//Read the file out directly
		exit();
    }

    /**
	 * save module configuration
	 */
	function saveconfig() {
        
        // create a new RSS object if enable is checked.
        if (!empty($this->params['enable_rss'])) {
            $params = $this->params;
            $params['title'] = $params['feed_title'];
            unset($params['feed_title']);
            $params['sef_url'] = $params['feed_sef_url'];
            unset($params['feed_sef_url']);
            $rssfeed = new expRss($params);
            $rssfeed->update($params);
            $this->params['feed_sef_url'] = $rssfeed->sef_url;
        } else {
            $rssfeed = new expRss($this->params);
            $params = $this->params;
            $params['enable_rss'] = false;
            if (empty($params['advertise'])) $params['advertise'] = false;
            $params['title'] = $params['feed_title'];
            unset($params['feed_title']);
            $params['sef_url'] = $params['feed_sef_url'];
            unset($params['feed_sef_url']);
            if (!empty($rssfeed->id)) {  // do NOT create a new record, only update existing ones
                $rssfeed->update($params);
                $this->params['feed_sef_url'] = $rssfeed->sef_url;
            }
        }
        
        // create a new eAlerts object if enable is checked.
        if (!empty($this->params['enable_ealerts'])) {
            $ealert = new expeAlerts($this->params);
            $ealert->update($this->params);
        }
                           
        // unset some unneeded params
        unset($this->params['module']);
        unset($this->params['controller']);
        unset($this->params['src']);
        unset($this->params['int']);
        unset($this->params['id']);
        unset($this->params['cid']);
        unset($this->params['action']);
        unset($this->params['PHPSESSID']);
        
        // setup and save the config
        $config = new expConfig($this->loc);
        $config->update(array('config'=>$this->params));

        flash('message', gt('Configuration updated'));
        expHistory::back();
    }

	/**
	 * download a file attached to item
	 */
	function downloadfile() {
        global $db;    
        
        if (!isset($this->config['allowdownloads']) || $this->config['allowdownloads'] == true) { 
            //if ($db->selectObject('content_expFiles', 'content_type="'.$this->baseclassname.'" AND expfiles_id='.$this->params['id']) != null) {
                expFile::download($this->params['id']);
            //}
        } else {
            flash('error', gt('Downloads have not been enabled for this file'));
            expHistory::back();         
        }
        
    }

	/**
	 * permission functions
	 * @return array
	 */
	function permissions() {
        //set the permissions array
        $perms = array();
        foreach($this->permissions as $perm=>$name) {
            if (!in_array($perm, $this->remove_permissions)) $perms[$perm] = $name;
        }
        $perms = array_merge($perms, $this->add_permissions);
        return $perms;
    }

	/**
	 * get the models associated with this module
	 * @return array
	 */
	function getModels() {
        return isset($this->models) ? $this->models : array($this->basemodel_name);
    }

	/**
	 * type of items searched in the module
	 * @return string
	 */
	function searchName() {
        return $this->displayname();
    }

	/**
	 * category of items searched in the module
	 * @return string
	 */
	function searchCategory() {
        return $this->basemodel_name;
    }

	/**
	 * add all module items to search index
	 * @return int
	 */
	function addContentToSearch() {
        global $db, $router;

        $count = 0;
        $model = new $this->basemodel_name(null, false, false);
        $where = (!empty($this->params['id'])) ? 'id='.$this->params['id'] : null;
        $content = $db->selectArrays($model->tablename,$where);
        foreach ($content as $cnt) {
            $origid = $cnt['id'];
            unset($cnt['id']);
            $sql = "original_id=".$origid." AND ref_module='".$this->classname."'";
            $oldindex = $db->selectObject('search',$sql);
            if (!empty($oldindex)) {
                $search_record = new search($oldindex->id, false, false);
                $search_record->update($cnt);
            } else {
                $search_record = new search($cnt, false, false);
            }

            //build the search record and save it.
            $search_record->original_id = $origid;
            $search_record->posted = empty($cnt['created_at']) ? null : $cnt['created_at'];
            // get the location data for this content
            if (isset($cnt['location_data'])) $loc = expUnserialize($cnt['location_data']);
            $src = isset($loc->src) ? $loc->src : null;
            if (!empty($cnt['sef_url'])) {
                $link = str_replace(URL_FULL,'', makeLink(array('controller'=>$this->baseclassname, 'action'=>'show', 'title'=>$cnt['sef_url'])));
            } else {
                $link = str_replace(URL_FULL,'', makeLink(array('controller'=>$this->baseclassname, 'action'=>'show', 'id'=>$origid, 'src'=>$src)));
            }
//	        if (empty($search_record->title)) $search_record->title = 'Untitled';
            $search_record->view_link = $link;
            $search_record->ref_module = $this->classname;
            $search_record->category = $this->searchName();
            $search_record->ref_type = $this->searchCategory();
            $search_record->save();
            $count += 1;
         }
         
         return $count;
    }

	/**
	 * remove module items from search index
	 */
	function delete_search() {
        global $db;        
        // remove this modules entries from the search table.
        if ($this->isSearchable()) {
            $where = "ref_module='".$this->classname."' AND location_data='".serialize($this->loc)."'";            
//            $test = $db->selectObjects('search', $where);
            $db->delete('search', $where);
        }
    }

	/**
	 * delete an item by instance for backwards compat with old modules
	 * @param $loc
	 */
	function delete_In($loc) { $this->delete_instance(); }

	/**
	 * delete an item by instance
	 */
	function delete_instance() {
        global $db;
        $model = new $this->basemodel_name();
        $where = null;
        if ($this->hasSources()) $where = "location_data='".serialize($this->loc)."'";
        $db->delete($model->tablename, $where);
    }

	/**
	 * get the metainfo for this module
	 * @return array
	 */
	function metainfo() {
        global $router;
        if (empty($router->params['action'])) return false;
        
        // figure out what metadata to pass back based on the action we are in.
        $action = $_REQUEST['action'];
        $metainfo = array('title'=>'', 'keywords'=>'', 'description'=>'');
        $modelname = $this->basemodel_name;
        switch($action) {
            case 'showall':
                $metainfo = array('title'=>"Showing all - ".$this->displayname(), 'keywords'=>SITE_KEYWORDS, 'description'=>SITE_DESCRIPTION);
            break;
            case 'show':
            case 'showByTitle':
                // look up the record.
                if (isset($_REQUEST['id']) || isset($_REQUEST['title'])) {
                    $lookup = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : expString::sanitize($_REQUEST['title']);
                    $object = new $modelname($lookup);
                    // set the meta info
                    if (!empty($object)) {
                        $metainfo['title'] = empty($object->meta_title) ? $object->title : $object->meta_title;
                        $metainfo['keywords'] = empty($object->meta_keywords) ? SITE_KEYWORDS : $object->meta_keywords;
                        $metainfo['description'] = empty($object->meta_description) ? SITE_DESCRIPTION : $object->meta_description;
                    }              
                }
            break;
            default:
                //check for a function in the controller called 'action'_meta and use it if so
                $functionName = $action."_meta";
                $mod = new $this->classname;                
                if (method_exists($mod,$functionName)) {
                    $metainfo = $mod->$functionName($_REQUEST);
                } else {
                    $metainfo = array('title'=>$this->displayname()." - ".SITE_TITLE, 'keywords'=>SITE_KEYWORDS, 'description'=>SITE_DESCRIPTION);
                }
        }
        
        return $metainfo;
    }

    function showall_by_tags_meta($request) {
        // look up the record.
        if (isset($request['tag'])) {
            $object = new expTag(expString::sanitize($request['tag']));
            // set the meta info
            if (!empty($object)) {
                $metainfo['title'] = gt('Showing all Items tagged with') ." \"" . $object->title . "\"";
                $metainfo['keywords'] = empty($object->meta_keywords) ? SITE_KEYWORDS : $object->meta_keywords;
                $metainfo['description'] = empty($object->meta_description) ? SITE_DESCRIPTION : $object->meta_description;
            }
        }
    }

	/**
	 * The aggregateWhereClause function creates a sql where clause which also includes aggregated module content
	 *
	 * @return string
	 */
	function aggregateWhereClause() {
        $sql = '';

        if (!$this->hasSources() && empty($this->config['add_source'])) { return $sql; }

        if (!empty($this->config['aggregate'])) $sql .= '(';
        
        $sql .= "location_data ='".serialize($this->loc)."'";
        
        if (!empty($this->config['aggregate'])) {
            foreach ($this->config['aggregate'] as $src) {
                $loc = expCore::makeLocation($this->baseclassname, $src);
                $sql .= " OR location_data ='".serialize($loc)."'";
            }
            
            $sql .= ')';
        }       
        
        return $sql;
    }
}

?>