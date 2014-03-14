<?php

class EWRcarta_Listener_Model
{
    public static function post($class, array &$extend)
    {
        if ($class == 'XenForo_Model_Post')
        {
            $extend[] = 'EWRcarta_Model_Post';
        }
    }
}