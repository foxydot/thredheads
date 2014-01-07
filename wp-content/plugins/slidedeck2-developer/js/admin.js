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

(function($,d,e){SlideDeckPlugin.LensEditor={editor:null,line:null,textarea:null,initialize:function(){var a=this;$('#lens').bind('change',function(){$(this).closest('form').submit()});$('#lens-change-submit').hide();this.textarea=$('#slidedeck-lens-editor').find('textarea');if(this.textarea.length){this.editor=CodeMirror.fromTextArea(this.textarea[0],{lineNumbers:true,mode:"html",theme:"slidedeck",readOnly:false,indentUnit:4,tabSize:4,lineWrapping:true,onCursorActivity:function(){SlideDeckPlugin.LensEditor.editor.setLineClass(SlideDeckPlugin.LensEditor.line,null);SlideDeckPlugin.LensEditor.line=SlideDeckPlugin.LensEditor.editor.setLineClass(SlideDeckPlugin.LensEditor.editor.getCursor().line,"activeline")}});this.line=this.editor.setLineClass(0,"activeline")}}};SlideDeckPlugin.LensManagementDevelopers={elems:{},validateForm:function(b){var c=this;if(typeof(b)=='undefined'){b=false}c.elems.newLensSlugLabel.removeClass('invalid valid').addClass('loading');$.getJSON(ajaxurl+"?action=slidedeck_validate_copy_lens&slug="+this.elems.newLensSlug.val(),function(a){c.elems.newLensSlugLabel.removeClass('loading');if(a.valid===true){c.elems.newLensSlugLabel.removeClass('invalid').addClass('valid');if(b===true){c.elems.copyLensForm[0].submit()}}else{c.elems.newLensSlugLabel.removeClass('valid').addClass('invalid')}})},initialize:function(){var b=this;this.elems.copyLensForm=$('#slidedeck-copy-lens');if(this.elems.copyLensForm.length){this.elems.newLensSlug=$('#new-lens-slug');this.elems.newLensSlugLabel=this.elems.newLensSlug.closest('label');this.elems.newLensSlug.bind('keyup',function(a){if(this.timer){clearTimeout(this.timer)}this.timer=setTimeout(function(){b.validateForm()},100)});this.elems.copyLensForm.bind('submit',function(a){a.preventDefault();b.validateForm(true)})}}};$(document).ready(function(){SlideDeckPlugin.LensEditor.initialize();SlideDeckPlugin.LensManagementDevelopers.initialize()})})(jQuery,window,null);