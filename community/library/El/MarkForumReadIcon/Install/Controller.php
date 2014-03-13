<?php

class El_MarkForumReadIcon_Install_Controller
{

    CONST ADDON_ID = 'El_MarkForumReadIcon';
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $db;

    protected $tables = array();


    public static function install($existingAddOn, $addOnData, SimpleXMLElement $xml)
    {
        $installClass = new self();
        $installClass->checkXFVersion(1020070);
    }


    protected $XfVersionIds = array(
        1010570 => '1.1.5',
        1020070 => '1.2.0',
    );

    protected function checkXFVersion($versionId)
    {
        if (XenForo_Application::$versionId < $versionId) {
            throw new XenForo_Exception('This add-on requires XenForo ' . $this->XfVersionIds[$versionId] . ' or higher.', true);
        }
    }
}