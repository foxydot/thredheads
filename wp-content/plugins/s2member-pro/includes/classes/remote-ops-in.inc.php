<?php
/**
* s2Member Pro Remote Operations API (inner processing routines).
*
* Copyright: © 2009-2011
* {@link http://www.websharks-inc.com/ WebSharks, Inc.}
* (coded in the USA)
*
* This WordPress® plugin (s2Member Pro) is comprised of two parts:
*
* o (1) Its PHP code is licensed under the GPL license, as is WordPress®.
* 	You should have received a copy of the GNU General Public License,
* 	along with this software. In the main directory, see: /licensing/
* 	If not, see: {@link http://www.gnu.org/licenses/}.
*
* o (2) All other parts of (s2Member Pro); including, but not limited to:
* 	the CSS code, some JavaScript code, images, and design;
* 	are licensed according to the license purchased.
* 	See: {@link http://www.s2member.com/prices/}
*
* Unless you have our prior written consent, you must NOT directly or indirectly license,
* sub-license, sell, resell, or provide for free; part (2) of the s2Member Pro Module;
* or make an offer to do any of these things. All of these things are strictly
* prohibited with part (2) of the s2Member Pro Module.
*
* Your purchase of s2Member Pro includes free lifetime upgrades via s2Member.com
* (i.e. new features, bug fixes, updates, improvements); along with full access
* to our video tutorial library: {@link http://www.s2member.com/videos/}
*
* @package s2Member\API_Remote_Ops
* @since 110713
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");

if (!class_exists ("c_ws_plugin__s2member_pro_remote_ops_in"))
	{
		/**
		* s2Member Pro Remote Operations API (inner processing routines).
		*
		* @package s2Member\API_Remote_Ops
		* @since 110713
		*/
		class c_ws_plugin__s2member_pro_remote_ops_in
			{
				/**
				* Creates a new User.
				*
				* @package s2Member\API_Remote_Ops
				* @since 110713
				*
				* @param array An input array of Remote Operation parameters.
				* @return str Returns a serialized array with an `ID` element object on success,
				* 	else returns a string beginning with `Error:` on failure; which will include details regarding the error.
				*/
				public static function create_user ($op = NULL)
					{
						if (!empty ($op["op"]) && $op["op"] === "create_user" && !empty ($op["data"]) && is_array ($op["data"]))
							{
								if(!empty($op["data"]["modify_if_login_exists"]))
									if(!empty($op["data"]["user_login"]) && ($_user = new WP_User((string)$op["data"]["user_login"])) && !empty($_user->ID))
										return c_ws_plugin__s2member_pro_remote_ops_in::modify_user(array_merge($op, array("op" => "modify_user")));

								$GLOBALS["ws_plugin__s2member_registration_vars"] = array ();
								$v = &$GLOBALS["ws_plugin__s2member_registration_vars"];

								$v["ws_plugin__s2member_custom_reg_field_user_login"] = (string)@$op["data"]["user_login"];
								$v["ws_plugin__s2member_custom_reg_field_user_email"] = (string)@$op["data"]["user_email"];

								if (empty ($op["data"]["user_pass"]) || !is_string ($op["data"]["user_pass"]))
									$op["data"]["user_pass"] = /* Auto-generate. */ wp_generate_password ();
								$GLOBALS["ws_plugin__s2member_generate_password_return"] = $op["data"]["user_pass"];

								$v["ws_plugin__s2member_custom_reg_field_first_name"] = (string)@$op["data"]["first_name"];
								$v["ws_plugin__s2member_custom_reg_field_last_name"] = (string)@$op["data"]["last_name"];

								$v["ws_plugin__s2member_custom_reg_field_s2member_level"] = (string)@$op["data"]["s2member_level"];
								$v["ws_plugin__s2member_custom_reg_field_s2member_ccaps"] = (string)@$op["data"]["s2member_ccaps"];

								$v["ws_plugin__s2member_custom_reg_field_s2member_registration_ip"] = (string)@$op["data"]["s2member_registration_ip"];

								$v["ws_plugin__s2member_custom_reg_field_s2member_subscr_gateway"] = (string)@$op["data"]["s2member_subscr_gateway"];
								$v["ws_plugin__s2member_custom_reg_field_s2member_subscr_id"] = (string)@$op["data"]["s2member_subscr_id"];
								$v["ws_plugin__s2member_custom_reg_field_s2member_custom"] = (string)@$op["data"]["s2member_custom"];

								$v["ws_plugin__s2member_custom_reg_field_s2member_auto_eot_time"] = (string)@$op["data"]["s2member_auto_eot_time"];

								$v["ws_plugin__s2member_custom_reg_field_s2member_notes"] = (string)@$op["data"]["s2member_notes"];

								$v["ws_plugin__s2member_custom_reg_field_opt_in"] = (string)@$op["data"]["opt_in"];

								if ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_fields"])
									foreach (json_decode ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_fields"], true) as $field)
										{
											$field_var = preg_replace ("/[^a-z0-9]/i", "_", strtolower ($field["id"]));
											$field_id_class = preg_replace ("/_/", "-", $field_var);

											if (isset($op["data"]["custom_fields"][$field_var]))
												$v["ws_plugin__s2member_custom_reg_field_" . $field_var] = $op["data"]["custom_fields"][$field_var];
										}
								$create = array ("user_login" => (string)@$op["data"]["user_login"], "user_pass" => (string)@$op["data"]["user_pass"], "user_email" => (string)@$op["data"]["user_email"]);

								if (((is_multisite () && ($new = $user_id = c_ws_plugin__s2member_registrations::ms_create_existing_user ($create["user_login"], $create["user_email"], $create["user_pass"]))) || ($new = $user_id = wp_create_user ($create["user_login"], $create["user_pass"], $create["user_email"]))) && !is_wp_error ($new))
									{
										if (is_object ($user = new WP_User ($user_id)) && !empty ($user->ID) && ($user_id = $user->ID))
											{
												if (!empty ($op["data"]["notification"]))
													wp_new_user_notification ($user_id, $op["data"]["user_pass"]);

												return serialize (array ("ID" => $user_id));
											}
										return "Error: Creation may have failed. Unable to obtain WP_User ID.";
									}
								else if (is_wp_error ($new) && $new->get_error_code ())
									return "Error: " . $new->get_error_message ();

								return "Error: User creation failed for an unknown reason. Please try again.";
							}
						return "Error: Empty or invalid request ( `create_user` ). Please try again.";
					}
				/**
				* Modifies an existing User.
				*
				* @package s2Member\API_Remote_Ops
				* @since 110713
				*
				* @param array An input array of Remote Operation parameters.
				* @return str Returns a serialized array with an `ID` element object on success,
				* 	else returns a string beginning with `Error:` on failure; which will include details regarding the error.
				*/
				public static function modify_user ($op = NULL)
					{
						if (!empty ($op["op"]) && $op["op"] === "modify_user" && !empty ($op["data"]) && is_array ($op["data"]))
							{
								if(!empty($op["data"]["user_id"]) && ($_user = new WP_User((integer)$op["data"]["user_id"])) && !empty($_user->ID))
									$user = $_user;

								else if(!empty($op["data"]["user_login"]) && ($_user = new WP_User((string)$op["data"]["user_login"])) && !empty($_user->ID))
									$user = $_user;

								else return "Error: Modification failed. Unable to obtain WP_User object instance with data supplied (i.e. ID/Username not found).";

								if (is_multisite () && !is_user_member_of_blog ($user->ID))
									return "Error: Modification failed. Unable to obtain WP_User object instance with data supplied (i.e. ID/Username not a part of this Blog).";

								if(is_super_admin($user->ID) || $user->has_cap("administrator"))
									return "Error: Modification failed. This API will not modify Administrators.";

								$userdata["ID"] = /* Needed for database update. */ $user->ID;

								if (!empty ($op["data"]["user_email"]))
									if (is_email ((string)$op["data"]["user_email"]) && !email_exists ((string)$op["data"]["user_email"]))
											$userdata["user_email"] = (string)$op["data"]["user_email"];

								if (!empty ($op["data"]["user_pass"]))
									if /* No pass change on demo! */ ($user->user_login !== "demo")
										$userdata["user_pass"] = (string)$op["data"]["user_pass"];

								if (!empty ($op["data"]["first_name"]))
									$userdata["first_name"] = (string)$op["data"]["first_name"];

								if (!empty ($op["data"]["display_name"]))
									$userdata["display_name"] = (string)$op["data"]["display_name"];

								if (!empty ($op["data"]["last_name"]))
									$userdata["last_name"] = (string)$op["data"]["last_name"];

								if (isset ($op["data"]["s2member_level"]) && (integer)$op["data"]["s2member_level"] === 0)
								{
									if /* Not the same? */ (c_ws_plugin__s2member_user_access::user_access_role ($user) !== get_option("default_role"))
										$userdata["role"] = get_option ("default_role");
								}
								else if (!empty ($op["data"]["s2member_level"]) && (integer)$op["data"]["s2member_level"] > 0)
								{
									if /* Not the same? */ (c_ws_plugin__s2member_user_access::user_access_role ($user) !== "s2member_level".(integer)$op["data"]["s2member_level"])
										$userdata["role"] = "s2member_level".(integer)$op["data"]["s2member_level"];
								}
								wp_update_user /* OK. Now send this array for an update. */($userdata);

								$old_user = /* Copy existing User obj. */ unserialize(serialize($user));
								$user = /* Update our object instance. */ new WP_User($user->ID);

								$role = c_ws_plugin__s2member_user_access::user_access_role ($user);
								$level = c_ws_plugin__s2member_user_access::user_access_role_to_level($role);

								if(!empty($op["data"]["auto_opt_out_transition"]))
									$_p["ws_plugin__s2member_custom_reg_auto_opt_out_transitions"] = TRUE;

								if /* In this case, we need to fire Hook: `ws_plugin__s2member_during_collective_mods`. */(!empty($userdata["role"]))
									do_action("ws_plugin__s2member_during_collective_mods", $user->ID, get_defined_vars(), "user-role-change", "modification", $role, $user, $old_user);

								if (!empty($op["data"]["s2member_ccaps"]) && preg_match ("/^-all/", str_replace ("+", "", (string)$op["data"]["s2member_ccaps"])))
									foreach ($user->allcaps as $cap => $cap_enabled)
										if (preg_match ("/^access_s2member_ccap_/", $cap))
											$user->remove_cap ($ccap = $cap);

								if (!empty($op["data"]["s2member_ccaps"]) && preg_replace ("/^-all[\r\n\t\s;,]*/", "", str_replace ("+", "", (string)$op["data"]["s2member_ccaps"])))
									foreach (preg_split ("/[\r\n\t\s;,]+/", preg_replace ("/^-all[\r\n\t\s;,]*/", "", str_replace ("+", "", (string)$op["data"]["s2member_ccaps"]))) as $ccap)
										if (strlen ($ccap = trim (strtolower (preg_replace ("/[^a-z_0-9]/i", "", $ccap)))))
											$user->add_cap ("access_s2member_ccap_" . $ccap);

								if(isset($op["data"]["s2member_originating_blog"]) && is_multisite())
									update_user_meta($user->ID, "s2member_originating_blog", (integer)$op["data"]["s2member_originating_blog"]);

								if(isset($op["data"]["s2member_subscr_gateway"]))
									update_user_option($user->ID, "s2member_subscr_gateway", (string)$op["data"]["s2member_subscr_gateway"]);

								if(isset($op["data"]["s2member_subscr_id"]))
									update_user_option($user->ID, "s2member_subscr_id", (string)$op["data"]["s2member_subscr_id"]);

								if(isset($op["data"]["s2member_custom"]))
									update_user_option($user->ID, "s2member_custom", (string)$op["data"]["s2member_custom"]);

								if(isset($op["data"]["s2member_registration_ip"]))
									update_user_option($user->ID, "s2member_registration_ip", (string)$op["data"]["s2member_registration_ip"]);

								if(isset($op["data"]["s2member_notes"]))
									update_user_option($user->ID, "s2member_notes", trim(get_user_option("s2member_notes", $user->ID)."\n\n".(string)$op["data"]["s2member_notes"]));

								if(isset($op["data"]["s2member_auto_eot_time"]))
									update_user_option($user->ID, "s2member_auto_eot_time", ((!empty($op["data"]["s2member_auto_eot_time"])) ? strtotime((string)$op["data"]["s2member_auto_eot_time"]) : ""));

								if /* Custom Registration/Profile Fields configured? */ ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_fields"])
									{
										$_existing_fields = get_user_option("s2member_custom_fields", $user->ID);

										foreach(json_decode($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_fields"], true) as $field)
										{
											$field_var = preg_replace("/[^a-z0-9]/i", "_", strtolower($field["id"]));
											$field_id_class = preg_replace("/_/", "-", $field_var);

											if(!isset($op["data"]["custom_fields"][$field_var]))
											{
												if(isset($_existing_fields[$field_var]) && ((is_array($_existing_fields[$field_var]) && !empty($_existing_fields[$field_var])) || (is_string($_existing_fields[$field_var]) && strlen($_existing_fields[$field_var]))))
													$fields[$field_var] = $_existing_fields[$field_var];
												else unset($fields[$field_var]);
											}
											else // Default case handler.
											{
												if((is_array($op["data"]["custom_fields"][$field_var]) && !empty($op["data"]["custom_fields"][$field_var])) || (is_string($op["data"]["custom_fields"][$field_var]) && strlen($op["data"]["custom_fields"][$field_var])))
													$fields[$field_var] = $op["data"]["custom_fields"][$field_var];
												else unset($fields[$field_var]);
											}
										}
										if(!empty($fields))
											update_user_option($user->ID, "s2member_custom_fields", $fields);
										else delete_user_option($user->ID, "s2member_custom_fields");
									}
								if /* We ONLY process this if they are higher than Level #0. */($level > 0)
								{
									$pr_times = get_user_option("s2member_paid_registration_times", $user->ID);
									$pr_times["level"] = (empty($pr_times["level"])) ? time() : $pr_times["level"];
									$pr_times["level".$level] = (empty($pr_times["level".$level])) ? time() : $pr_times["level".$level];
									update_user_option /* Update now. */($user->ID, "s2member_paid_registration_times", $pr_times);
								}
								if /* Should we process List Servers? */(!empty($op["data"]["opt_in"]) && !empty($role) && $level >= 0)
									c_ws_plugin__s2member_list_servers::process_list_servers($role, $level, $user->user_login, ((!empty($op["data"]["user_pass"])) ? (string)$op["data"]["user_pass"] : ""), $user->user_email, $user->first_name, $user->last_name, false, true, true, $user->ID);

								if /* Delete/reset IP Restrictions? */(!empty($op["data"]["reset_ip_restrictions"]))
									c_ws_plugin__s2member_ip_restrictions::delete_reset_specific_ip_restrictions(strtolower($user->user_login));

								if /* Delete/reset File Downloads log? */ (!empty($op["data"]["reset_file_download_access_log"]))
									delete_user_option ($user->ID, "s2member_file_download_access_log");

								return serialize (array ("ID" => $user->ID));
							}
						return "Error: Empty or invalid request ( `modify_user` ). Please try again.";
					}
				/**
				* Deletes an existing User.
				*
				* @package s2Member\API_Remote_Ops
				* @since 110713
				*
				* @param array An input array of Remote Operation parameters.
				* @return str Returns a serialized array with an `ID` element object on success,
				* 	else returns a string beginning with `Error:` on failure; which will include details regarding the error.
				*/
				public static function delete_user ($op = NULL)
					{
						if (!empty ($op["op"]) && $op["op"] === "delete_user" && !empty ($op["data"]) && is_array ($op["data"]))
							{
								if(!empty($op["data"]["user_id"]) && ($_user = new WP_User((integer)$op["data"]["user_id"])) && !empty($_user->ID))
									$user = $_user;

								else if(!empty($op["data"]["user_login"]) && ($_user = new WP_User((string)$op["data"]["user_login"])) && !empty($_user->ID))
									$user = $_user;

								else return "Error: Deletion failed. Unable to obtain WP_User object instance.";

								if(is_super_admin($user->ID) || $user->has_cap("administrator"))
									return "Error: Deletion failed. This API will not delete Administrators.";

								include_once ABSPATH . "wp-admin/includes/admin.php";
								wp_delete_user($user->ID);

								return serialize (array ("ID" => $user->ID));
							}
						return "Error: Empty or invalid request (`delete_user`). Please try again.";
					}
			}
	}
?>