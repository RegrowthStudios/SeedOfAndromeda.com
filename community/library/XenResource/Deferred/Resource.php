<?php

class XenResource_Deferred_Resource extends XenForo_Deferred_Abstract
{
	public function execute(array $deferred, array $data, $targetRunTime, &$status)
	{
		$data = array_merge(array(
			'position' => 0,
			'batch' => 100
		), $data);
		$data['batch'] = max(1, $data['batch']);

		/* @var $resourceModel XenResource_Model_Resource */
		$resourceModel = XenForo_Model::create('XenResource_Model_Resource');

		$resourceIds = $resourceModel->getResourceIdsInRange($data['position'], $data['batch']);
		if (sizeof($resourceIds) == 0)
		{
			return true;
		}

		foreach ($resourceIds AS $resourceId)
		{
			$data['position'] = $resourceId;

			$dw = XenForo_DataWriter::create('XenResource_DataWriter_Resource', XenForo_DataWriter::ERROR_SILENT);
			if ($dw->setExistingData($resourceId))
			{
				$dw->rebuildCounters();
				$dw->save();
			}
		}

		$rbPhrase = new XenForo_Phrase('rebuilding');
		$typePhrase = new XenForo_Phrase('resources');
		$status = sprintf('%s... %s (%s)', $rbPhrase, $typePhrase, XenForo_Locale::numberFormat($data['position']));

		return $data;
	}

	public function canCancel()
	{
		return true;
	}
}