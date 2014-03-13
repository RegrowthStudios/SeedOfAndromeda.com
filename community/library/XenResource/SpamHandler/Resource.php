<?php

class XenResource_SpamHandler_Resource extends XenForo_SpamHandler_Abstract
{
	/**
	 * Checks that the options array contains a non-empty 'action_threads' key
	 *
	 * @param array $user
	 * @param array $options
	 *
	 * @return boolean
	 */
	public function cleanUpConditionCheck(array $user, array $options)
	{
		return !empty($options['action_threads']);
	}

	/**
	 * @see XenForo_SpamHandler_Abstract::cleanUp()
	 */
	public function cleanUp(array $user, array &$log, &$errorKey)
	{
		$resourceModel = $this->getModelFromCache('XenResource_Model_Resource');
		$resources = $resourceModel->getResources(array(
			'user_id' => $user['user_id'],
			'moderated' => true,
			'deleted' => true
		));

		if ($resources)
		{
			$resourceIds = array_keys($resources);

			$deleteType = (XenForo_Application::get('options')->spamMessageAction == 'delete' ? 'hard' : 'soft');

			$log['resource'] = array(
				'deleteType' => $deleteType,
				'resourceIds' => $resourceIds
			);

			$inlineModModel = $this->getModelFromCache('XenResource_Model_InlineMod_Resource');
			$inlineModModel->enableLogging = false;

			$ret = $inlineModModel->deleteResources(
				$resourceIds, array('deleteType' => $deleteType, 'skipPermissions' => true), $errorKey
			);
			if (!$ret)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @see XenForo_SpamHandler_Abstract::restore()
	 */
	public function restore(array $log, &$errorKey = '')
	{
		if ($log['deleteType'] == 'soft')
		{
			$inlineModModel = $this->getModelFromCache('XenResource_Model_InlineMod_Resource');
			$inlineModModel->enableLogging = false;

			return $inlineModModel->undeleteResources(
				$log['resourceIds'], array('skipPermissions' => true), $errorKey
			);
		}

		return true;
	}
}