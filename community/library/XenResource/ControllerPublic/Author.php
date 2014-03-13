<?php

class XenResource_ControllerPublic_Author extends XenForo_ControllerPublic_Abstract
{
	protected function _preDispatch($action)
	{
		if (!$this->_getResourceModel()->canViewResources($error))
		{
			throw $this->getErrorOrNoPermissionResponseException($error);
		}
	}

	public function actionIndex()
	{
		if ($this->_input->filterSingle('user_id', XenForo_Input::UINT))
		{
			return $this->responseReroute(__CLASS__, 'view');
		}

		$resourceModel = $this->_getResourceModel();

		$authors = $resourceModel->getMostActiveAuthors(20);
		if (!$authors)
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
				XenForo_Link::buildPublicLink('resources')
			);
		}

		$viewParams = array(
			'authors' => $authors
		);
		return $this->responseView('XenResource_ViewPublic_Author_List', 'resource_author_list', $viewParams);
	}

	public function actionView()
	{
		$userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);

		if (!$user = $this->_getUserModel()->getUserById($userId))
		{
			return $this->responseError(new XenForo_Phrase('requested_user_not_found'));
		}

		$resourceModel = $this->_getResourceModel();

		$conditions = array('user_id' => $userId);
		$conditions += $this->_getCategoryModel()->getPermissionBasedFetchConditions();

		$categories = $this->_getCategoryModel()->getViewableCategories();
		$conditions['resource_category_id'] = array_keys($categories);

		$aggregate = $resourceModel->getAggregateResourceData($conditions);
		if (!$aggregate['total_resources'])
		{
			return $this->responseError(new XenForo_Phrase('requested_user_has_no_resources'));
		}

		$page = $this->_input->filterSingle('page', XenForo_Input::UINT);
		$perPage = XenForo_Application::get('options')->resourcesPerPage;

		$visitor = XenForo_Visitor::getInstance();

		$resources = $resourceModel->getResources(
			$conditions,
			array(
				'join' => XenResource_Model_Resource::FETCH_CATEGORY |
					XenResource_Model_Resource::FETCH_VERSION |
					XenResource_Model_Resource::FETCH_USER,
				'permissionCombinationId' => $visitor['permission_combination_id'],
				'order' => 'last_update',
				'direction' => 'desc',
				'page' => $page,
				'perPage' => $perPage
			)
		);

		$this->_getCategoryModel()->bulkSetCategoryPermCache(
			$visitor['permission_combination_id'], $resources, 'category_permission_cache'
		);

		foreach ($resources AS $key => $resource)
		{
			if (!$resourceModel->canViewResourceAndContainer($resource, $resource))
			{
				unset($resources[$key]);
			}
		}

		$resources = $resourceModel->prepareResources($resources);
		$inlineModOptions = $resourceModel->getInlineModOptionsForResources($resources);

		// get average author rating
		$ratingSum = 0;
		$ratingCount = 0;
		foreach ($resources AS $resource)
		{
			$ratingSum += $resource['rating_sum'];
			$ratingCount += $resource['rating_count'];
		}

		$viewParams = array(
			'resources' => $resources,
			'inlineModOptions' => $inlineModOptions,

			'page' => $page,
			'perPage' => $perPage,

			'user' => $user,
			'aggregate' => $aggregate,

			'ratingAvg' => $resourceModel->getRatingAverage(
				$aggregate['rating_sum'], $aggregate['rating_count']
			),

			'fromProfile' => $this->_input->filterSingle('profile', XenForo_Input::UINT)
		);

		return $this->responseView('XenResource_ViewPublic_Author_View', 'resource_author_view', $viewParams);
	}

	public static function getSessionActivityDetailsForList(array $activities)
	{
		return new XenForo_Phrase('viewing_resource_author');
	}

	/**
	 * @return XenResource_Model_Resource
	 */
	protected function _getResourceModel()
	{
		return $this->getModelFromCache('XenResource_Model_Resource');
	}

	/**
	 * @return XenResource_Model_Category
	 */
	protected function _getCategoryModel()
	{
		return $this->getModelFromCache('XenResource_Model_Category');
	}

	/**
	 * @return XenForo_Model_User
	 */
	protected function _getUserModel()
	{
		return $this->getModelFromCache('XenForo_Model_User');
	}
}