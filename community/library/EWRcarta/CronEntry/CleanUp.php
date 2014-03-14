<?php

class EWRcarta_CronEntry_CleanUp
{
	public static function runDailyCleanUp()
	{
		$db = XenForo_Application::getDb();
		
		$readMarkingCutOff = XenForo_Application::$time - (XenForo_Application::get('options')->readMarkingDataLifetime * 86400);
		$db->delete('EWRcarta_read', 'page_read_date < ' . $readMarkingCutOff);
	}
}