{*
 * Copyright (c) 2007-2008 OIC Group, Inc.
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
 
 {css unique="product-edit" link="`$asset_path`css/product_edit.css" corecss="tree,panels"}

 {/css}

{script unique="editor" src="`$smarty.const.PATH_RELATIVE`external/editors/ckeditor/ckeditor.js"}

{/script}

<div id="editproduct" class="module store edit yui-skin-sam yui3-skin-sam exp-skin exp-admin-skin">
    {if $record->id != ""}
        <h1>Edit Information for {$modelname}</h1>
    {else}
        <h1>New {$modelname}</h1>
    {/if}
    

    {script unique="prodtabs" yui3mods="1"}
    {literal}
        	
        YUI(EXPONENT.YUI3_CONFIG).use("yui", "get", "tabview", "gallery-widget-io", function(Y) {
           {/literal}
           {if $record->id}
               var pid = {$record->id};
           {/if}
           {literal}
           
            var feeds = {
               'General': 'view=edit_general',
               'Pricing, Tax & Discounts': 'view=edit_pricing',
               'Images & Files': 'view=edit_images',
               'Quantity': 'view=edit_quantity',
               'Shipping': 'view=edit_shipping',
               'Categories': 'view=edit_categories',
               'Options': 'view=edit_options',
               'Featured': 'view=edit_featured',
               'Related Products': 'view=edit_related',
               'User Input Fields': 'view=edit_userinput',
               'Active & Status Settings': 'view=edit_status',   
               'Meta Info': 'view=edit_meta',
               'Notes': 'view=edit_notes',
               'Extra Fields': 'view=edit_extrafields',
               'SKUS/Model': 'view=edit_model',
               'Misc': 'view=edit_misc'
            },

            TabIO = function(config) {
                TabIO.superclass.constructor.apply(this, arguments); 
            };

            Y.extend(TabIO, Y.Plugin.WidgetIO, {
                _tabCache:[],
                _ioScriptsExecuted:[],
                
                initializer: function() {
                    var tab = this.get('host');
                    tab.on('selectedChange', this.afterSelectedChange);
                },

                afterSelectedChange: function(e) { // this === tab
                    var tab = this.io.get('host');
                    if (e.newVal && Y.Lang.isUndefined(this.io._tabCache[tab.get('label')])) { // tab has been selected
                        this.io.refresh();
                    }
                },

                setContent: function(content) {                    
                    var tab = this.get('host');
                    //console.debug(this._tabCache[tab.get('label')]);
                    if (Y.Lang.isUndefined(this._tabCache[tab.get('label')])&&content!=="") {
                        tab.set('content', content);
                        this._tabCache[tab.get('label')] = 1;
                        this._execIOResponseScripts();
                    };
                },

                _toggleLoadingClass: function(add) {
                    this.get('host').get('panelNode')
                        .toggleClass(this.get('host').getClassName('loading'), add);
                },

                _execIOResponseScripts: function() {
                    var tab = this.get('host').get('panelNode');
                    
                    // js
                    tab.all('.io-execute-response script').each(function(n){
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
                    tab.all('.io-execute-response link').each(function(n){
                        var url = n.get('href');
                        Y.Get.css(url);
                    });
                }

            }, {
                NAME: 'tabIO',
                NS: 'io'
            });

            var tabview = new Y.TabView();

            Y.each(feeds, function(src, label) {
               var tContent = EXPONENT.URL_FULL+"index.php?controller=store&action=edit&id="+pid+"&ajax_action=1&"+src;
               //console.debug(tContent);
               tabview.add({
                    label: label,
                    plugins: [{
                        fn: TabIO, 
                        cfg: {
                            uri: tContent
                        }
                    }]
               });
            });

            tabview.render('#demo');
            
            // Y.on('io:complete',function(){
            //     var ioScriptsExecuted = [];
            //     var ioScripts = Y.all('.io-execute-response script');
            //     console.debug(ioScripts);
            //     if (!Y.Lang.isNull(ioScripts)) {
            //         ioScripts.each(function(n){
            //             console.debug(n.get('src'));
            //             eval(n.get('innerHTML'));
            //             ioScriptsExecuted[n.get('id')];
            //         });
            //     };
            //     
            // });

            Y.one("#loading").remove();
            //Y.one(".exp-tabview").ancestor('.module').removeClass('hide');
        });
    {/literal}
    {/script}

    {form action=update}
        {control type="hidden" name="id" value=$record->id}
        <div id="demo"></div>
        {control type="buttongroup" submit="Save Product" cancel="Cancel"}
        {if isset($record->original_id)}
            {control type="hidden" name="original_id" value=$record->original_id}
            {control type="hidden" name="original_model" value=$record->original_model}
            {control type="checkbox" name="copy_children" label="Copy Child Products?" value="1"}
            {control type="checkbox" name="copy_related" label="Copy Related Products?" value="1"}
            {control type="checkbox" name="adjust_child_price" label="Reset Price on Child Products?" value="1"}
            {control type="text" name="new_child_price" label="New Child Price" value=""}
            {*control type="checkbox" name="copy_related" label="Copy Related Products?" value="1"*}
        {/if}
    {/form}
</div>
<div id="loading" class="loadingdiv">Loading</div>

{*script unique="prodedit"}
{literal}
    function switchMethods() {
        var dd = YAHOO.util.Dom.get('required_shipping_calculator_id');
        var methdd = YAHOO.util.Dom.get('dd-'+dd.value);

        var otherdds = YAHOO.util.Dom.getElementsByClassName('methods', 'div');
        
        for(i=0; i<otherdds.length; i++) {
            if (otherdds[i].id == 'dd-'+dd.value) {
                YAHOO.util.Dom.setStyle(otherdds[i].id, 'display', 'block');
            } else {
                YAHOO.util.Dom.setStyle(otherdds[i].id, 'display', 'none');
            }
            
        }
        YAHOO.util.Dom.setStyle(methdd, 'display', 'block');
        //console.debug(methdd);
        //console.debug(dd.value);
    }
    YAHOO.util.Event.onDOMReady(switchMethods);
{/literal}
{/script*}
