/**
 * SlideDeck 2 Professional for WordPress Admin JavaScript
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


(function($,window,undefined){SlideDeckPlugin.CustomCSSEditor={textarea:null,initialize:function(){var self=this;self.textarea=$('#custom-slidedeck-css').find('textarea');if(self.textarea.length){this.editor=CodeMirror.fromTextArea(self.textarea[0],{lineNumbers:true,mode:"css",theme:"slidedeck",readOnly:false,indentUnit:4,tabSize:4,lineWrapping:true,onCursorActivity:function(cm){cm.save();SlideDeckPlugin.CustomCSSEditor.editor.setLineClass(SlideDeckPlugin.CustomCSSEditor.line,null);SlideDeckPlugin.CustomCSSEditor.line=SlideDeckPlugin.CustomCSSEditor.editor.setLineClass(SlideDeckPlugin.CustomCSSEditor.editor.getCursor().line,"activeline")},onChange:function(cm){if(self.textarea.sliderTimer)clearTimeout(self.textarea.sliderTimer);self.textarea.sliderTimer=setTimeout(function(){SlideDeckPreview.ajaxUpdate()},990)}});this.line=this.editor.setLineClass(0,"activeline")}}};$(document).ready(function(){SlideDeckPlugin.CustomCSSEditor.initialize()})})(jQuery,window,null);