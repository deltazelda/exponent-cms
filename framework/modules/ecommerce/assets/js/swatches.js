YUI({base:EXPONENT.URL_FULL+'/external/yui3/build/'}).use('node','anim', function(Y) {
    // set up the images with correct z-indexes to put the first image on top
    var imgs = Y.all('.ecom-images img.large-img');
    var thumbs = Y.all('.thumbnails img');
    var swatches = Y.all('.swatches .swatch');

    //remove loading
    Y.get('.loading-images').removeClass('loading-images');

    var resetZ = function(n,y){
        n.setStyles({'zIndex':0,'display':'none'});
        n.set('id','exp-ecom-msi-'+y);
    }

    imgs.each(resetZ);
    imgs.item(0).setStyles({'zIndex':'1','display':'block'});
    
    swatches.each(function(n,y){
        n.set('id','exp-ecom-ms-'+y)
    });
    
    swatches.on('click',function(e){
        imgs.each(resetZ);
        var curImg = imgs.item(swatches.indexOf(e.target));
        var imgWin = curImg.ancestor('ul.enlarged');
        imgWin.setStyle('height',curImg.get('height')+'px');
        //animImgWin(imgWin,curImg.get('height'));
        curImg.setStyles({'zIndex':'1','display':'block'});
    });

    thumbs.on('click',function(e){
        imgs.each(resetZ);
        
        if (swatches.size()!=0) {
            var processedIndex = thumbs.indexOf(e.target)==0 ? 0 : swatches.size()+thumbs.indexOf(e.target)-1;
        } else {
            var processedIndex = thumbs.indexOf(e.target);
        }
        var curImg = imgs.item(processedIndex);   
        curImg.ancestor('ul.enlarged').setStyle('height',curImg.get('height')+'px');                
        curImg.setStyles({'zIndex':'1','display':'block'});
    });
    
    // animation...  too much for now, but we'll leave the code
    var animImgWin = function (node,h) {
        var hAnim = new Y.Anim({
                node: node,
                to: {height: h},
                easing:Y.Easing.easeOut,
                duration:0.5
        });
        hAnim.run();
    }

});