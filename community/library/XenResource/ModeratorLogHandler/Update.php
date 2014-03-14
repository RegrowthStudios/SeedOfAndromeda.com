<?php

class XenResource_ModeratorLogHandler_Update extends XenForo_ModeratorLogHandler_Abstract
{
	protected function _log(array $logUser, array $content, $action, array $actionParams = array(), $parentContent = null)
	{
		if (!$parentContent)
		{
			$parentContent = XenForo_Model::create('XenResource_Model_Resource')->getResourceById($content['resource_id']);
		}
		if (!$parentContent)
		{
			return false;
		}

		$dw = XenForo_DataWriter::create('XenForo_DataWriter_ModeratorLog');
		$dw->bulkSet(array(
			'user_id' => $logUser['user_id'],
			'content_type' => 'resource_update',
			'content_id' => $content['resource_update_id'],
			'content_user_id' => $parentContent['user_id'],
			'content_username' => $parentContent['username'],
			'content_title' => $content['title'],
			'content_url' => XenForo_Link::buildPublicLink('resources', $parentContent, array('resource_update_id' => $content['resource_update_id'])),
			'discussion_content_type' => 'resource',
			'discussion_content_id' => $content['resource_id'],
			'action' => $action,
			'action_params' => $actionParams
		));
		$dw->save();

		return $dw->get('moderator_log_id');
	}

	protected function _prepareEntry(array $entry)
	{
		$elements = json_decode($entry['action_params'], true);

		if ($entry['action'] == 'edit')
		{
			$entry['actionText'] = new XenForo_Phrase(
				'moderator_log_resource_update_edit',
				array('elements' => implode(', ', array_keys($elements)))
			);
		}

		return $entry;
	}
}