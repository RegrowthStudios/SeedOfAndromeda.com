<?php

class EWRporta_Listener_ViewPublic
{
    public static function view($class, array &$extend)
    {
		switch ($class)
		{
			case 'XenForo_ViewPublic_Forum_List':
			case 'XenForo_ViewPublic_Forum_View':
			case 'XenForo_ViewPublic_Thread_View':
			case 'EWRporta_ViewPublic_Portal':
				$extend[] = 'EWRporta_ViewPublic_Custom';
				break;
		}
    }
}