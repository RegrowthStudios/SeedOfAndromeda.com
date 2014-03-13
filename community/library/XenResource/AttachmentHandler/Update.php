<?php

class XenResource_AttachmentHandler_Update extends XenForo_AttachmentHandler_Abstract
{
	protected $_contentIdKey = 'resource_update_id';

	protected $_contentTypePhraseKey = 'resource_update';

	protected $_contentRoute = 'resources/update';

	protected function _canUploadAndManageAttachments(array $contentData, array $viewingUser)
	{
		return $this->_getUpdateModel()->canUploadAndManageUpdateAttachment($null, $viewingUser);
	}

	protected function _canViewAttachment(array $attachment, array $viewingUser)
	{
		$updateModel = $this->_getUpdateModel();

		$update = $updateModel->getUpdateById($attachment['content_id']);
		if (!$update)
		{
			return false;
		}

		/** @var $resourceModel XenResource_Model_Resource */
		$resourceModel = XenForo_Model::create('XenResource_Model_Resource');
		$resource = $resourceModel->getResourceById($update['resource_id']);
		if (!$resource)
		{
			return false;
		}

		/** @var XenResource_Model_Category $categoryModel */
		$categoryModel = XenForo_Model::create('XenResource_Model_Category');
		$category = $categoryModel->getCategoryById($resource['resource_category_id'], array(
			'permissionCombinationId' => $viewingUser['permission_combination_id']
		));
		if (!$category)
		{
			return false;
		}

		$categoryModel->setCategoryPermCache(
			$viewingUser['permission_combination_id'], $resource['resource_category_id'],
			$category['category_permission_cache']
		);

		if (!$resourceModel->canViewResourceAndContainer($resource, $category, $null, $viewingUser))
		{
			return false;
		}

		return $updateModel->canViewUpdateImages($resource, $category, $null, $viewingUser);
	}

	public function attachmentPostDelete(array $attachment, Zend_Db_Adapter_Abstract $db)
	{
		$db->query('
			UPDATE xf_resource_update
			SET attach_count = IF(attach_count > 0, attach_count - 1, 0)
			WHERE resource_update_id = ?
		', $attachment['content_id']);
	}

	public function getAttachmentConstraints()
	{
		return $this->_getUpdateModel()->getUpdateAttachmentConstraints();
	}

	public function getContentDataFromContentId($contentId)
	{
		$update = XenForo_Model::create('XenResource_Model_Update')->getUpdateById($contentId, array(
			'join' => XenResource_Model_Update::FETCH_USER
		));
		return ($update ? $update : parent::getContentDataFromContentId($contentId));
	}

	public function getContentLink(array $attachment, array $extraParams = array(), $skipPrepend = false)
	{
		$update = $this->getContentDataFromContentId($attachment['content_id']);
		$extraParams['update'] = $update['resource_update_id'];
		return XenForo_Link::buildPublicLink($this->_contentRoute, $update, $extraParams, $skipPrepend);
	}

	/**
	 * @return XenResource_Model_Update
	 */
	protected function _getUpdateModel()
	{
		return XenForo_Model::create('XenResource_Model_Update');
	}
}