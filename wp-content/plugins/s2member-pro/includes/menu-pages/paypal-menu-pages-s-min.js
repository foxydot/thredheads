jQuery(document).ready(function($){var esc_attr=esc_html=function(str){return String(str).replace(/"/g,"&quot;").replace(/\</g,"&lt;").replace(/\>/g,"&gt;")};if(location.href.match(/page\=ws-plugin--s2member-pro-paypal-ops/)){$("select#ws-plugin--s2member-auto-eot-system-enabled").change(function(){var $this=$(this),val=$this.val();var $viaCron=$("p#ws-plugin--s2member-auto-eot-system-enabled-via-cron");if(val==2){$viaCron.show()}else{$viaCron.hide()}})}else{if(location.href.match(/page\=ws-plugin--s2member-pro-paypal-forms/)){var taxMayApply=("<?php echo (int)c_ws_plugin__s2member_pro_paypal_utilities::paypal_tax_may_apply(); ?>"==="1")?true:false;var handleFormDescriptions=function(){var form=this.id.replace(/^ws-plugin--s2member-pro-(.+?)-(trial-period|trial-amount|trial-term|amount|term|currency)$/g,"$1");var trialAmount=$("input#ws-plugin--s2member-pro-"+form+"-trial-amount").val().replace(/[^0-9\.]/g,"");var trialPeriod=$("input#ws-plugin--s2member-pro-"+form+"-trial-period").val().replace(/[^0-9]/g,"");var trialTermLabel=$.trim($("select#ws-plugin--s2member-pro-"+form+"-trial-term :selected").text());var regAmount=$("input#ws-plugin--s2member-pro-"+form+"-amount").val().replace(/[^0-9\.]/g,"");var regTermLabel=$.trim($("select#ws-plugin--s2member-pro-"+form+"-term :selected").text());var currencyCode=$("select#ws-plugin--s2member-pro-"+form+"-currency").val().replace(/[^A-Z]/g,"");$("input#ws-plugin--s2member-pro-"+form+"-desc").val(((trialPeriod>0)?trialPeriod+" "+((trialPeriod==1)?trialTermLabel.replace(/s$/,""):trialTermLabel)+" "+((trialAmount>0)?"@ $"+trialAmount:"free")+" / then ":"")+"$"+regAmount+" "+currencyCode+""+((taxMayApply)?" + tax":"")+" / "+regTermLabel)};$("div.ws-menu-page select[id]").filter(function(){return this.id.match(/^ws-plugin--s2member-pro-(level[1-9][0-9]*|modification)-term$/)}).change(function(){var form=this.id.replace(/^ws-plugin--s2member-pro-(.+?)-term$/g,"$1");var trialDisabled=($(this).val().split("-")[2].replace(/[^0-1BN]/g,"")==="BN")?1:0;$("p#ws-plugin--s2member-pro-"+form+"-trial-line").css("display",(trialDisabled?"none":""));$("span#ws-plugin--s2member-pro-"+form+"-trial-then").css("display",(trialDisabled?"none":""));(trialDisabled)?$("input#ws-plugin--s2member-pro-"+form+"-trial-period").val(0):null;(trialDisabled)?$("input#ws-plugin--s2member-pro-"+form+"-trial-amount").val("0.00"):null});$("div.ws-menu-page input[id]").filter(function(){return this.id.match(/^ws-plugin--s2member-pro-(level[1-9][0-9]*|modification|ccap)-ccaps$/)}).keyup(function(){var value=this.value.replace(/^(-all|-al|-a|-)[;,]*/gi,""),_all=(this.value.match(/^(-all|-al|-a|-)[;,]*/i))?"-all,":"";if(value.match(/[^a-z_0-9,]/)){this.value=_all+$.trim($.trim(value).replace(/[ \-]/g,"_").replace(/[^a-z_0-9,]/gi,"").toLowerCase())}});$("div.ws-menu-page input[id]").filter(function(){return this.id.match(/^ws-plugin--s2member-pro-(level[1-9][0-9]*|modification)-trial-amount$/)}).keyup(handleFormDescriptions);$("div.ws-menu-page input[id]").filter(function(){return this.id.match(/^ws-plugin--s2member-pro-(level[1-9][0-9]*|modification)-trial-period$/)}).keyup(handleFormDescriptions);$("div.ws-menu-page select[id]").filter(function(){return this.id.match(/^ws-plugin--s2member-pro-(level[1-9][0-9]*|modification)-trial-term$/)}).change(handleFormDescriptions);$("div.ws-menu-page input[id]").filter(function(){return this.id.match(/^ws-plugin--s2member-pro-(level[1-9][0-9]*|modification)-amount$/)}).keyup(handleFormDescriptions);$("div.ws-menu-page select[id]").filter(function(){return this.id.match(/^ws-plugin--s2member-pro-(level[1-9][0-9]*|modification)-term$/)}).change(handleFormDescriptions);$("div.ws-menu-page select[id]").filter(function(){return this.id.match(/^ws-plugin--s2member-pro-(level[1-9][0-9]*|modification)-currency$/)}).change(handleFormDescriptions);ws_plugin__s2member_pro_paypalFormGenerate=function(form){var shortCodeTemplate='[s2Member-Pro-PayPal-Form %%attrs%% accept="paypal,visa,mastercard,amex,discover,maestro,solo" accept_via_paypal="paypal" coupon="" accept_coupons="0" default_country_code="" captcha="0" /]',shortCodeTemplateAttrs="",labels={};eval("<?php echo c_ws_plugin__s2member_utils_strings::esc_dq($vars['labels']); ?>");var shortCode=$("input#ws-plugin--s2member-pro-"+form+"-shortcode");var modLevel=$("select#ws-plugin--s2member-pro-modification-level");var level=(form==="modification")?modLevel.val().split(":",2)[1]:form.replace(/^level/,"");var label=labels["level"+level].replace(/"/g,"");var trialAmount=$("input#ws-plugin--s2member-pro-"+form+"-trial-amount").val().replace(/[^0-9\.]/g,"");var trialPeriod=$("input#ws-plugin--s2member-pro-"+form+"-trial-period").val().replace(/[^0-9]/g,"");var trialTerm=$("select#ws-plugin--s2member-pro-"+form+"-trial-term").val().replace(/[^A-Z]/g,"");var regAmount=$("input#ws-plugin--s2member-pro-"+form+"-amount").val().replace(/[^0-9\.]/g,"");var regPeriod=$("select#ws-plugin--s2member-pro-"+form+"-term").val().split("-")[0].replace(/[^0-9]/g,"");var regTerm=$("select#ws-plugin--s2member-pro-"+form+"-term").val().split("-")[1].replace(/[^A-Z]/g,"");var regRecur=$("select#ws-plugin--s2member-pro-"+form+"-term").val().split("-")[2].replace(/[^0-1BN]/g,"");var regRecurTimes="",regRecurRetries="2";var desc=$.trim($("input#ws-plugin--s2member-pro-"+form+"-desc").val().replace(/"/g,""));var localeCode="",digital="0",noShipping="1";var pageStyle=$.trim($("input#ws-plugin--s2member-pro-"+form+"-page-style").val().replace(/"/g,""));var currencyCode=$("select#ws-plugin--s2member-pro-"+form+"-currency").val().replace(/[^A-Z]/g,"");var cCaps=$.trim($.trim($("input#ws-plugin--s2member-pro-"+form+"-ccaps").val()).replace(/^(-all|-al|-a|-)[;,]*/gi,"").replace(/[ \-]/g,"_").replace(/[^a-z_0-9,]/gi,"").toLowerCase());cCaps=($.trim($("input#ws-plugin--s2member-pro-"+form+"-ccaps").val()).match(/^(-all|-al|-a|-)[;,]*/i))?((cCaps)?"-all,":"-all")+cCaps.toLowerCase():cCaps.toLowerCase();trialPeriod=(regRecur==="BN")?"0":trialPeriod;trialAmount=(!trialAmount||isNaN(trialAmount)||trialAmount<0.01||trialPeriod<=0)?"0":trialAmount;regAmount=(!regAmount||isNaN(regAmount)||regAmount<0.01||regAmount<=0)?"0":regAmount;var levelCcapsPer=(regRecur==="BN"&&regTerm!=="L")?level+":"+cCaps+":"+regPeriod+" "+regTerm:level+":"+cCaps;levelCcapsPer=levelCcapsPer.replace(/\:+$/g,"");if(trialAmount!=="0"&&(isNaN(trialAmount)||trialAmount<0)){alert("— Oops, a slight problem: —\n\nWhen provided, Trial Amount must be >= 0.00");return false}else{if(trialAmount!=="0"&&trialAmount>10000&&currencyCode.toUpperCase()==="USD"){alert("— Oops, a slight problem: —\n\nMaximum Trial Amount is: 10000.00");return false}else{if(regAmount!=="0"&&(isNaN(regAmount)||regAmount<0)){alert("— Oops, a slight problem: —\n\nAmount must be >= 0.00");return false}else{if(regAmount>10000&&currencyCode.toUpperCase()==="USD"){alert("— Oops, a slight problem: —\n\nMaximum Amount is: 10000.00");return false}else{if(!desc){alert("— Oops, a slight problem: —\n\nPlease type a Description for this Form.");return false}}}}}shortCodeTemplateAttrs+=(form==="modification")?'modify="1" ':"";shortCodeTemplateAttrs+='level="'+esc_attr(level)+'" ccaps="'+esc_attr(cCaps)+'" desc="'+esc_attr(desc)+'" ps="'+esc_attr(pageStyle)+'" lc="'+esc_attr(localeCode)+'" cc="'+esc_attr(currencyCode)+'" dg="'+esc_attr(digital)+'" ns="'+esc_attr(noShipping)+'" custom="<?php echo c_ws_plugin__s2member_utils_strings::esc_js_sq (esc_attr ($_SERVER["HTTP_HOST"])); ?>"';shortCodeTemplateAttrs+=' ta="'+esc_attr(trialAmount)+'" tp="'+esc_attr(trialPeriod)+'" tt="'+esc_attr(trialTerm)+'" ra="'+esc_attr(regAmount)+'" rp="'+esc_attr(regPeriod)+'" rt="'+esc_attr(regTerm)+'" rr="'+esc_attr(regRecur)+'" rrt="'+esc_attr(regRecurTimes)+'" rra="'+esc_attr(regRecurRetries)+'"';shortCode.val(shortCodeTemplate.replace(/%%attrs%%/,shortCodeTemplateAttrs));alert("Your Form has been generated.\nPlease copy/paste the Shortcode into your WordPress® Editor.");shortCode.each(function(){this.focus(),this.select()});return false};ws_plugin__s2member_pro_paypalCcapFormGenerate=function(){var shortCodeTemplate='[s2Member-Pro-PayPal-Form %%attrs%% accept="paypal,visa,mastercard,amex,discover,maestro,solo" accept_via_paypal="paypal" coupon="" accept_coupons="0" default_country_code="" captcha="0" /]',shortCodeTemplateAttrs="";var shortCode=$("input#ws-plugin--s2member-pro-ccap-shortcode");var desc=$.trim($("input#ws-plugin--s2member-pro-ccap-desc").val().replace(/"/g,""));var regAmount=$("input#ws-plugin--s2member-pro-ccap-amount").val().replace(/[^0-9\.]/g,"");var regPeriod=$("select#ws-plugin--s2member-pro-ccap-term").val().split("-")[0].replace(/[^0-9]/g,"");var regTerm=$("select#ws-plugin--s2member-pro-ccap-term").val().split("-")[1].replace(/[^A-Z]/g,"");var regRecur=$("select#ws-plugin--s2member-pro-ccap-term").val().split("-")[2].replace(/[^0-1BN]/g,"");var localeCode="",digital="0",noShipping="1";var pageStyle=$.trim($("input#ws-plugin--s2member-pro-ccap-page-style").val().replace(/"/g,""));var currencyCode=$("select#ws-plugin--s2member-pro-ccap-currency").val().replace(/[^A-Z]/g,"");var cCaps=$.trim($.trim($("input#ws-plugin--s2member-pro-ccap-ccaps").val()).replace(/^(-all|-al|-a|-)[;,]*/gi,"").replace(/[ \-]/g,"_").replace(/[^a-z_0-9,]/gi,"").toLowerCase());cCaps=($.trim($("input#ws-plugin--s2member-pro-ccap-ccaps").val()).match(/^(-all|-al|-a|-)[;,]*/i))?((cCaps)?"-all,":"-all")+cCaps.toLowerCase():cCaps.toLowerCase();regAmount=(!regAmount||isNaN(regAmount)||regAmount<0.01||regAmount<=0)?"0":regAmount;var levelCcapsPer=(regRecur==="BN"&&regTerm!=="L")?"*:"+cCaps+":"+regPeriod+" "+regTerm:"*:"+cCaps;levelCcapsPer=levelCcapsPer.replace(/\:+$/g,"");if(!cCaps||cCaps==="-all"){alert("— Oops, a slight problem: —\n\nPlease provide at least one Custom Capability.");return false}else{if(regAmount!=="0"&&(isNaN(regAmount)||regAmount<0)){alert("— Oops, a slight problem: —\n\nAmount must be >= 0.00");return false}else{if(regAmount>10000&&currencyCode.toUpperCase()==="USD"){alert("— Oops, a slight problem: —\n\nMaximum Amount is: 10000.00");return false}else{if(!desc){alert("— Oops, a slight problem: —\n\nPlease type a Description for this Form.");return false}}}}shortCodeTemplateAttrs+='level="*" ccaps="'+esc_attr(cCaps)+'" desc="'+esc_attr(desc)+'" ps="'+esc_attr(pageStyle)+'" lc="'+esc_attr(localeCode)+'" cc="'+esc_attr(currencyCode)+'" dg="'+esc_attr(digital)+'" ns="'+esc_attr(noShipping)+'"';shortCodeTemplateAttrs+=' custom="<?php echo c_ws_plugin__s2member_utils_strings::esc_js_sq (esc_attr ($_SERVER["HTTP_HOST"])); ?>" ra="'+esc_attr(regAmount)+'" rp="'+esc_attr(regPeriod)+'" rt="'+esc_attr(regTerm)+'" rr="'+esc_attr(regRecur)+'"';shortCode.val(shortCodeTemplate.replace(/%%attrs%%/,shortCodeTemplateAttrs));alert("Your Form has been generated.\nPlease copy/paste the Shortcode into your WordPress® Editor.");shortCode.each(function(){this.focus(),this.select()});return false};ws_plugin__s2member_pro_paypalSpFormGenerate=function(){var shortCodeTemplate='[s2Member-Pro-PayPal-Form %%attrs%% accept="paypal,visa,mastercard,amex,discover,maestro,solo" accept_via_paypal="paypal" coupon="" accept_coupons="0" default_country_code="" captcha="0" /]',shortCodeTemplateAttrs="";var shortCode=$("input#ws-plugin--s2member-pro-sp-shortcode");var leading=$("select#ws-plugin--s2member-pro-sp-leading-id").val().replace(/[^0-9]/g,"");var additionals=$("select#ws-plugin--s2member-pro-sp-additional-ids").val()||[];var hours=$("select#ws-plugin--s2member-pro-sp-hours").val().replace(/[^0-9]/g,"");var regAmount=$("input#ws-plugin--s2member-pro-sp-amount").val().replace(/[^0-9\.]/g,"");var desc=$.trim($("input#ws-plugin--s2member-pro-sp-desc").val().replace(/"/g,""));var localeCode="",digital="0",noShipping="1";var pageStyle=$.trim($("input#ws-plugin--s2member-pro-sp-page-style").val().replace(/"/g,""));var currencyCode=$("select#ws-plugin--s2member-pro-sp-currency").val().replace(/[^A-Z]/g,"");regAmount=(!regAmount||isNaN(regAmount)||regAmount<0.01||regAmount<=0)?"0":regAmount;if(!leading){alert("— Oops, a slight problem: —\n\nPlease select a Leading Post/Page.\n\n*Tip* If there are no Posts/Pages in the menu, it's because you've not configured s2Member for Specific Post/Page Access yet. See: s2Member -› Restriction Options -› Specific Post/Page Access.");return false}else{if(regAmount!=="0"&&(isNaN(regAmount)||regAmount<0)){alert("— Oops, a slight problem: —\n\nAmount must be >= 0.00");return false}else{if(regAmount>10000&&currencyCode.toUpperCase()==="USD"){alert("— Oops, a slight problem: —\n\nMaximum Amount is: 10000.00");return false}else{if(!desc){alert("— Oops, a slight problem: —\n\nPlease type a Description for this Form.");return false}}}}for(var i=0,ids=leading;i<additionals.length;i++){if(additionals[i]&&additionals[i]!==leading){ids+=","+additionals[i]}}var spIdsHours="sp:"+ids+":"+hours;shortCodeTemplateAttrs+='sp="1" ids="'+esc_attr(ids)+'" exp="'+esc_attr(hours)+'" desc="'+esc_attr(desc)+'" ps="'+esc_attr(pageStyle)+'" lc="'+esc_attr(localeCode)+'" cc="'+esc_attr(currencyCode)+'" dg="'+esc_attr(digital)+'" ns="'+esc_attr(noShipping)+'"';shortCodeTemplateAttrs+=' custom="<?php echo c_ws_plugin__s2member_utils_strings::esc_js_sq (esc_attr ($_SERVER["HTTP_HOST"])); ?>" ra="'+esc_attr(regAmount)+'"';shortCode.val(shortCodeTemplate.replace(/%%attrs%%/,shortCodeTemplateAttrs));alert("Your Form has been generated.\nPlease copy/paste the Shortcode into your WordPress® Editor.");shortCode.each(function(){this.focus(),this.select()});return false};ws_plugin__s2member_pro_paypalRegLinkGenerate=function(){var level=$("select#ws-plugin--s2member-pro-reg-link-level").val().replace(/[^0-9]/g,"");var subscrID=$.trim($("input#ws-plugin--s2member-pro-reg-link-subscr-id").val());var custom=$.trim($("input#ws-plugin--s2member-pro-reg-link-custom").val());var cCaps=$.trim($.trim($("input#ws-plugin--s2member-pro-reg-link-ccaps").val()).replace(/[ \-]/g,"_").replace(/[^a-z_0-9,]/gi,"").toLowerCase());var fixedTerm=$.trim($("input#ws-plugin--s2member-pro-reg-link-fixed-term").val().replace(/[^A-Z 0-9]/gi,"").toUpperCase());var $link=$("p#ws-plugin--s2member-pro-reg-link"),$loading=$("img#ws-plugin--s2member-pro-reg-link-loading");var levelCcapsPer=(fixedTerm&&!fixedTerm.match(/L$/))?level+":"+cCaps+":"+fixedTerm:level+":"+cCaps;levelCcapsPer=levelCcapsPer.replace(/\:+$/g,"");if(!subscrID){alert("— Oops, a slight problem: —\n\nPaid Subscr. ID is a required value.");return false}else{if(!custom||custom.indexOf('<?php echo c_ws_plugin__s2member_utils_strings::esc_js_sq ($_SERVER["HTTP_HOST"]); ?>')!==0){alert("— Oops, a slight problem: —\n\nThe Custom Value MUST start with your domain name.");return false}else{if(fixedTerm&&!fixedTerm.match(/^[1-9]+ (D|W|M|Y|L)$/)){alert("— Oops, a slight problem: —\n\nThe Fixed Term Length is not formatted properly.");return false}}}$link.hide(),$loading.show(),$.post(ajaxurl,{action:"ws_plugin__s2member_reg_access_link_via_ajax",ws_plugin__s2member_reg_access_link_via_ajax:'<?php echo c_ws_plugin__s2member_utils_strings::esc_js_sq (wp_create_nonce ("ws-plugin--s2member-reg-access-link-via-ajax")); ?>',s2member_reg_access_link_subscr_gateway:"paypal",s2member_reg_access_link_subscr_id:subscrID,s2member_reg_access_link_custom:custom,s2member_reg_access_link_item_number:levelCcapsPer},function(response){$link.show().html('<a href="'+esc_attr(response)+'" target="_blank" rel="external">'+esc_html(response)+"</a>"),$loading.hide()});return false};ws_plugin__s2member_pro_paypalSpLinkGenerate=function(){var leading=$("select#ws-plugin--s2member-pro-sp-link-leading-id").val().replace(/[^0-9]/g,"");var additionals=$("select#ws-plugin--s2member-pro-sp-link-additional-ids").val()||[];var hours=$("select#ws-plugin--s2member-pro-sp-link-hours").val().replace(/[^0-9]/g,"");var $link=$("p#ws-plugin--s2member-pro-sp-link"),$loading=$("img#ws-plugin--s2member-pro-sp-link-loading");if(!leading){alert("— Oops, a slight problem: —\n\nPlease select a Leading Post/Page.\n\n*Tip* If there are no Posts/Pages in the menu, it's because you've not configured s2Member for Specific Post/Page Access yet. See: s2Member -› Restriction Options -› Specific Post/Page Access.");return false}for(var i=0,ids=leading;i<additionals.length;i++){if(additionals[i]&&additionals[i]!==leading){ids+=","+additionals[i]}}$link.hide(),$loading.show(),$.post(ajaxurl,{action:"ws_plugin__s2member_sp_access_link_via_ajax",ws_plugin__s2member_sp_access_link_via_ajax:'<?php echo c_ws_plugin__s2member_utils_strings::esc_js_sq (wp_create_nonce ("ws-plugin--s2member-sp-access-link-via-ajax")); ?>',s2member_sp_access_link_ids:ids,s2member_sp_access_link_hours:hours},function(response){$link.show().html('<a href="'+esc_attr(response)+'" target="_blank" rel="external">'+esc_html(response)+"</a>"),$loading.hide()});return false}}}});