{control type="checkbox" name="no_shipping" label="This item doesn't require shipping" value=1 checked=$record->no_shipping}
{control type="dropdown" name="required_shipping_calculator_id" id="required_shipping_calculator_id" label="Required Shipping Service" includeblank="--- Select a shipping service ---" items=$shipping_services value=$record->required_shipping_calculator_id onchange="switchMethods();"}
{foreach from=$shipping_methods key=calcid item=methods name=sm}
    <div id="dd-{$calcid}" class="hide methods">
    {control type="dropdown" name="required_shipping_methods[`$calcid`]" label="Shipping Methods" items=$methods value=$record->required_shippng_method}
    </div>
{/foreach}
{control type="text" name="weight" label="Item Weight" size=4 filter=decimal value=$record->weight}
{control type="text" name="width" label="Width (in inches)" size=4 filter=decimal value=$record->width}
{control type="text" name="height" label="Height (in inches)" size=4 filter=decimal value=$record->height}                
{control type="text" name="length" label="Length (in inches)" size=4 filter=decimal value=$record->length}          
{control type="text" name="surcharge" label="Surcharge" size=4 filter=money value=$record->surcharge}

{script unique="prodedit" yui3mods=1}
{literal}
YUI(EXPONENT.YUI3_CONFIG).use('yui2-yahoo-dom-event', function(Y) {
    switchMethods = function() {
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
});

{/literal}
{/script}
