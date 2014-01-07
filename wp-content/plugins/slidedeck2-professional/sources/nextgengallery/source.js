(function($){
    window.NextGenSource = {
        elems: {},
         
        initialize: function(){
            var self = this;
            
            this.elems.form = $('#slidedeck-update-form');
            
            this.elems.form.delegate('#options-ngg_gallery_or_album-gallery, #options-ngg_gallery_or_album-album', 'change', function(event){
                switch( event.target.id ){
                    case 'options-ngg_gallery_or_album-gallery':
                        $('li.nextgen-album').hide();
                        $('li.nextgen-gallery').show();
                    break;
                    case 'options-ngg_gallery_or_album-album':
                        $('li.nextgen-gallery').hide();
                        $('li.nextgen-album').show();
                    break;
                }
            });
        }
    };
    
    $(document).ready(function(){
        NextGenSource.initialize();
    });

})(jQuery);

