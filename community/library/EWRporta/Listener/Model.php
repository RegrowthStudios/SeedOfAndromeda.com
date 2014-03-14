<?php

class EWRporta_Listener_Model
{
    public static function model($class, array &$extend)
    {
		switch ($class)
		{
			case 'XenForo_Model_Attachment':
				$extend[] = 'EWRporta_Model_Attachment';
				break;
			case 'XenForo_Model_Post':
				$extend[] = 'EWRporta_Model_Post';
				break;
		}
    }
}