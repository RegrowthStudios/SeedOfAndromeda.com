<?php

class Andy_ForumModerators_Listener
{
	public static function Forum($class, array &$extend)
	{
		$extend[] = 'Andy_ForumModerators_ControllerPublic_Forum';
	}	
}

?>