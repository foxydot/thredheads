jQuery( document ).ready( function( $ ) {
    tinymce.create('tinymce.plugins.pbtipsy', {
        init : function(ed, url){
              ed.addButton	('tipbutton', {
                title: 'ilcsyntax.php',
                image: pb_tipsy_location + '/images/tipsy1.png',
                cmd: 'mceilcPHP',
                onclick : function (){
                tb_show( 'Tipsy', 'post.php#TB_inline?inlineId=pb_tiptip' ); return false;
                }
            });
            ed.addShortcut('alt+ctrl+x', 'mceilcPHP');
        },
        createControl : function(n, cm){
            return null;
        },
        getInfo : function(){
            return {
                longname: 'Pluginbuddy Tipsy',
                author: '@j0shben',
                authorurl: 'http://pluginbuddy.com/',
                version: "1.0"
            };
        }
    });
    tinymce.PluginManager.add('pbtipsy', tinymce.plugins.pbtipsy);
});
