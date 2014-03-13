<?php

class XenResource_Listener_Template
{
	protected static $_hasTemplatePerm = null;
	protected static $_resource = false;

	public static function templateCreate($templateName, array &$params, XenForo_Template_Abstract $template)
	{
		if (self::$_hasTemplatePerm === null)
		{
			self::$_hasTemplatePerm = XenForo_Visitor::getInstance()->hasPermission('resource', 'view');
		}

		if (!isset($params['canViewResources']))
		{
			$params['canViewResources'] = self::$_hasTemplatePerm;
		}
	}

	public static function threadViewPostDispatch($controller, $response, $controllerName, $action)
	{
		if (!($response instanceof XenForo_ControllerResponse_View))
		{
			return;
		}

		if ($response->viewName != 'XenForo_ViewPublic_Thread_View' || empty($response->params['thread']))
		{
			return;
		}

		$thread = $response->params['thread'];
		if ($thread['discussion_type'] != 'resource' || !XenForo_Visitor::getInstance()->hasPermission('resource', 'view'))
		{
			return;
		}

		/* @var $resourceModel XenResource_Model_Resource */
		$resourceModel = XenForo_Model::create('XenResource_Model_Resource');

		$visitor = XenForo_Visitor::getInstance();

		$fetchOptions = array(
			'join' => XenResource_Model_Resource::FETCH_CATEGORY
				| XenResource_Model_Resource::FETCH_USER
				| XenResource_Model_Resource::FETCH_ATTACHMENT
				| XenResource_Model_Resource::FETCH_VERSION,
			'watchUserId' => $visitor['user_id'],
			'permissionCombinationId' => $visitor['permission_combination_id']
		);

		if ($visitor->hasPermission('resource', 'viewDeleted')) {
			$fetchOptions['join'] |= XenResource_Model_Resource::FETCH_DELETION_LOG;
		}

		$resource = $resourceModel->getResourceByDiscussionId(
			$thread['thread_id'], $fetchOptions
		);
		if (!$resource)
		{
			return;
		}

		/* @var $categoryModel XenResource_Model_Category */
		$categoryModel = XenForo_Model::create('XenResource_Model_Category');
		$categoryModel->setCategoryPermCache(
			$visitor['permission_combination_id'], $resource['resource_category_id'],
			$resource['category_permission_cache']
		);

		if (!$resourceModel->canViewResourceAndContainer($resource, $resource))
		{
			return;
		}

		$updateConditions = $categoryModel->getPermissionBasedFetchConditions($resource);
		if ($updateConditions['deleted'] === true || $updateConditions['moderated'] === true || $updateConditions['moderated'] == $resource['user_id'])
		{
			/* @var $updateModel XenResource_Model_Update */
			$updateModel = XenForo_Model::create('XenResource_Model_Update');

			$resourceUpdateCount = $updateModel->countUpdates(
				$updateConditions + array(
					'resource_id' => $resource['resource_id'],
					'resource_update_id_not' => $resource['description_update_id']
				)
			);
		}
		else
		{
			$resourceUpdateCount = $resource['update_count'];
		}

		$resource = $resourceModel->prepareResource($resource, $resource);
		$resource = $resourceModel->prepareResourceCustomFields($resource, $resource);

		$response->params['resource'] = $resource;
		$response->params['resourceUpdateCount'] = $resourceUpdateCount;
	}

	public static function navigationTabs(&$extraTabs, $selectedTabId)
	{
		if (XenForo_Visitor::getInstance()->hasPermission('resource', 'view'))
		{
			$extraTabs['resources'] = array(
				'title' => new XenForo_Phrase('resources'),
				'href' => XenForo_Link::buildPublicLink('full:resources'),
				'position' => 'middle',
				'linksTemplate' => 'resources_tab_links'
			);
		}
	}
}