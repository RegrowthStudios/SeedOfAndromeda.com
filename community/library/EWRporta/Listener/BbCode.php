<?php

class EWRporta_Listener_BbCode
{
    public static function formatter($class, array &$extend)
    {
        if ($class == 'XenForo_BbCode_Formatter_Base')
        {
            $extend[] = 'EWRporta_BbCode_Formatter';
        }
    }
}