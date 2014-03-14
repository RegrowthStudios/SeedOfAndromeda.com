<?php

class Tinhte_MoreXenForoPermissions_Listener
{
    public static function load_class($class, array &$extend)
    {
        switch($class)
        {
            case 'XenForo_ControllerPublic_Online':
                $extend[] = 'Tinhte_MoreXenForoPermissions_ControllerPublic_Online';
                break;
            case 'XenForo_ControllerPublic_Member':
                $extend[] = 'Tinhte_MoreXenForoPermissions_ControllerPublic_Member';
                break;
            case 'XenForo_ControllerPublic_RecentActivity':
                $extend[] = 'Tinhte_MoreXenForoPermissions_ControllerPublic_RecentActivity';
                break;
        }
    }
}