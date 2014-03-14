<?php

class EWRcarta_Listener_Init
{
	public static function listen(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
		XenForo_DataWriter_User::$usernameChangeUpdates['permanent']['ewrcarta_history_username'] = array('EWRcarta_history', 'username', 'user_id');
	}
}