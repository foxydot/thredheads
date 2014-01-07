/**
 * SlideDeck 2 Developer for WordPress Admin JavaScript
 * 
 * More information on this project:
 * http://www.slidedeck.com/
 * 
 * Full Usage Documentation: http://www.slidedeck.com/usage-documentation 
 * 
 * @package SlideDeck
 * @subpackage SlideDeck 2 Pro for WordPress
 * 
 * @author dtelepathy
 */

/*
Copyright 2012 digital-telepathy  (email : support@digital-telepathy.com)

This file is part of SlideDeck.

SlideDeck is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

SlideDeck is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with SlideDeck.  If not, see <http://www.gnu.org/licenses/>.
*/


(function($, window, undefined){
    /**
     * Lens Editor Interaction
     * 
     * Interaction for editing interface for lens files
     */
    SlideDeckPlugin.LensEditor = {
        editor: null,
        line: null,
        textarea: null,
        
        initialize: function(){
            var self = this;
            
            $('#lens').bind('change', function(){
                $(this).closest('form').submit();
            });
            $('#lens-change-submit').hide();
            
            this.textarea = $('#slidedeck-lens-editor').find('textarea');
            
            if(this.textarea.length){
                this.editor = CodeMirror.fromTextArea(this.textarea[0], {
                    lineNumbers: true,
                    mode: "html",
                    theme: "slidedeck",
                    readOnly: false,
                    indentUnit: 4,
                    tabSize: 4,
                    lineWrapping: true,
                    onCursorActivity: function() {
                        SlideDeckPlugin.LensEditor.editor.setLineClass(SlideDeckPlugin.LensEditor.line, null);
                        SlideDeckPlugin.LensEditor.line = SlideDeckPlugin.LensEditor.editor.setLineClass(SlideDeckPlugin.LensEditor.editor.getCursor().line, "activeline");
                    }
                });
                this.line = this.editor.setLineClass(0, "activeline");
            }
        }
    };

    /**
     * Lens Management Interaction
     * 
     * Interaction scripting for copying lenses and deleting lenses
     */
    SlideDeckPlugin.LensManagementDevelopers = {
        elems: {},
        
        validateForm: function(submit){
            var self = this;
            
            // If the submit was not passed in, assume we are not submitting the form after the AJAX check
            if(typeof(submit) == 'undefined'){
                submit = false;
            }
            
            // Add a loading icon before starting the AJAX request
            self.elems.newLensSlugLabel.removeClass('invalid valid').addClass('loading');
            
            // AJAX request to check if slug is valid
            $.getJSON(ajaxurl + "?action=slidedeck_validate_copy_lens&slug=" + this.elems.newLensSlug.val(), function(data){
                // Remove the loading icon to be replaced by a validation response icon
                self.elems.newLensSlugLabel.removeClass('loading');
                
                if(data.valid === true){
                    self.elems.newLensSlugLabel.removeClass('invalid').addClass('valid');
                    
                    // If submit was passed in and is boolean(true), submit the form
                    if(submit === true){
                        self.elems.copyLensForm[0].submit();
                    }
                } else {
                    self.elems.newLensSlugLabel.removeClass('valid').addClass('invalid');
                }
            });
        },
        
        initialize: function(){
            var self = this;
            
            this.elems.copyLensForm = $('#slidedeck-copy-lens');
            
            if(this.elems.copyLensForm.length){
                this.elems.newLensSlug = $('#new-lens-slug');
                this.elems.newLensSlugLabel = this.elems.newLensSlug.closest('label');
                
                // Check for a valid lens slug name on keyup event in the slug field
                this.elems.newLensSlug.bind('keyup', function(event){
                    if(this.timer) {
                        clearTimeout(this.timer);
                    }
                    this.timer = setTimeout(function(){
                        self.validateForm();
                    },100);
                });
                
                // Validate for a unique slug on the form submit
                this.elems.copyLensForm.bind('submit', function(event){
                    event.preventDefault();
                    
                    self.validateForm(true);
                });
            }
        }
    };
    

    $(document).ready(function(){
        // Lens editing view interaction JavaScript
        SlideDeckPlugin.LensEditor.initialize();
        // Lens management view interaction JavaScript
        SlideDeckPlugin.LensManagementDevelopers.initialize();
    });
})(jQuery, window, null);