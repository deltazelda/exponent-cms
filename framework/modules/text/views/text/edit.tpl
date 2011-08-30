{*
 * Copyright (c) 2007-2011 OIC Group, Inc.
 * Written and Designed by Adam Kessler
 *
 * This file is part of Exponent
 *
 * Exponent is free software; you can redistribute
 * it and/or modify it under the terms of the GNU
 * General Public License as published by the Free
 * Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * GPL: http://www.gnu.org/licenses/gpl.txt
 *
 *}
 
 
 {css unique="myID"}
 {literal}
 	.yui3-panel {
   outline:none;
 }

 .yui3-panel #panelContent {
   -webkit-box-shadow: 0px 0px 2px black;
   -moz-box-shadow: 0px 0px 2px black;
   box-shadow: 0px 0px 2px black;
 }
 .yui3-panel #panelContent .yui3-widget-hd {
   font-weight:bold;
   padding:5px;

 }

 #panelContent .yui3-widget-bd {
   padding:15px;
   background:white;
   text-align:center;
 }


 .yui3-skin-sam .yui3-widget-mask {
   background-color: #223460;
   opacity: 0.9;
 }

 {/literal}
 {/css}

 

<div class="module text edit">
    {*if $record->id != ""}
        <h1>Editing: {$record->title}</h1>
    {else}
        <h1>New {$modelname}</h1>
    {/if}

    {form action=update}
        {control type=hidden name=id value=$record->id}
        {control type=hidden name=rank value=$record->rank}
        {control type=text name=title label="Title" value=$record->title|escape:"html"}
        {control type=html name=body label="Body Content" value=$record->body}
        {if $config.filedisplay}
            {control type="files" name="files" label="Files" value=$record->expFile}
        {/if}
        {control type=buttongroup submit="Save Text" cancel="Cancel"}
    {/form*}   
</div>

<div class="yui3-skin-sam">
    <div id="panelContent"> 
      <div class="yui3-widget-hd"> 
        Showing an animated panel
      </div> 
      <div class="yui3-widget-bd"> 
        <p>Making panels animate is easy with the "transition" module!</p> 

      </div> 
    </div> 
</div>

<button type="submit" id="openButton">POP!</button>

{script unique="test" yui3mods=""}
{literal}
YUI(EXPONENT.YUI3_CONFIG).use('node-load',"transition", "panel", function(Y) {
    
    var myCallBack = function(o,k) {
        Y.all('.io-execute-response script').each(function(n){
            if(!n.get('src')){
                eval(n.get('innerHTML'));
            } else {
                var url = n.get('src');
                if (url.indexOf("ckeditor")) {
                    Y.Get.script(url);
                };
            };
        });
        // css
        //console.debug(tab.all('.io-execute-response link'));
        Y.all('.io-execute-response link').each(function(n){
            var url = n.get('href');
            Y.Get.css(url);
        });
    }
    
    // Y.one('.module.text.edit').load(
    //     EXPONENT.URL_FULL+'index.php?controller=text&action=showall&view=test&ajax_action=1',
    //     '',
    //     myCallBack
    // ).transition({
    //     duration: 5, // seconds
    //     opacity : 0
    // });
    
    var openBtn = Y.one('#openButton'),
    panel = new Y.Panel({
      srcNode: "#panelContent",
      width:330,
      centered:true,
      modal:true,
      visible:false,
      zIndex: 5,
      buttons: [
        {
          value: "Close the panel",
          action: function(e) {
            e.preventDefault();
            hidePanel();
          },
          section: "footer"
        }
      ],
      render:true
    }),

    bb = panel.get('boundingBox');

    openBtn.on('click', function(e) {
      showPanel();
    });

    function showPanel () {
      panel.show();
      bb.transition({
        duration: 0.5,
        opacity:1
        //top:"80px"
      });
    }

    function hidePanel () {
      bb.transition({
        duration: 0.5,
        opacity:0
        // top:"-300px"
      }, function() {
        panel.hide();
      });
    }

    
    
});

{/literal}
{/script}