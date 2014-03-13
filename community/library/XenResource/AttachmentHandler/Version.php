<?php

class XenResource_AttachmentHandler_Version extends XenForo_AttachmentHandler_Abstract
{
	protected $_resourceModel = null;
	protected $_versionModel = null;

	/**
	 * Key of primary content in content data array.
	 *
	 * @var string
	 */
	protected $_contentIdKey = 'resource_version_id';

	/**
	 * Route to get to a resource
	 *
	 * @var string
	 */
	protected $_contentRoute = 'resources/history';

	/**
	 * Name of the phrase that describes the content type
	 *
	 * @var string
	 */
	protected $_contentTypePhraseKey = 'resource_version';

	/**
	 * Determines if attachments and be uploaded and managed in this context.
	 *
	 * @see XenForo_AttachmentHandler_Abstract::_canUploadAndManageAttachments()
	 */
	protected function _canUploadAndManageAttachments(array $contentData, array $viewingUser)
	{
		$resourceModel = $this->_getResourceModel();

		/** @var XenResource_Model_Category $categoryModel */
		$categoryModel = XenForo_Model::create('XenResource_Model_Category');

		if (!empty($contentData['resource_id']))
		{
			$resource = $resourceModel->getResourceById($contentData['resource_id']);
			if ($resource)
			{
				$category = $categoryModel->getCategoryById($resource['resource_category_id'], array(
					'permissionCombinationId' => $viewingUser['permission_combination_id']
				));
				if ($category)
				{
					$categoryPermissions = XenForo_Permission::unserializePermissions($category['category_permission_cache']);

					return XenForo_Model::create('XenResource_Model_Version')->canAddVersion(
						$resource, $category, $null, $viewingUser, $categoryPermissions
					);
				}
				else
				{
					return false;
				}
			}
		}

		return $categoryModel->canAddResource(null, $null, $viewingUser);
	}

	/**
	 * Determines if the specified attachment can be viewed.
	 *
	 * @see XenForo_AttachmentHandler_Abstract::_canViewAttachment()
	 */
	protected function _canViewAttachment(array $attachment, array $viewingUser)
	{
		$resourceModel = $this->_getResourceModel();
		$versionModel = $this->_getVersionModel();

		$version = $versionModel->getVersionById($attachment['content_id']);
		if (!$version)
		{
			return false;
		}

		$resource = $resourceModel->getResourceById($version['resource_id']);
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

		return $versionModel->canDownloadVersion($version, $resource, $category, $null, $viewingUser);
	}

	public function getAttachmentConstraints()
	{
		return XenForo_Model::create('XenResource_Model_Version')->getVersionFileConstraints();
	}

	public function attachmentPostDelete(array $attachment, Zend_Db_Adapter_Abstract $db) {}

	public function getContentDataFromContentId($contentId)
	{
		$version = XenForo_Model::create('XenResource_Model_Version')->getVersionById($contentId, array(
				'join' => XenResource_Model_Version::FETCH_RESOURCE
		));
		return ($version ? $version : parent::getContentDataFromContentId($contentId));
	}

	/**
	 * @return XenResource_Model_Resource
	 */
	protected function _getResourceModel()
	{
		if (!$this->_resourceModel)
		{
			$this->_resourceModel = XenForo_Model::create('XenResource_Model_Resource');
		}

		return $this->_resourceModel;
	}

	/**
	* @return XenResource_Model_Version
	*/
	protected function _getVersionModel()
	{
		if (!$this->_versionModel)
		{
			$this->_versionModel = XenForo_Model::create('XenResource_Model_Version');
		}

		return $this->_versionModel;
	}
}