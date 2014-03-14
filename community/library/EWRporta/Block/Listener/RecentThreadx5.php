<?php

class EWRporta_Block_Listener_RecentThreadx5
{
    public static function controller($class, array &$extend)
    {
		switch ($class)
		{
			case 'EWRporta_ControllerPublic_Portal':
				$extend[] = 'EWRporta_Block_Controller_RecentThreadx5';
				break;
		}
    }
}