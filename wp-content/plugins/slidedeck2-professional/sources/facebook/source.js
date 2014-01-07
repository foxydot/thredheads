(function($){
    // Get ready to trigger the ajax update.
    $(window).bind( 'load.facebook_token',function(){
        if( document.location.search.match(/&token=(.+)/) ){
            SlideDeckPreview.ajaxUpdate();
            $(window).unbind('load.facebook_token');
        }
    });
    
    var ajaxOptions = [
        "options[facebook_username]",
        "options[facebook_access_token]",
        "options[facebook_recent_or_likes]"
    ];
    for(var o in ajaxOptions){
        SlideDeckPreview.ajaxOptions.push(ajaxOptions[o]);
    }
    
    $('#slidedeck-content-control').delegate('#get-facebook-access-token-link', 'click', function(event){
        event.preventDefault();
        
        var data = $('#slidedeck-update-form').serialize();
            data = data.replace(/_wpnonce([^\=]*)\=([a-zA-Z0-9]+)/gi, "");
            data = data.replace(/action\=([^\&]+)/, "");
        
        $.ajax({
            url: this.href,
            data: data,
            dataType: "JSON",
            type: "post",
            success: function(data){
                if(data.valid == true){
                    window.onbeforeunload = null;
                    document.location.href = data.url;
                }
            }
        });
    });
})(jQuery);
