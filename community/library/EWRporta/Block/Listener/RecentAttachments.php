<?php

class EWRporta_Block_Listener_RecentAttachments
{
    public static function controller($class, array &$extend)
    {
		switch ($class)
		{
			case 'EWRporta_ControllerPublic_Portal':
				$extend[] = 'EWRporta_Block_Controller_RecentAttachments';
				break;
		}
    }
}