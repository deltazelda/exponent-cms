<?php
/**
 *  This file is part of Exponent
 *  Exponent is free software; you can redistribute
 *  it and/or modify it under the terms of the GNU
 *  General Public License as published by the Free
 *  Software Foundation; either version 2 of the
 *  License, or (at your option) any later version.
 *
 * The file that holds the expJavascript class
 *
 * @link http://www.gnu.org/licenses/gpl.txt GPL http://www.gnu.org/licenses/gpl.txt
 * @package Exponent-CMS
 * @copyright 2004-2011 OIC Group, Inc.
 * @author Adam Kessler <adam@oicgroup.net>
 * @version 2.0.0
 */
/** @define "BASE" "../../.." */


/**
 * This is the class expJavascript
 *
 * @subpackage Core-Subsytems
 * @package Framework
 */

class expJavascript {
	public static function inAjaxAction() {
		return empty($_REQUEST['ajax_action']) ? false : true;
	}

	public static function requiresJSON() {
		return !empty($_REQUEST['json']) ? true : false;
	}
	
	public static function parseJSFiles() {
        global $userjsfiles,$expJS,$yui2js,$yui3js;
        
    	$scripts = "";
        if (!empty($yui2js)) {
        	require_once(BASE.'external/lissa/class.lissa.php');        
        
            // instantiate loader class for yui2
            $yui2Loader = new Lissa(YUI2_VERSION, null);

            // instantiate loader class for yui3
            //$yui3Loader = new Lissa(YUI3_VERSION, null, $expJS);

            // load Exponent's yui2 dependencies
            $yui2Loader->load("dom");
            $yui2Loader->load("event");

            // load yui2 modules called for via the scipt plugin
            foreach ($yui2js as $key=>$mod) {
                $yui2Loader->load($mod);
            }
            $yui2Loader->combine = intval(MINIFY);
            $scripts = "\r\n\t"."<!-- YUI2 Scripts -->"."\r\n";
            $scripts .= $yui2Loader->scripts()."\r\n";
        }
        
        // load yui3 modules called for via the scipt plugin
        // if (!empty($yui3js)) {
        //     foreach ($yui3js as $key=>$mod) {
        //         $yui3Loader->load($mod);
        //     }
        // }
        
        // load external (non-yui) scripts
        // if (!empty($expJS)) {
        //     foreach ($expJS as $key=>$mod) {
        //         $yui3Loader->load($mod['name']);
        //     }
        // }
                
        // $yui3Loader->combine = intval(MINIFY);
        
        $scripts .= "\t"."<!-- EXPONENT namespace setup -->"."\r\n";
        $scripts .= "\t".'<script type="text/javascript" src="'.PATH_RELATIVE.'exponent.js.php"></script>'."\r\n";

        $scripts .= (!empty($yui3js)) ? "\r\n\t"."<!-- YUI3 Scripts -->"."\r\n\t".'<script type="text/javascript" src="'.YUI3_PATH.'yui/yui-min.js"></script>'."\r\n" : "";
        //$scripts .= "\r\n\t"."<meta id=\"yui3marker\" />"."\r\n";
        if (!empty($expJS)) {
            foreach ($expJS as $key=>$mod) {
                //eDebug($mod['name']);
                $scripts .= "\t".'<script type="text/javascript" src="'.$mod['fullpath'].'"></script>'."\r\n";
            }
        }



        //$html .= "\t".$expYUIJSLoader->js()."\r\n";
        return $scripts;
	}
	
	public static function footJavascriptOutput() {
        global $js2foot;
        $html = "";
        // need to have some control over which scripts execute first.
        // solution: alphabetical by unique
        if(!empty($js2foot)){
            ksort($js2foot);
            foreach($js2foot as $key=>$file){
                $html.= $file."\r\n";
            }            
        } 
        
        return '<script type="text/javascript" charset="utf-8">//<![CDATA['."\r\n".$html.'//]]></script>';
        
	}
	
    public static function pushToFoot($params) {
    	global $js2foot,$yui2js,$yui3js,$expJS;

    	if (self::inAjaxAction()) {
		    echo "<div class=\"io-execute-response\">";
		    
            if(!empty($params['yui2mods'])){
                $toreplace = array('"',"'"," ");
                $stripmodquotes = str_replace($toreplace, "", $params['yui2mods']);               
                $splitmods = explode(",",$stripmodquotes);

                 require_once(BASE.'external/lissa/class.lissa.php');        

                // instantiate loader class for yui2
                $yui2Loader = new Lissa(YUI2_VERSION, null);

                // instantiate loader class for yui3
                //$yui3Loader = new Lissa(YUI3_VERSION, null, $expJS);

                // load yui2 modules called for via the scipt plugin
                foreach ($splitmods as $key=>$mod) {
                    $yui2Loader->load($mod);
                }
                $yui2Loader->combine = intval(MINIFY);
                $scripts = "\r\n\t"."<!-- YUI2 Scripts -->"."\r\n";
                $scripts .= $yui2Loader->scripts()."\r\n";
                echo $scripts;
            }
            

    	    if ($params['src']) {
                echo '<script type="text/javascript" src="'.$params['src'].'"></script>';
    	    }
    	    
		    echo "
		    <script id=\"".$params['unique']."\" type=\"text/javascript\" charset=\"utf-8\">
		      ".$params['content']."
		    </script>
		    </div>
		    ";
		    return true;
    	}


    	if (!empty($params['src'])) {
    	    //$src = str_replace(URL_FULL,PATH_RELATIVE,$params['src']);
    	    $src = $params['src'];
    	    //if (file_exists(str_replace(PATH_RELATIVE,"",$src))) {
                $expJS[$params['unique']] = array(
                        "name" => $params['unique'],
                        "type" => 'js',
                        "fullpath" => $src
                );
            // } else {
            //     flash('error',"Exponent could not find ".$src.". Check to make sure the path is correct.");
            // }
    	}

        // if(!empty($params['yui2mods'])){
        //             $toreplace = array('"',"'"," ");
        //             $stripmodquotes = str_replace($toreplace, "", $params['yui2mods']);               
        //             $splitmods = explode(",",$stripmodquotes);
        // 
        //             foreach ($splitmods as $key=>$val){
        //                 $yui2js[$val] = $val;
        //             }
        //         }

		if (stristr($params['content'],"use('*',") && isset($params['yui3mods'])) {
            $params['content'] = str_replace("use('*',",('use(\''.str_replace(',','\',\'',$params['yui3mods']).'\','),$params['content']);
            $yui3js["yui"] = "yui";
		}

    	if(!empty($params['yui3mods'])){
            $toreplace = array('"',"'"," ");
            $stripmodquotes = str_replace($toreplace, "", $params['yui3mods']);               
            $splitmods = explode(",",$stripmodquotes);

            foreach ($splitmods as $key=>$val){
                $yui3js[$val] = $val;
            }
        }

    	$js2foot[$params['unique']] = $params['content'];
    }
	
}
?>



