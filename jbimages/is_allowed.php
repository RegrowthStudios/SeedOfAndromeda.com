<?php
/**
 * Justboil.me - a TinyMCE image upload plugin
 * jbimages/config.php
 *
 * Released under Creative Commons Attribution 3.0 Unported License
 *
 * License: http://creativecommons.org/licenses/by/3.0/
 * Plugin info: http://justboil.me/
 * Author: Viktor Kuzhelnyi
 *
 * Version: 2.3 released 23/06/2013
 */

/*
 * ------------------------------------------------------------------- | | IMPORTANT NOTE! In case, when TinyMCE�s folder is not protected with HTTP Authorisation, | you should require is_allowed() function to return | `TRUE` if user is authorised, | `FALSE` - otherwise | | This is intended to protect upload script, if someone guesses it's url. | -------------------------------------------------------------------
 */
function is_allowed() {
	require_once (__DIR__ . '/../community/XenForoSDK.php');
	$sdk = new XenForoSDK ();
	$loggedIn = $sdk->isLoggedIn ();
	$userinfo = $sdk->getUser ();
	if (! $loggedIn) {
		return false;
	}
	$groups = explode ( ",", $userinfo ["secondary_group_ids"] );
	$groups [] = $userinfo ["user_group_id"];
	
	$uploadallowed = array (
			7,
			13,
			3 
	); // 7 = Dev member, 13 = Dev leader, 3 = Admins
	
	foreach ( $uploadallowed as $groupid ) {
		if (in_array ( $groupid, $groups )) {
			return TRUE;
		}
	}
	return false;
}

?>