<ul class="slide-content-fields">
    <li><label><?php _e( "Title", $namespace ); ?><br />
        <input type="text" name="post_title" value="<?php echo get_the_title( $slide->ID ); ?>" />
    </label></li>
</ul>

<div class="p"><label><?php _e( "Insert HTML", $namespace ); ?></label><br />
<textarea name="post_excerpt" cols="40" rows="20"><?php echo esc_textarea( $slide->post_content ); ?></textarea></div>

<script type="text/javascript">
    (function($, window, undefined){
        window.htmlSlideEditor = CodeMirror.fromTextArea($('#slidedeck-custom-slide-editor textarea[name="post_excerpt"]')[0], {
            lineNumbers: true,
            theme: "slidedeck",
            readOnly: false,
            indentUnit: 4,
            tabSize: 4,
            lineWrapping: true,
            onCursorActivity: function() {
                htmlSlideEditor.setLineClass(htmlSlideEditorLine, null);
                htmlSlideEditorLine = htmlSlideEditor.setLineClass(htmlSlideEditor.getCursor().line, "activeline");
            }
        });
        window.htmlSlideEditorLine = htmlSlideEditor.setLineClass(0, "activeline");
        setTimeout(function(){
            window.htmlSlideEditor.refresh();
        }, 250);
    })(jQuery, window, null);
</script>
