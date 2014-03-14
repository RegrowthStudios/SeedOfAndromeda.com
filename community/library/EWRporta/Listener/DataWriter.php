<?php

class EWRporta_Listener_DataWriter
{
    public static function datawriter($class, array &$extend)
    {
		switch ($class)
		{
			case 'XenForo_DataWriter_DiscussionMessage_ProfilePost':
				$extend[] = 'EWRporta_DataWriter_DiscussionMessage_ProfilePost';
				break;
			case 'XenForo_DataWriter_Discussion_Thread':
				$extend[] = 'EWRporta_DataWriter_Discussion_Thread';
				break;
		}
    }
}