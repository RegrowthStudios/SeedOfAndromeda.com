<?php

class XenResource_ControllerHelper_Resource extends XenForo_ControllerHelper_Abstract
{
	/**
	 * The current browsing user.
	 *
	 * @var XenForo_Visitor
	 */
	protected $_visitor;

	/**
	 * Additional constructor setup behavior.
	 */
	protected function _constructSetup()
	{
		$this->_visitor = XenForo_Visitor::getInstance();
	}

	/**
	 * Checks that a category is valid and viewable, before returning the category's info.
	 *
	 * @param integer|null $id Category ID
	 * @param array $fetchOptions Extra data to fetch with the category
	 *
	 * @return array Forum info
	 */
	public function assertCategoryValidAndViewable($id = null, array $fetchOptions = array())
	{
		$fetchOptions += array('permissionCombinationId' => $this->_visitor['permission_combination_id']);

		/** @var XenResource_Model_Category $categoryModel */
		$categoryModel = $this->_controller->getModelFromCache('XenResource_Model_Category');

		$category = $this->getCategoryOrError($id, $fetchOptions);
		if (isset($category['category_permission_cache']))
		{
			$categoryModel->setCategoryPermCache(
				$this->_visitor['permission_combination_id'], $category['resource_category_id'],
				$category['category_permission_cache']
			);
			unset($category['category_permission_cache']);
		}

		if (!$categoryModel->canViewCategory($category, $errorPhraseKey))
		{
			throw $this->_controller->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		$category = $categoryModel->prepareCategory($category);

		return $category;
	}

	/**
	 * Checks that a resource is valid and viewable, before returning the resource
	 * and containing category's info.
	 *
	 * @param integer|null $resourceId
	 * @param array $resourceFetchOptions Extra data to fetch with the resource
	 * @param array $categoryFetchOptions Extra data to fetch with the category
	 *
	 * @return array Format: [0] => resource info, [1] => category info
	 */
	public function assertResourceValidAndViewable($resourceId = null,
		array $resourceFetchOptions = array(), array $categoryFetchOptions = array()
	)
	{
		if (!isset($resourceFetchOptions['join']))
		{
			$resourceFetchOptions['join'] = 0;
		}

		$resourceFetchOptions['join'] |=
			XenResource_Model_Resource::FETCH_VERSION
			| XenResource_Model_Resource::FETCH_USER
			| XenResource_Model_Resource::FETCH_FEATURED;

		$resource = $this->getResourceOrError($resourceId, $resourceFetchOptions);
		$category = $this->assertCategoryValidAndViewable($resource['resource_category_id'], $categoryFetchOptions);

		/** @var XenResource_Model_Resource $resourceModel */
		$resourceModel = $this->_controller->getModelFromCache('XenResource_Model_Resource');

		if (!$resourceModel->canViewResource($resource, $category, $errorPhraseKey))
		{
			throw $this->_controller->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		$resource = $resourceModel->prepareResource($resource, $category);
		$resource = $resourceModel->prepareResourceCustomFields($resource, $category);

		return array($resource, $category);
	}

	/**
	 * Checks that a update is valid and viewable, before returning the update, resource,
	 * and containing category's info.
	 *
	 * @param integer|null $updateId
	 * @param array $updateFetchOptions Extra data to fetch with the update
	 * @param array $resourceFetchOptions Extra data to fetch with the resource
	 * @param array $categoryFetchOptions Extra data to fetch with the category
	 *
	 * @return array Format: [0] => update info, [1] => resource info, [2] => category info
	 */
	public function assertUpdateValidAndViewable($updateId = null, array $updateFetchOptions = array(),
		array $resourceFetchOptions = array(), array $categoryFetchOptions = array()
	)
	{
		$update = $this->getUpdateOrError($updateId, $updateFetchOptions);
		list($resource, $category) = $this->assertResourceValidAndViewable(
			$update['resource_id'], $resourceFetchOptions, $categoryFetchOptions
		);

		/** @var XenResource_Model_Update $updateModel */
		$updateModel = $this->_controller->getModelFromCache('XenResource_Model_Update');

		if (!$updateModel->canViewUpdate($update, $resource, $category, $errorPhraseKey))
		{
			throw $this->_controller->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		$update = $updateModel->prepareUpdate($update, $resource, $category);

		return array($update, $resource, $category);
	}

	/**
	 * Checks that a version is valid and viewable, before returning the version, resource,
	 * and containing category's info.
	 *
	 * @param integer|null $versionId
	 * @param array $versionFetchOptions Extra data to fetch with the version
	 * @param array $resourceFetchOptions Extra data to fetch with the resource
	 * @param array $categoryFetchOptions Extra data to fetch with the category
	 *
	 * @return array Format: [0] => update info, [1] => resource info, [2] => category info
	 */
	public function assertVersionValidAndViewable($versionId = null, array $versionFetchOptions = array(),
		array $resourceFetchOptions = array(), array $categoryFetchOptions = array()
	)
	{
		$version = $this->getVersionOrError($versionId, $versionFetchOptions);
		list($resource, $category) = $this->assertResourceValidAndViewable(
			$version['resource_id'], $resourceFetchOptions, $categoryFetchOptions
		);

		/** @var XenResource_Model_Version $versionModel */
		$versionModel = $this->_controller->getModelFromCache('XenResource_Model_Version');

		$version = $versionModel->prepareVersion($version, $resource, $category);

		return array($version, $resource, $category);
	}

	/**
	 * Checks that a review is valid and viewable, before returning the version, resource,
	 * and containing category's info.
	 *
	 * @param integer|null $reviewId
	 * @param array $ratingFetchOptions Extra data to fetch with the review
	 * @param array $resourceFetchOptions Extra data to fetch with the resource
	 * @param array $categoryFetchOptions Extra data to fetch with the category
	 *
	 * @return array Format: [0] => update info, [1] => resource info, [2] => category info
	 */
	public function assertReviewValidAndViewable($reviewId = null, array $ratingFetchOptions = array(),
		array $resourceFetchOptions = array(), array $categoryFetchOptions = array()
	)
	{
		if (!isset($ratingFetchOptions['join']))
		{
			$ratingFetchOptions['join'] = 0;
		}
		$ratingFetchOptions['join'] |= XenResource_Model_Rating::FETCH_USER;

		$rating = $this->getReviewOrError($reviewId, $ratingFetchOptions);
		list($resource, $category) = $this->assertResourceValidAndViewable(
			$rating['resource_id'], $resourceFetchOptions, $categoryFetchOptions
		);

		/** @var XenResource_Model_Rating $ratingModel */
		$ratingModel = $this->_controller->getModelFromCache('XenResource_Model_Rating');

		if (!$ratingModel->canViewRating($rating, $resource, $category, $errorPhraseKey))
		{
			throw $this->_controller->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		$rating = $ratingModel->prepareRating($rating, $resource, $category);

		return array($rating, $resource, $category);
	}

	/**
	* Gets the specified review or throws an error.
	*
	* @param integer|null $versionId
	* @param array $fetchOptions Options that control the data fetched with the version
	*
	* @return array
	*/
	public function getReviewOrError($ratingId = null, array $fetchOptions = array())
	{
		if ($ratingId === null)
		{
			$ratingId = $this->_controller->getInput()->filterSingle('resource_rating_id', XenForo_Input::UINT);
		}

		$review = $this->_controller->getModelFromCache('XenResource_Model_Rating')->getRatingById($ratingId, $fetchOptions);
		if (!$review || !$review['is_review'])
		{
			throw $this->_controller->responseException(
				$this->_controller->responseError(new XenForo_Phrase('requested_review_not_found'), 404)
			);
		}

		return $review;
	}

	/**
	 * Gets the specified version or throws an error.
	 *
	 * @param integer|null $versionId
	 * @param array $fetchOptions Options that control the data fetched with the version
	 *
	 * @return array
	 */
	public function getVersionOrError($versionId = null, array $fetchOptions = array())
	{
		if ($versionId === null)
		{
			$versionId = $this->_controller->getInput()->filterSingle('resource_version_id', XenForo_Input::UINT);
		}

		$version = $this->_controller->getModelFromCache('XenResource_Model_Version')->getVersionById($versionId, $fetchOptions);
		if (!$version)
		{
			throw $this->_controller->responseException(
				$this->_controller->responseError(new XenForo_Phrase('requested_version_not_found'), 404)
			);
		}

		return $version;
	}

	/**
	 * Gets the specified update or throws an error.
	 *
	 * @param integer|null $updateId
	 * @param array $fetchOptions Options that control the data fetched with the update
	 *
	 * @return array
	 */
	public function getUpdateOrError($updateId = null, array $fetchOptions = array())
	{
		if ($updateId === null)
		{
			$updateId = $this->_controller->getInput()->filterSingle('resource_update_id', XenForo_Input::UINT);
		}

		$update = $this->_controller->getModelFromCache('XenResource_Model_Update')->getUpdateById($updateId, $fetchOptions);
		if (!$update)
		{
			throw $this->_controller->responseException(
				$this->_controller->responseError(new XenForo_Phrase('requested_update_not_found'), 404)
			);
		}

		return $update;
	}

	/**
	 * Gets the specified resource or throws an error.
	 *
	 * @param integer|null $resourceId
	 * @param array $fetchOptions Options that control the data fetched with the resource
	 *
	 * @return array
	 */
	public function getResourceOrError($resourceId = null, array $fetchOptions = array())
	{
		if ($resourceId === null)
		{
			$resourceId = $this->_controller->getInput()->filterSingle('resource_id', XenForo_Input::UINT);
		}

		$resource = $this->_controller->getModelFromCache('XenResource_Model_Resource')->getResourceById($resourceId, $fetchOptions);
		if (!$resource)
		{
			throw $this->_controller->responseException(
				$this->_controller->responseError(new XenForo_Phrase('requested_resource_not_found'), 404)
			);
		}

		return $resource;
	}

	/**
	 * Gets the specified category or throws an error.
	 *
	 * @param integer|null $categoryId Category ID
	 * @param array $fetchOptions Options that control the data fetched with the category
	 *
	 * @return array
	 */
	public function getCategoryOrError($categoryId = null, array $fetchOptions = array())
	{
		if ($categoryId === null)
		{
			$categoryId = $this->_controller->getInput()->filterSingle('resource_category_id', XenForo_Input::UINT);
		}

		$category = $this->_controller->getModelFromCache('XenResource_Model_Category')->getCategoryById(
			$categoryId, $fetchOptions
		);
		if (!$category)
		{
			throw $this->_controller->responseException(
				$this->_controller->responseError(new XenForo_Phrase('requested_category_not_found'), 404)
			);
		}

		return $category;
	}

	public function getCustomFieldValues(array &$values = null, array &$shownKeys = null)
	{
		$input = $this->_controller->getInput();

		if ($values === null)
		{
			$values = $input->filterSingle('custom_fields', XenForo_Input::ARRAY_SIMPLE);
		}

		if ($shownKeys === null)
		{
			$shownKeys = $input->filterSingle('custom_fields_shown', XenForo_Input::STRING, array('array' => true));
		}

		if (!$shownKeys)
		{
			return array();
		}

		/** @var $fieldModel XenResource_Model_ResourceField */
		$fieldModel = $this->_controller->getModelFromCache('XenResource_Model_ResourceField');
		$fields = $fieldModel->getResourceFields();

		$output = array();
		foreach ($shownKeys AS $key)
		{
			if (!isset($fields[$key]))
			{
				continue;
			}

			$field = $fields[$key];

			if (isset($values[$key]))
			{
				$output[$key] = $values[$key];
			}
			else if ($field['field_type'] == 'bbcode' && isset($values[$key . '_html']))
			{
				$messageTextHtml = strval($values[$key . '_html']);

				if ($input->filterSingle('_xfRteFailed', XenForo_Input::UINT))
				{
					// actually, the RTE failed to load, so just treat this as BB code
					$output[$key] = $messageTextHtml;
				}
				else if ($messageTextHtml !== '')
				{
					$output[$key] = $this->_controller->getHelper('Editor')->convertEditorHtmlToBbCode($messageTextHtml, $input);
				}
				else
				{
					$output[$key] = '';
				}
			}
		}

		return $output;
	}
}