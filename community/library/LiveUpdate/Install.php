<?php

class LiveUpdate_Install
{
	public static function installer()
	{
		if (XenForo_Application::$versionId < 1020070)
		{
			throw new XenForo_Exception('This add-on requires XenForo 1.2.0 or higher.', true);
		}

		$addOnModel = XenForo_Model::create('XenForo_Model_AddOn');
		$addOn = $addOnModel->getAddOnById('AjaxPolling');

		if ($addOn)
		{
			$dw = XenForo_DataWriter::create('XenForo_DataWriter_AddOn');
			$dw->setExistingData('AjaxPolling');

			$dw->preDelete();
			$dw->delete();
		}
	}
}