<?php

class EWRporta_Listener_Route
{
    public static function route($class, array &$extend)
    {
		switch ($class)
		{
			case 'XenForo_Route_Prefix_Threads':
				$extend[] = 'EWRporta_Route_Thread';
				break;
		}
    }
}