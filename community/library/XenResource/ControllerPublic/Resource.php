<?php

class XenResource_ControllerPublic_Resource extends XenForo_ControllerPublic_Abstract
{
	protected function _preDispatch($action)
	{
		if (XenForo_Application::isRegistered('addOns'))
		{
			$addOns = XenForo_Application::get('addOns');
			if (!empty($addOns['XenResource']) && $addOns['XenResource'] < 1010000)
			{
				$response = $this->responseMessage(new XenForo_Phrase('board_currently_being_upgraded'));
				throw $this->responseException($response, 503);
			}
		}

		if (!$this->_getResourceModel()->canViewResources($error))
		{
			throw $this->getErrorOrNoPermissionResponseException($error);
		}
	}

	public function actionIndex()
	{
		if ($resourceId = $this->_input->filterSingle('resource_id', XenForo_Input::UINT))
		{
			return $this->responseReroute(__CLASS__, 'view');
		}

		$resourceModel = $this->_getResourceModel();
		$categoryModel = $this->_getCategoryModel();

		$defaultOrder = 'last_update';
		$defaultOrderDirection = 'desc';

		$order = $this->_input->filterSingle('order', XenForo_Input::STRING, array('default' => $defaultOrder));
		$orderDirection = $this->_input->filterSingle('direction', XenForo_Input::STRING, array('default' => $defaultOrderDirection));

		$typeFilter = $this->_input->filterSingle('type', XenForo_Input::STRING);
		$prefixFilter = $this->_input->filterSingle('prefix_id', XenForo_Input::INT);

		$criteria = array();
		if ($typeFilter == 'free')
		{
			$criteria['price'] = array('=', 0);
		}
		else if ($typeFilter == 'paid')
		{
			$criteria['price'] = array('>', 0);
		}
		else
		{
			$typeFilter = false;
		}

		if ($prefixFilter)
		{
			$criteria['prefix_id'] = $prefixFilter;
		}

		$criteria += $categoryModel->getPermissionBasedFetchConditions();

		$viewableCategories = $this->_getCategoryModel()->getViewableCategories();
		$criteria['resource_category_id'] = array_keys($viewableCategories);

		$categoryList = $categoryModel->groupCategoriesByParent($viewableCategories);
		$categoryList = $categoryModel->applyRecursiveCountsToGrouped($categoryList);
		$categories = isset($categoryList[0]) ? $categoryList[0] : array();

		$page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
		$perPage = XenForo_Application::get('options')->resourcesPerPage;

		if (!empty($criteria['price']) || $criteria['deleted'] === true || $criteria['moderated'] === true)
		{
			$totalResources = $resourceModel->countResources($criteria);
		}
		else
		{
			$totalResources = 0;
			foreach ($categories AS $category)
			{
				$totalResources += $category['resource_count'];
			}
		}

		$totalFeatured = 0;
		foreach ($categories AS $category)
		{
			$totalFeatured += $category['featured_count'];
		}

		if ($totalFeatured && !$typeFilter && !$prefixFilter && $order == $defaultOrder)
		{
			$featuredResources = $resourceModel->getFeaturedResourcesInCategories($criteria['resource_category_id'],
				array_merge(
					$this->_getResourceListFetchOptions(),
					array('limit' => 6, 'order' => 'random')
				)
			);
			$featuredResources = $this->_getResourceModel()->filterUnviewableResources($featuredResources);
			$featuredResources = $resourceModel->prepareResources($featuredResources);
		}
		else
		{
			$featuredResources = array();
		}

		$this->canonicalizePageNumber($page, $perPage, $totalResources, 'resources');

		$resources = $resourceModel->getResources($criteria,
			array_merge(
				$this->_getResourceListFetchOptions(),
				array(
					'perPage' => $perPage,
					'page' => $page,
					'order' => $order,
					'direction' => $orderDirection
				)
			)
		);
		$resources = $this->_getResourceModel()->filterUnviewableResources($resources);
		$resources = $resourceModel->prepareResources($resources);
		$inlineModOptions = $this->_getResourceModel()->getInlineModOptionsForResources($resources);

		$topResourceCount = XenForo_Application::getOptions()->topResourcesCount;
		if ($topResourceCount)
		{
			$topResources = $resourceModel->getResources($criteria,
				array_merge(
					$this->_getResourceListFetchOptions(),
					array(
						'limit' => $topResourceCount,
						'order' => 'rating_weighted',
						'direction' => 'desc'
					)
				)
			);
			$topResources = $this->_getResourceModel()->filterUnviewableResources($topResources);
		}
		else
		{
			$topResources = array();
		}

		$pageNavParams = array(
			'order' => ($order != $defaultOrder ? $order : false),
			'direction' => ($orderDirection != $defaultOrderDirection ? $orderDirection : false),
			'type' => ($typeFilter ? $typeFilter : false),
			'prefix_id' => ($prefixFilter ? $prefixFilter : false)
		);

		$viewParams = array(
			'categories' => $categoryModel->prepareCategories($categories),
			'showFilterTabs' => $this->_displayFilterOptions($viewableCategories, array_keys($viewableCategories)),
			'resources' => $resources,
			'totalResources' => $totalResources,
			'featuredResources' => $featuredResources,
			'ignoredNames' => $this->_getIgnoredContentUserNames($resources),
			'topResources' => $resourceModel->prepareResources($topResources),
			'canAddResource' => $categoryModel->canAddResource(),
			'inlineModOptions' => $inlineModOptions,

			'activeAuthors' => $resourceModel->getMostActiveAuthors(5),

			'page' => $page,
			'perPage' => $perPage,
			'pageNavParams' => $pageNavParams,

			'order' => $order,
			'direction' => $orderDirection,
			'typeFilter' => $typeFilter,
			'prefixFilter' => $prefixFilter
		);

		return $this->responseView('XenResource_ViewPublic_Resource_Index', 'resource_index', $viewParams);
	}

	public function actionFeatured()
	{
		$this->canonicalizeRequestUrl(XenForo_Link::buildPublicLink('resources/featured'));

		$resourceModel = $this->_getResourceModel();
		$categoryModel = $this->_getCategoryModel();

		$viewableCategories = $categoryModel->prepareCategories($categoryModel->getViewableCategories());

		$categoryList = $categoryModel->groupCategoriesByParent($viewableCategories);
		$categoryList = $categoryModel->applyRecursiveCountsToGrouped($categoryList);

		$searchCategoryIds = array_keys($viewableCategories);

		$resources = $resourceModel->getFeaturedResourcesInCategories($searchCategoryIds,
			$this->_getResourceListFetchOptions()
		);
		$resources = $this->_getResourceModel()->filterUnviewableResources($resources);
		$resources = $resourceModel->prepareResources($resources);

		if (!$resources)
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
				XenForo_Link::buildPublicLink('resources')
			);
		}

		$viewParams = array(
			'categoriesGrouped' => $categoryList,

			'resources' => $resources,
			'ignoredNames' => $this->_getIgnoredContentUserNames($resources),

			'inlineModOptions' => $this->_getResourceModel()->getInlineModOptionsForResources($resources)
		);

		return $this->responseView('XenResource_ViewPublic_Resource_Featured', 'resource_featured', $viewParams);
	}

	public function actionFilterMenu()
	{
		$categoryModel = $this->_getCategoryModel();
		$viewableCategories = $categoryModel->getViewableCategories();

		$categoryId = $this->_input->filterSingle('resource_category_id', XenForo_Input::UINT);
		if ($categoryId)
		{
			$category = $this->_getResourceHelper()->assertCategoryValidAndViewable();

			$categoryList = $categoryModel->groupCategoriesByParent($viewableCategories);

			$childCategories = (isset($categoryList[$category['resource_category_id']])
				? $categoryList[$category['resource_category_id']]
				: array()
			);
			if ($childCategories)
			{
				$searchCategoryIds = $categoryModel->getDescendantCategoryIdsFromGrouped($categoryList, $category['resource_category_id']);
				$searchCategoryIds[] = $category['resource_category_id'];
			}
			else
			{
				$searchCategoryIds = array($category['resource_category_id']);
			}
		}
		else
		{
			$category = null;
			$searchCategoryIds = array_keys($viewableCategories);
		}

		$params = $this->_input->filterSingle('params', XenForo_Input::ARRAY_SIMPLE);
		$typeFilter = isset($params['type']) ? strval($params['type']) : '';
		$prefixFilter = isset($params['prefix_id']) ? intval($params['prefix_id']) : '';

		$prefixModel = $this->_getPrefixModel();

		$prefixesGrouped = $prefixModel->getPrefixesByGroups();
		if ($prefixesGrouped)
		{
			$visiblePrefixes = $prefixModel->getVisiblePrefixIds(null, $searchCategoryIds);
			foreach ($prefixesGrouped AS $key => $prefixes)
			{
				foreach ($prefixes AS $prefixId => $prefix)
				{
					if (!isset($visiblePrefixes[$prefixId]))
					{
						unset($prefixes[$prefixId]);
					}
				}

				if (!count($prefixes))
				{
					unset($prefixesGrouped[$key]);
				}
			}
		}

		$showPriceFilters = false;

		if (XenForo_Application::getOptions()->resourceShowFilterTabs)
		{
			foreach ($searchCategoryIds AS $searchCategoryId)
			{
				if (!isset($viewableCategories[$searchCategoryId]))
				{
					continue;
				}
				if ($viewableCategories[$searchCategoryId]['allow_commercial_external'])
				{
					$showPriceFilters = true;
					break;
				}
			}
		}

		$viewParams = array(
			'category' => $category,
			'showPriceFilters' => $showPriceFilters,
			'params' => $params,
			'prefixesGrouped' => $prefixesGrouped,
			'typeFilter' => $typeFilter,
			'prefixFilter' => $prefixFilter
		);

		return $this->responseView('XenResource_ViewPublic_Resource_FilterMenu', 'resource_filter_menu', $viewParams);
	}

	public function actionCategory()
	{
		$categoryId = $this->_input->filterSingle('resource_category_id', XenForo_Input::UINT);
		if (!$categoryId)
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
				XenForo_Link::buildPublicLink('resources')
			);
		}

		$category = $this->_getResourceHelper()->assertCategoryValidAndViewable(null, array(
			'watchUserId' => XenForo_Visitor::getUserId()
		));

		$this->canonicalizeRequestUrl(XenForo_Link::buildPublicLink('resources/categories', $category));

		$resourceModel = $this->_getResourceModel();
		$categoryModel = $this->_getCategoryModel();

		$page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
		$perPage = XenForo_Application::get('options')->resourcesPerPage;

		$defaultOrder = 'last_update';
		$defaultOrderDirection = 'desc';

		$order = $this->_input->filterSingle('order', XenForo_Input::STRING, array('default' => $defaultOrder));
		$orderDirection = $this->_input->filterSingle('direction', XenForo_Input::STRING, array('default' => $defaultOrderDirection));

		$typeFilter = $this->_input->filterSingle('type', XenForo_Input::STRING);
		$prefixFilter = $this->_input->filterSingle('prefix_id', XenForo_Input::INT);

		$criteria = array();

		$viewableCategories = $categoryModel->prepareCategories($categoryModel->getViewableCategories());

		$categoryList = $categoryModel->groupCategoriesByParent($viewableCategories);
		$categoryList = $categoryModel->applyRecursiveCountsToGrouped($categoryList);

		$childCategories = (isset($categoryList[$category['resource_category_id']])
			? $categoryList[$category['resource_category_id']]
			: array()
		);
		if ($childCategories)
		{
			$searchCategoryIds = $categoryModel->getDescendantCategoryIdsFromGrouped($categoryList, $category['resource_category_id']);
			$searchCategoryIds[] = $category['resource_category_id'];
		}
		else
		{
			$searchCategoryIds = array($category['resource_category_id']);
		}

		$criteria['resource_category_id'] = $searchCategoryIds;
		if ($typeFilter == 'free')
		{
			$criteria['price'] = array('=', 0);
		}
		else if ($typeFilter == 'paid')
		{
			$criteria['price'] = array('>', 0);
		}
		else
		{
			$typeFilter = false;
		}

		if ($prefixFilter)
		{
			$criteria['prefix_id'] = $prefixFilter;
		}

		$criteria += $categoryModel->getPermissionBasedFetchConditions($category);

		$totalResources = $resourceModel->countResources($criteria);
		$this->canonicalizePageNumber($page, $perPage, $totalResources, 'resources/categories', $category);

		$fetchOptions = $this->_getResourceListFetchOptions();
		if ($criteria['deleted']) {
			$fetchOptions['join'] |= XenResource_Model_Resource::FETCH_DELETION_LOG;
		}

		$resources = $resourceModel->getResources(
			$criteria,
			array_merge(
				$fetchOptions,
				array(
					'perPage' => $perPage,
					'page' => $page,
					'order' => $order,
					'direction' => $orderDirection
				)
			)
		);
		$resources = $this->_getResourceModel()->filterUnviewableResources($resources);

		$resources = $resourceModel->prepareResources($resources, $category);
		$inlineModOptions = $this->_getResourceModel()->getInlineModOptionsForResources($resources);

		if ($categoryList[$category['parent_category_id']][$category['resource_category_id']]['featured_count']
			&& !$typeFilter && !$prefixFilter && $order == $defaultOrder
		)
		{
			$featuredResources = $resourceModel->getFeaturedResourcesInCategories($searchCategoryIds,
				array_merge(
					$this->_getResourceListFetchOptions(),
					array('limit' => 6, 'order' => 'random')
				)
			);
			$featuredResources = $this->_getResourceModel()->filterUnviewableResources($featuredResources);
			$featuredResources = $resourceModel->prepareResources($featuredResources);
		}
		else
		{
			$featuredResources = array();
		}

		$topResourceCount = XenForo_Application::getOptions()->topResourcesCount;
		if ($topResourceCount)
		{
			$topResources = $resourceModel->getResources($criteria,
				array_merge(
					$fetchOptions,
					array(
						'limit' => $topResourceCount,
						'order' => 'rating_weighted',
						'direction' => 'desc'
					)
				)
			);
			$topResources = $this->_getResourceModel()->filterUnviewableResources($topResources);
		}
		else
		{
			$topResources = array();
		}

		$pageNavParams = array(
			'order' => ($order != $defaultOrder ? $order : false),
			'direction' => ($orderDirection != $defaultOrderDirection ? $orderDirection : false),
			'type' => ($typeFilter ? $typeFilter : false),
			'prefix_id' => ($prefixFilter ? $prefixFilter : false)
		);

		$viewParams = array(
			'category' => $category,
			'categoriesGrouped' => $categoryList,
			'childCategories' => $childCategories,
			'categoryBreadcrumbs' => $categoryModel->getCategoryBreadcrumb($category, false),

			'resources' => $resources,
			'ignoredNames' => $this->_getIgnoredContentUserNames($resources),
			'featuredResources' => $featuredResources,
			'topResources' => $resourceModel->prepareResources($topResources, $category),
			'totalResources' => $totalResources,
			'inlineModOptions' => $inlineModOptions,

			'order' => $order,
			'orderDirection' => $orderDirection,
			'typeFilter' => $typeFilter,
			'prefixFilter' => $prefixFilter,
			'showFilterTabs' => $this->_displayFilterOptions($viewableCategories, $searchCategoryIds),

			'page' => $page,
			'perPage' => $perPage,
			'pageNavParams' => $pageNavParams,

			'canAddResource' => $categoryModel->canAddResource($category),
			'canWatchCategory' => $categoryModel->canWatchCategory($category)
		);

		return $this->responseView('XenResource_ViewPublic_Resource_Category', 'resource_category', $viewParams);
	}

	protected function _displayFilterOptions(array $viewableCategories, array $searchCategoryIds)
	{
		$allowPriceFilter = XenForo_Application::getOptions()->resourceShowFilterTabs;

		foreach ($searchCategoryIds AS $searchCategoryId)
		{
			if (!isset($viewableCategories[$searchCategoryId]))
			{
				continue;
			}

			if ($viewableCategories[$searchCategoryId]['prefix_cache']
				&& strlen($viewableCategories[$searchCategoryId]['prefix_cache']) > 5 # empty array
			)
			{
				return true;
			}

			if ($allowPriceFilter && $viewableCategories[$searchCategoryId]['allow_commercial_external'])
			{
				return true;
			}
		}

		return false;
	}

	public function actionCategoryFeatured()
	{
		$category = $this->_getResourceHelper()->assertCategoryValidAndViewable();

		$this->canonicalizeRequestUrl(XenForo_Link::buildPublicLink('resources/categories/featured', $category));

		$resourceModel = $this->_getResourceModel();
		$categoryModel = $this->_getCategoryModel();

		$viewableCategories = $categoryModel->prepareCategories($categoryModel->getViewableCategories());

		$categoryList = $categoryModel->groupCategoriesByParent($viewableCategories);
		$categoryList = $categoryModel->applyRecursiveCountsToGrouped($categoryList);

		$childCategories = (isset($categoryList[$category['resource_category_id']])
			? $categoryList[$category['resource_category_id']]
			: array()
		);
		if ($childCategories)
		{
			$searchCategoryIds = $categoryModel->getDescendantCategoryIdsFromGrouped($categoryList, $category['resource_category_id']);
			$searchCategoryIds[] = $category['resource_category_id'];
		}
		else
		{
			$searchCategoryIds = array($category['resource_category_id']);
		}

		$resources = $resourceModel->getFeaturedResourcesInCategories($searchCategoryIds,
			$this->_getResourceListFetchOptions()
		);
		$resources = $this->_getResourceModel()->filterUnviewableResources($resources);
		$resources = $resourceModel->prepareResources($resources);

		if (!$resources)
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
				XenForo_Link::buildPublicLink('resources/categories', $category)
			);
		}

		$viewParams = array(
			'category' => $category,
			'categoriesGrouped' => $categoryList,
			'childCategories' => $childCategories,
			'categoryBreadcrumbs' => $categoryModel->getCategoryBreadcrumb($category, true),

			'resources' => $resources,
			'ignoredNames' => $this->_getIgnoredContentUserNames($resources),
			'inlineModOptions' => $this->_getResourceModel()->getInlineModOptionsForResources($resources)
		);

		return $this->responseView('XenResource_ViewPublic_Resource_Category_Featured', 'resource_category_featured', $viewParams);
	}

	protected function _getResourceListFetchOptions()
	{
		return array(
			'join' => XenResource_Model_Resource::FETCH_VERSION
				| XenResource_Model_Resource::FETCH_USER
				| XenResource_Model_Resource::FETCH_CATEGORY
				| XenResource_Model_Resource::FETCH_FEATURED
		);
	}

	public function actionCategoryWatch()
	{
		$category = $this->_getResourceHelper()->assertCategoryValidAndViewable();

		$categoryModel = $this->_getCategoryModel();

		if (!$categoryModel->canWatchCategory($category, $key))
		{
			throw $this->getErrorOrNoPermissionResponseException($key);
		}

		$watchModel = $this->_getCategoryWatchModel();

		if ($this->isConfirmedPost())
		{
			if ($this->_input->filterSingle('stop', XenForo_Input::STRING))
			{
				$notifyOn = 'delete';
			}
			else
			{
				$notifyOn = $this->_input->filterSingle('notify_on', XenForo_Input::STRING);
			}

			$sendAlert = $this->_input->filterSingle('send_alert', XenForo_Input::BOOLEAN);
			$sendEmail = $this->_input->filterSingle('send_email', XenForo_Input::BOOLEAN);
			$includeChildren = $this->_input->filterSingle('include_children', XenForo_Input::BOOLEAN);

			$watchModel->setCategoryWatchState(
				XenForo_Visitor::getUserId(), $category['resource_category_id'],
				$notifyOn, $sendAlert, $sendEmail, $includeChildren
			);

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('resources/category', $category),
				null,
				array('linkPhrase' => ($notifyOn != 'delete' ? new XenForo_Phrase('unwatch_category') : new XenForo_Phrase('watch_category')))
			);
		}
		else
		{
			$watch = $watchModel->getUserCategoryWatchByCategoryId(
				XenForo_Visitor::getUserId(), $category['resource_category_id']
			);

			$viewParams = array(
				'category' => $category,
				'watch' => $watch,
				'categoryBreadcrumbs' => $categoryModel->getCategoryBreadcrumb($category, true),
			);

			return $this->responseView('XenResource_ViewPublic_Resource_Category_Watch', 'resource_category_watch', $viewParams);
		}
	}

	public function actionCategorySaveDraft()
	{
		$category = $this->_getResourceHelper()->assertCategoryValidAndViewable();

		$categoryModel = $this->_getCategoryModel();

		if (!$categoryModel->canAddResource($category, $key))
		{
			throw $this->getErrorOrNoPermissionResponseException($key);
		}

		$extra = $this->_input->filter(array(
			'title' => XenForo_Input::STRING,
			'tag_line' => XenForo_Input::STRING,
			'prefix_id' => XenForo_Input::UINT,
			'external_url' => XenForo_Input::STRING,
			'alt_support_url' => XenForo_Input::STRING,

			'attachment_hash' => XenForo_Input::STRING,
			'file_hash' => XenForo_Input::STRING,
			'version_string' => XenForo_Input::STRING,
			'resource_file_type' => XenForo_Input::STRING,
			'download_url' => XenForo_Input::STRING,
			'price' => XenForo_Input::UNUM,
			'currency' => XenForo_Input::STRING,
			'external_purchase_url' => XenForo_Input::STRING,
		));
		$extra['custom_fields'] = $this->_getResourceHelper()->getCustomFieldValues();
		$message = $this->getHelper('Editor')->getMessageText('message', $this->_input);

		$forceDelete = $this->_input->filterSingle('delete_draft', XenForo_Input::BOOLEAN);
		$draftId = "resource-category-$category[resource_category_id]";

		if (!strlen($message) || $forceDelete)
		{
			$draftSaved = false;
			$draftDeleted = $this->_getDraftModel()->deleteDraft($draftId) || $forceDelete;
		}
		else
		{
			$this->_getDraftModel()->saveDraft($draftId, $message, $extra);
			$draftSaved = true;
			$draftDeleted = false;
		}

		$viewParams = array(
			'category' => $category,
			'draftSaved' => $draftSaved,
			'draftDeleted' => $draftDeleted
		);
		$view = $this->responseView('XenResource_ViewPublic_Resource_Category_SaveDraft', '', $viewParams);
		$view->jsonParams = array(
			'draftSaved' => $draftSaved,
			'draftDeleted' => $draftDeleted
		);
		return $view;
	}

	protected function _getResourceAddOrEditResponse(array $resource, array $category, array $attachments = array())
	{
		$categoryModel = $this->_getCategoryModel();
		$versionModel = $this->_getVersionModel();
		$updateModel = $this->_getUpdateModel();

		$uploaderId = 'ResourceFile_' . md5(uniqid('', true));

		$categories = $categoryModel->getViewableCategories();
		// TODO: filter out ones that they can't add to that don't have children?
		// May need to do something slightly different for editing.

		$resourceType = '';
		if (empty($resource['resource_id']))
		{
			if (!empty($resource['resource_file_type']))
			{
				$resourceType = $resource['resource_file_type'];
			}
			else if ($category['allow_local'])
			{
				$resourceType = 'local';
			}
			else if ($category['allow_external'])
			{
				$resourceType = 'url';
			}
			else if ($category['allow_fileless'])
			{
				$resourceType = 'fileless';
			}
			else
			{
				$resourceType = 'commercial_external';
			}

			$canEditCategory = true;
			$showEditIconOption = XenForo_Application::getOptions()->resourceAllowIcons;
		}
		else
		{
			$categoryPermissions = $categoryModel->getCategoryPermCache(null, $category['resource_category_id']);
			$canEditCategory = XenForo_Permission::hasContentPermission($categoryPermissions, 'editAny');
			$showEditIconOption = $this->_getResourceModel()->canEditResourceIcon($resource, $category);
		}

		$fieldModel = $this->_getFieldModel();
		$customFields = $fieldModel->getResourceFieldsForEdit(
			$category['resource_category_id'], empty($resource['resource_id']) ? 0 : $resource['resource_id']
		);
		$customFields = $fieldModel->prepareResourceFields($customFields, true,
			!empty($resource['customFields']) ? $resource['customFields'] : array()
		);

		$viewParams = array(
			'resource' => $resource,
			'resourceType' => $resourceType,
			'allowFilelessOnly' => (
				$category['allow_fileless']
				&& !$category['allow_local']
				&& !$category['allow_external']
				&& !$category['allow_commercial_external']
			),
			'category' => $category,
			'categories' => $this->_getCategoryModel()->prepareCategories($categories),
			'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category),
			'canEditCategory' => $canEditCategory,

			'customFields' => $fieldModel->groupResourceFields($customFields),

			'prefixes' => $this->_getPrefixModel()->getUsablePrefixesInCategories($category['resource_category_id']),

			'attachments' => $attachments,
			'attachmentParams' => $updateModel->getUpdateAttachmentParams(),
			'attachmentConstraints' => $updateModel->getUpdateAttachmentConstraints(),

			'uploaderId' => $uploaderId,
			'fileParams' => array($uploaderId => $versionModel->getVersionFileParams(array(), array(
				'resource_category_id' => $category['resource_category_id']
			))),
			'fileConstraints' => array($uploaderId => $versionModel->getVersionFileConstraints()),
			'currencies' => $this->_getResourceModel()->getAvailableCurrencies(),

			'showEditIconOption' => $showEditIconOption
		);

		return $this->responseView('XenResource_ViewPublic_Resource_Add', 'resource_add', $viewParams);
	}

	public function actionAdd()
	{
		$categoryModel = $this->_getCategoryModel();

		$categoryId = $this->_input->filterSingle('resource_category_id', XenForo_Input::UINT);
		if ($categoryId)
		{
			$category = $this->_getResourceHelper()->assertCategoryValidAndViewable($categoryId);
			if (!$category['allowResource'])
			{
				$category = false;
			}
		}
		else
		{
			$category = false;
		}

		if (!$category)
		{
			if (!$categoryModel->canAddResource(null, $key))
			{
				throw $this->getErrorOrNoPermissionResponseException($key);
			}

			$categories = $categoryModel->prepareCategories($categoryModel->getViewableCategories());
			return $this->responseView('XenResource_ViewPublic_Resource_ChooseCategory', 'resource_choose_category', array(
				'categories' =>$categories
			));
		}
		else
		{
			if (!$categoryModel->canAddResource($category, $key))
			{
				throw $this->getErrorOrNoPermissionResponseException($key);
			}

			$resource = array(
				'resource_category_id' => $categoryId
			);

			$draft = $this->_getDraftModel()->getDraftByUserKey("resource-category-$categoryId", XenForo_Visitor::getUserId());
			if ($draft)
			{
				$extra = @unserialize($draft['extra_data']);
				$resource += array(
					'title' => $extra['title'],
					'prefix_id' => $extra['prefix_id'],
					'tag_line' => $extra['tag_line'],
					'external_url' => $extra['external_url'],
					'alt_support_url' => $extra['alt_support_url'],
					'version_string' => $extra['version_string'],
					'resource_file_type' => $extra['resource_file_type'],
					'download_url' => $extra['download_url'],
					'price' => $extra['price'],
					'currency' => $extra['currency'],
					'external_purchase_url' => $extra['external_purchase_url'],
					'description' => $draft['message'],
					'customFields' => $extra['custom_fields']
				);
			}

			return $this->_getResourceAddOrEditResponse($resource, $category);
		}
	}

	public function actionEdit()
	{
		$fetchOptions = array('join' => XenResource_Model_Resource::FETCH_DESCRIPTION);
		list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable(null, $fetchOptions);

		if (!$this->_getResourceModel()->canEditResource($resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		$attachmentModel = $this->_getAttachmentModel();
		$attachments = $attachmentModel->getAttachmentsByContentId('resource_update', $resource['description_update_id']);
		$attachments = $attachmentModel->prepareAttachments($attachments);

		return $this->_getResourceAddOrEditResponse($resource, $category, $attachments);
	}

	public function actionSave()
	{
		$this->_assertPostOnly();

		$categoryModel = $this->_getCategoryModel();

		if ($resourceId = $this->_input->filterSingle('resource_id', XenForo_Input::UINT))
		{
			list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable($resourceId);
			if (!$this->_getResourceModel()->canEditResource($resource, $category, $errorPhraseKey))
			{
				throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
			}

			$categoryPermissions = $categoryModel->getCategoryPermCache(null, $category['resource_category_id']);
			$canEditCategory = XenForo_Permission::hasContentPermission($categoryPermissions, 'editAny');
		}
		else
		{
			$category = false;
			$resource = false;
			$canEditCategory = true;
		}

		$resourceData = $this->_input->filter(array(
			'resource_category_id' => XenForo_Input::UINT,
			'title' => XenForo_Input::STRING,
			'tag_line' => XenForo_Input::STRING,
			'external_url' => XenForo_Input::STRING,
			'alt_support_url' => XenForo_Input::STRING,
			'prefix_id' => XenForo_Input::UINT
		));
		if (!$resourceData['resource_category_id'])
		{
			return $this->responseError(new XenForo_Phrase('you_must_select_category'));
		}

		$newCategory = $category;

		if ($canEditCategory)
		{
			if (!$resource || $resource['resource_category_id'] != $resourceData['resource_category_id'])
			{
				// new resource or changing category - let's make sure we can do that
				$newCategory = $this->_getResourceHelper()->assertCategoryValidAndViewable($resourceData['resource_category_id']);
				if (!$categoryModel->canAddResource($newCategory, $key))
				{
					throw $this->getErrorOrNoPermissionResponseException($key);
				}
			}

			$categoryId = $resourceData['resource_category_id'];
		}
		else
		{
			$categoryId = $resource['resource_category_id'];
			unset($resourceData['resource_category_id']);
		}

		if (!$resource
			|| $resource['prefix_id'] != $resourceData['prefix_id']
			|| $resource['resource_category_id'] != $categoryId
		)
		{
			if (!$this->_getPrefixModel()->verifyPrefixIsUsable($resourceData['prefix_id'], $categoryId))
			{
				$resourceData['prefix_id'] = 0; // not usable, just blank it out
			}
		}

		/* @var $dw XenResource_DataWriter_Resource */
		$dw = XenForo_DataWriter::create('XenResource_DataWriter_Resource');

		if ($resourceId)
		{
			$dw->setExistingData($resource['resource_id']);
		}
		else
		{
			$visitor = XenForo_Visitor::getInstance();

			$dw->set('user_id', $visitor['user_id']);
			$dw->set('username', $visitor['username']);
		}

		$dw->bulkSet($resourceData);

		if (!$resourceId || $newCategory['resource_category_id'] != $category['resource_category_id'])
		{
			if ($newCategory['always_moderate_create']
				&& ($dw->get('resource_state') == 'visible' || !$resourceId)
				&& !XenForo_Visitor::getInstance()->hasPermission('resource', 'approveUnapprove')
			)
			{
				$dw->set('resource_state', 'moderated');
			}
		}

		if (!$resourceId) {
			$watch = XenForo_Visitor::getInstance()->default_watch_state;
			if (!$watch)
			{
				$watch = 'watch_no_email';
			}

			$dw->setExtraData(XenResource_DataWriter_Resource::DATA_THREAD_WATCH_DEFAULT, $watch);
		}

		$customFields = $this->_getResourceHelper()->getCustomFieldValues($null, $shownCustomFields);
		$dw->setCustomFields($customFields, $shownCustomFields);

		$extraData = $this->_input->filter(array(
			'attachment_hash' => XenForo_Input::STRING,
			'file_hash' => XenForo_Input::STRING,
			'version_string' => XenForo_Input::STRING,
			'resource_file_type' => XenForo_Input::STRING,
			'download_url' => XenForo_Input::STRING,
			'price' => XenForo_Input::UNUM,
			'currency' => XenForo_Input::STRING,
			'external_purchase_url' => XenForo_Input::STRING
		));
		$message = $this->getHelper('Editor')->getMessageText('message', $this->_input);
		$message = XenForo_Helper_String::autoLinkBbCode($message);

		$descriptionDw = $dw->getDescriptionDw();
		$descriptionDw->set('message', $message);
		$descriptionDw->setExtraData(XenResource_DataWriter_Update::DATA_ATTACHMENT_HASH, $extraData['attachment_hash']);

		$versionDw = $dw->getVersionDw();
		if (!$resourceId)
		{
			switch ($extraData['resource_file_type'])
			{
				case 'file':
					if ($newCategory['allow_local'])
					{
						$versionDw->setExtraData(XenResource_DataWriter_Version::DATA_ATTACHMENT_HASH, $extraData['file_hash']);
					}
					break;

				case 'url':
					if ($newCategory['allow_external'])
					{
						if (!$extraData['download_url'])
						{
							$versionDw->error(new XenForo_Phrase('please_enter_external_download_url'), 'download_url');
						}
						else
						{
							$versionDw->set('download_url', $extraData['download_url']);
						}
					}
					break;

				case 'commercial_external':
					if ($newCategory['allow_commercial_external'])
					{
						if (!$extraData['price'] || !$extraData['currency'] || !$extraData['external_purchase_url'])
						{
							$dw->error(new XenForo_Phrase('please_complete_required_fields'));
						}
						else
						{
							$dw->bulkSet(array(
								'is_fileless' => 1,
								'price' => $extraData['price'],
								'currency' => $extraData['currency'],
								'external_purchase_url' => $extraData['external_purchase_url']
							));
							$versionDw->setOption(XenResource_DataWriter_Version::OPTION_IS_FILELESS, true);
						}
					}
					break;

				case 'fileless':
					if ($newCategory['allow_fileless'])
					{
						$dw->set('is_fileless', 1);
						$versionDw->setOption(XenResource_DataWriter_Version::OPTION_IS_FILELESS, true);

					}
					break;
			}
		}
		else if ($resource['external_purchase_url'])
		{
			// already an external purchase
			if (!$extraData['price'] || !$extraData['currency'] || !$extraData['external_purchase_url'])
			{
				$dw->error(new XenForo_Phrase('please_complete_required_fields'));
			}
			else
			{
				$dw->bulkSet(array(
					'price' => $extraData['price'],
					'currency' => $extraData['currency'],
					'external_purchase_url' => $extraData['external_purchase_url']
				));
				$versionDw->setOption(XenResource_DataWriter_Version::OPTION_IS_FILELESS, true);
			}
		}

		if ($extraData['version_string'] === '')
		{
			$extraData['version_string'] = XenForo_Locale::date(XenForo_Application::$time, 'Y-m-d');
		}

		$versionDw->set('version_string', $extraData['version_string']);

		$dw->preSave();

		// when editing, we can only do this check if not changing the category
		if ($newCategory['require_prefix']
			&& !$dw->get('prefix_id')
			&& (!$resource || $resource['resource_category_id'] == $newCategory['resource_category_id'])
		)
		{
			$dw->error(new XenForo_Phrase('please_select_a_prefix'), 'prefix_id');
		}

		if (!$dw->hasErrors())
		{
			$this->assertNotFlooding('post'); // use the action of "posting" as the trigger
		}

		$dw->save();
		$resource = $dw->getMergedData();
		$update = $descriptionDw->getMergedData();

		if ($dw->isUpdate() && XenForo_Visitor::getUserId() != $resource['user_id'])
		{
			$basicLog = $this->_getLogChanges($dw);
			if ($basicLog) {
				XenForo_Model_Log::logModeratorAction('resource', $resource, 'edit', $basicLog);
			}

			$basicLog = $this->_getLogChanges($descriptionDw);
			if ($basicLog) {
				XenForo_Model_Log::logModeratorAction('resource_update', $update, 'edit', $basicLog, $resource);
			}
		}

		if ($dw->isInsert())
		{
			$this->_getDraftModel()->deleteDraft("resource-category-$categoryId");
		}

		if ($this->_input->filterSingle('edit_icon', XenForo_Input::BOOLEAN))
		{
			XenForo_Application::getSession()->set('autoClickTrigger', '#EditIconTrigger');
		}

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('resources', $resource)
		);
	}

	public function actionIcon()
	{
		list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable();

		$resourceModel = $this->_getResourceModel();

		if (!$resourceModel->canEditResourceIcon($resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		if ($this->isConfirmedPost())
		{
			$icon = XenForo_Upload::getUploadedFile('icon');
			$delete = $this->_input->filterSingle('delete', XenForo_Input::BOOLEAN);

			if ($icon)
			{
				$resourceModel->uploadResourceIcon($icon, $resource['resource_id']);
			}
			else if ($delete)
			{
				$resourceModel->deleteResourceIcon($resource['resource_id']);
			}

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('resources', $resource)
			);
		}
		else
		{
			$viewParams = array(
				'resource' => $resource,
				'category' => $category,
				'iconSize' => XenResource_Model_Resource::$iconSize
			);

			return $this->responseView('XenResource_ViewPublic_Resource_Icon', 'resource_icon', $viewParams);
		}
	}

	protected function _getResourceViewWrapper($selectedTab, array $resource, array $category,
		XenForo_ControllerResponse_View $subView
	)
	{
		$resourceModel = $this->_getResourceModel();
		$otherCount = XenForo_Application::getOptions()->authorOtherResourcesCount;
		if ($resource['resource_count'] > 1 && $otherCount)
		{
			// author has another resource
			$otherResources = $resourceModel->getResources(
				array(
					'user_id' => $resource['user_id'],
					'resource_id_not' => $resource['resource_id'],
				),
				array(
					'join' => XenResource_Model_Resource::FETCH_VERSION,
					'permissionCombinationId' => XenForo_Visitor::getInstance()->permission_combination_id,
					'limit' => $otherCount
				)
			);
			$otherResources = $this->_getResourceModel()->filterUnviewableResources($otherResources);
		}
		else
		{
			$otherResources = array();
		}

		if ($resource['discussion_thread_id'])
		{
			$threadModel = $this->getModelFromCache('XenForo_Model_Thread');
			$thread = $threadModel->getThreadById(
				$resource['discussion_thread_id'],
				array(
					'join' => XenForo_Model_Thread::FETCH_FORUM,
					'permissionCombinationId' => XenForo_Visitor::getInstance()->permission_combination_id
				)
			);

			$null = null;
			if ($thread
				&& $thread['discussion_type'] == 'resource'
				&& !$threadModel->canViewThreadAndContainer(
					$thread, $thread, $null, XenForo_Permission::unserializePermissions($thread['node_permission_cache'])
				)
			)
			{
				$thread = false;
			}
		}
		else
		{
			$thread = false;
		}

		$updateConditions = $this->_getCategoryModel()->getPermissionBasedFetchConditions($category);

		if ($updateConditions['deleted'] === true || $updateConditions['moderated'] === true || $updateConditions['moderated'] == $resource['user_id'])
		{
			$resourceUpdateCount = $this->_getUpdateModel()->countUpdates(
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

		$session = XenForo_Application::getSession();
		$autoClickTrigger = $session->get('autoClickTrigger');
		if ($autoClickTrigger)
		{
			$session->remove('autoClickTrigger');
		}

		$viewParams = array(
			'resource' => $resource,
			'category' => $category,
			'selectedTab' => $selectedTab,

			'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category),
			'otherResources' => $otherResources,
			'thread' => $thread,
			'resourceUpdateCount' => $resourceUpdateCount,

			'autoClickTrigger' => $autoClickTrigger
		);
		$response = $this->responseView('XenResource_ViewPublic_Resource_View', 'resource_view', $viewParams);
		$response->subView = $subView;

		return $response;
	}

	protected function _getResourceViewInfo(array $fetchOptions = array())
	{
		$fetchOptions += array(
			'join' => 0,
			'watchUserId' => XenForo_Visitor::getUserId(),
			'downloadUserId' => XenForo_Visitor::getUserId()
		);
		$fetchOptions['join'] |= XenResource_Model_Resource::FETCH_VERSION
			| XenResource_Model_Resource::FETCH_ATTACHMENT;

		if (XenForo_Visitor::getInstance()->hasPermission('resource', 'viewDeleted')) {
			$fetchOptions['join'] |= XenResource_Model_Resource::FETCH_DELETION_LOG;
		}

		list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable(
			null, $fetchOptions
		);

		return array($resource, $category);
	}

	public function actionView()
	{
		list($resource, $category) = $this->_getResourceViewInfo();

		$resourceUpdateId = $this->_input->filterSingle('update', XenForo_Input::UINT);
		if (!$resourceUpdateId)
		{
			$resourceUpdateId = $this->_input->filterSingle('resource_update_id', XenForo_Input::UINT);
		}
		if ($resourceUpdateId)
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
				XenForo_Link::buildPublicLink('resources/update', $resource, array('update' => $resourceUpdateId))
			);
		}

		$this->canonicalizeRequestUrl(XenForo_Link::buildPublicLink('resources', $resource));

		$resourceModel = $this->_getResourceModel();
		$updateModel = $this->_getUpdateModel();
		$options = XenForo_Application::getOptions();

		$descriptionUpdate = $updateModel->getUpdateById($resource['description_update_id'], array(
			'likeUserId' => XenForo_Visitor::getUserId(),
			'setUserId' => $resource['user_id']
		));
		if (!$descriptionUpdate)
		{
			return $this->responseError(new XenForo_Phrase('requested_resource_not_found'), 404);
		}
		$updates = array($resource['description_update_id'] => $descriptionUpdate);
		$updates = $updateModel->getAndMergeAttachmentsIntoUpdates($updates);

		$update = $updates[$resource['description_update_id']];

		$isLimited = false;
		if ($resource['is_fileless']
			&& !$resource['external_purchase_url']
			&& $options->get('resourceFilelessViewFull', 'limit')
			&& !$resourceModel->canDownloadResource($resource, $category)
		)
		{
			$limit = $options->get('resourceFilelessViewFull', 'length');
			if ($limit > 0) {
				$trimmed = XenForo_Helper_String::wholeWordTrim($update['message'], $limit);
				$isLimited = strlen($trimmed) < strlen($update['message']);
			} else {
				$trimmed = '';
				$isLimited = true;
			}
			if ($isLimited)
			{
				$parser = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('XenForo_BbCode_Formatter_BbCode_AutoLink', false));
				$update['message'] = $parser->render($trimmed);
				$update['isMessageTrimmed'] = true;
			}
		}

		$maxRecentUpdateCount = XenForo_Application::getOptions()->resourceRecentUpdatesCount;
		if ($resource['update_count'] && $maxRecentUpdateCount)
		{
			$updates = $updateModel->getUpdates(
				array(
					'resource_id' => $resource['resource_id'],
					'resource_update_id_not' => $resource['description_update_id']
				),
				array(
					'order' => 'post_date',
					'direction' => 'desc',
					'limit' => $maxRecentUpdateCount
				)
			);
		}
		else
		{
			$updates = array();
		}
		$showReadAllUpdates = ($resource['update_count'] > 1);

		$maxRecentReviewCount = XenForo_Application::getOptions()->resourceRecentReviewsCount;
		if ($resource['review_count'] && $maxRecentReviewCount)
		{
			$reviews = $this->_getRatingModel()->getRatings(
				array(
					'resource_id' => $resource['resource_id'],
					'is_review' => 1
				),
				array(
					'join' => XenResource_Model_Rating::FETCH_USER,
					'limit' => $maxRecentReviewCount
				)
			);
			$reviews = $this->_getRatingModel()->prepareRatings($reviews, $resource, $category);
			$showMoreReviews = ($resource['review_count'] > $maxRecentReviewCount);
		}
		else
		{
			$reviews = array();
			$showMoreReviews = false;
		}

		$viewParams = array(
			'resource' => $resource,
			'category' => $category,

			'update' => $updateModel->prepareUpdate($update, $resource, $category),
			'isLimited' => $isLimited,
			'updates' => $updateModel->prepareUpdates($updates, $resource, $category),
			'showReadAllUpdates' => $showReadAllUpdates,
			'canViewImages' => $updateModel->canViewUpdateImages($resource, $category),
			'canViewWarnings' => $this->getModelFromCache('XenForo_Model_User')->canViewWarnings(),

			'reviews' => $reviews,
			'ignoredReviewNames' => $this->_getIgnoredContentUserNames($reviews),
			'showMoreReviews' => $showMoreReviews
		);

		return $this->_getResourceViewWrapper('description', $resource, $category,
			$this->responseView('XenResource_ViewPublic_Resource_Description', 'resource_description', $viewParams)
		);
	}

	public function actionExtra()
	{
		list($resource, $category) = $this->_getResourceViewInfo();

		if (!$resource['showExtraInfoTab'])
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
				XenForo_Link::buildPublicLink('resource', $resource)
			);
		}

		$this->canonicalizeRequestUrl(XenForo_Link::buildPublicLink('resources/extra', $resource));

		$viewParams = array(
			'resource' => $resource,
			'category' => $category
		);

		return $this->_getResourceViewWrapper('extra', $resource, $category,
			$this->responseView('XenResource_ViewPublic_Resource_Extra', 'resource_extra', $viewParams)
		);
	}

	public function actionField()
	{
		list($resource, $category) = $this->_getResourceViewInfo();

		$fieldId = $this->_input->filterSingle('field', XenForo_Input::STRING);
		$fields = $this->_getFieldModel()->getResourceFieldCache();

		if (!isset($fields[$fieldId])
			|| !isset($category['fieldCache']['new_tab'][$fieldId])
			|| !isset($resource['customFields'][$fieldId])
			|| (is_string($resource['customFields'][$fieldId]) && $resource['customFields'][$fieldId] === '')
			|| (is_array($resource['customFields'][$fieldId]) && count($resource['customFields'][$fieldId]) == 0)
		)
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
				XenForo_Link::buildPublicLink('resource', $resource)
			);
		}

		$viewParams = array(
			'resource' => $resource,
			'category' => $category,

			'field' => $fields[$fieldId],
			'fieldId' => $fieldId
		);

		return $this->_getResourceViewWrapper('field_' . $fieldId, $resource, $category,
			$this->responseView('XenResource_ViewPublic_Resource_Field', 'resource_field', $viewParams)
		);
	}

	public function actionQuickPreview()
	{
		list($resource, $category) = $this->_getResourceViewInfo();

		if (!$this->_getResourceModel()->canViewPreview($resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		$update = $this->_getUpdateModel()->getUpdateById($resource['description_update_id'], array(
			'likeUserId' => XenForo_Visitor::getUserId(),
			'setUserId' => $resource['user_id']
		));

		$viewParams = array(
			'resource' => $resource,
			'category' => $category,
			'update' => $update
		);
		return $this->responseView('XenResource_ViewPublic_Resource_QuickPreview', 'resource_quick_preview', $viewParams);
	}

	public function actionReassign()
	{
		list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable();

		if (!$this->_getResourceModel()->canReassignResource($resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		if ($this->isConfirmedPost())
		{
			$user = $this->getModelFromCache('XenForo_Model_User')->getUserByName(
				$this->_input->filterSingle('username', XenForo_Input::STRING),
				array('join' => XenForo_Model_User::FETCH_USER_PERMISSIONS)
			);
			$user['permissions'] = XenForo_Permission::unserializePermissions($user['global_permission_cache']);
			if (!$user || !XenForo_Permission::hasPermission($user['permissions'], 'resource', 'view'))
			{
				return $this->responseError(new XenForo_Phrase('you_may_only_reassign_resource_to_user_with_permission_to_view'));
			}

			$dw = XenForo_DataWriter::create('XenResource_DataWriter_Resource');
			$dw->setExistingData($resource['resource_id']);

			$dw->bulkSet(array(
				'user_id' => $user['user_id'],
				'username' => $user['username']
			));
			$dw->save();

			XenForo_Model_Log::logModeratorAction('resource', $resource, 'reassign', array('from' => $dw->getExisting('username')));

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('resources', $resource)
			);
		}
		else
		{
			$viewParams = array(
				'resource' => $resource,
				'category' => $category,
				'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category)
			);

			return $this->responseView('XenResource_ViewPublic_Resource_Reassign', 'resource_reassign', $viewParams);
		}
	}

	public function actionToggleFeatured()
	{
		$this->_checkCsrfFromToken($this->_input->filterSingle('t', XenForo_Input::STRING));

		list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable();

		if (!$this->_getResourceModel()->canFeatureUnfeatureResource($resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		if ($resource['feature_date'])
		{
			$this->_getResourceModel()->unfeatureResource($resource);

			$redirectPhrase = 'resource_unfeatured';
			$actionPhrase = 'feature_resource';
		}
		else
		{
			$this->_getResourceModel()->featureResource($resource);

			$redirectPhrase = 'resource_featured';
			$actionPhrase = 'unfeature_resource';
		}

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('resources', $resource),
			new XenForo_Phrase($redirectPhrase),
			array('actionPhrase' => new XenForo_Phrase($actionPhrase))
		);
	}

	public function actionDelete()
	{
		list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable();

		$hardDelete = $this->_input->filterSingle('hard_delete', XenForo_Input::UINT);
		$deleteType = ($hardDelete ? 'hard' : 'soft');

		if (!$this->_getResourceModel()->canDeleteResource($resource, $category, $deleteType, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		if ($this->isConfirmedPost())
		{
			$dw = XenForo_DataWriter::create('XenResource_DataWriter_Resource');
			$dw->setExistingData($resource['resource_id']);

			if ($hardDelete)
			{
				$dw->delete();

				XenForo_Model_Log::logModeratorAction('resource', $resource, 'delete_hard');
			}
			else
			{
				$reason = $this->_input->filterSingle('reason', XenForo_Input::STRING);
				$dw->setExtraData(XenResource_DataWriter_Resource::DATA_DELETE_REASON, $reason);
				$dw->set('resource_state', 'deleted');
				$dw->save();

				if (XenForo_Visitor::getUserId() != $resource['user_id'])
				{
					XenForo_Model_Log::logModeratorAction('resource', $resource, 'delete_soft', array('reason' => $reason));
				}
			}

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('resources/categories', $category)
			);
		}
		else
		{
			$viewParams = array(
				'resource' => $resource,
				'category' => $category,
				'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category),
				'canHardDelete' => $this->_getResourceModel()->canDeleteResource($resource, $category, 'hard')
			);

			return $this->responseView('XenResource_ViewPublic_Resource_Delete', 'resource_delete', $viewParams);
		}
	}

	public function actionUndelete()
	{
		list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable();

		if (!$this->_getResourceModel()->canUndeleteResource($resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		if ($this->isConfirmedPost())
		{
			$dw = XenForo_DataWriter::create('XenResource_DataWriter_Resource');
			$dw->setExistingData($resource['resource_id']);
			$dw->set('resource_state', 'visible');
			$dw->save();

			XenForo_Model_Log::logModeratorAction('resource', $resource, 'undelete');

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('resources', $resource)
			);
		}
		else
		{
			$viewParams = array(
				'resource' => $resource,
				'category' => $category,
				'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category)
			);

			return $this->responseView('XenResource_ViewPublic_Resource_Undelete', 'resource_undelete', $viewParams);
		}
	}

	public function actionApprove()
	{
		$this->_checkCsrfFromToken($this->_input->filterSingle('t', XenForo_Input::STRING));

		list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable();

		if (!$this->_getResourceModel()->canApproveResource($resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		$dw = XenForo_DataWriter::create('XenResource_DataWriter_Resource');
		$dw->setExistingData($resource['resource_id']);
		$dw->set('resource_state', 'visible');
		$dw->save();

		XenForo_Model_Log::logModeratorAction('resource', $resource, 'approve');

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('resources', $resource)
		);
	}

	public function actionUnapprove()
	{
		$this->_checkCsrfFromToken($this->_input->filterSingle('t', XenForo_Input::STRING));

		list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable();

		if (!$this->_getResourceModel()->canUnapproveResource($resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		$dw = XenForo_DataWriter::create('XenResource_DataWriter_Resource');
		$dw->setExistingData($resource['resource_id']);
		$dw->set('resource_state', 'moderated');
		$dw->save();

		XenForo_Model_Log::logModeratorAction('resource', $resource, 'unapprove');

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('resources', $resource)
		);
	}

	public function actionRate()
	{
		$fetchOptions = array(
			'downloadUserId' => XenForo_Visitor::getUserId()
		);
		list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable(null, $fetchOptions);

		$resourceModel = $this->_getResourceModel();

		if (!$resourceModel->canRateResource($resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		$visitor = XenForo_Visitor::getInstance();

		$input = $this->_input->filter(array(
			'rating' => XenForo_Input::UINT,
			'message' => XenForo_Input::STRING,
			'is_anonymous' => XenForo_Input::UINT
		));

		$existing = $this->_getRatingModel()->getRatingByVersionAndUserId($resource['current_version_id'], $visitor['user_id']);
		if ($existing && !$this->_getRatingModel()->canUpdateRating($existing, $resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		if ($this->isConfirmedPost())
		{
			if (XenForo_Application::getOptions()->resourceReviewRequired && strlen($input['message']) == 0)
			{
				return $this->responseError(new XenForo_Phrase('please_provide_review_with_your_rating'));
			}

			$ratingDw = XenForo_DataWriter::create('XenResource_DataWriter_Rating', XenForo_DataWriter::ERROR_EXCEPTION);
			$ratingDw->set('resource_version_id', $resource['current_version_id']);
			$ratingDw->set('version_string', $resource['version_string']);
			$ratingDw->set('resource_id', $resource['resource_id']);
			$ratingDw->set('user_id', $visitor['user_id']);
			$ratingDw->set('rating', $input['rating']);
			$ratingDw->set('message', $input['message']);

			if (XenForo_Application::getOptions()->resourceAllowAnonReview)
			{
				$ratingDw->set('is_anonymous', $input['is_anonymous']);
			}

			if ($existing)
			{
				$deleteDw = XenForo_DataWriter::create('XenResource_DataWriter_Rating');
				$deleteDw->setExistingData($existing, true);
				$deleteDw->delete();
			}

			$ratingDw->save();

			$versionDw = $ratingDw->getExtraData(XenResource_DataWriter_Rating::DATA_VERSION_DW);
			$newRating = $resourceModel->getRatingAverage($versionDw->get('rating_sum'), $versionDw->get('rating_count'), true);
			$hintText = new XenForo_Phrase('x_votes', array('count' => $versionDw->get('rating_count')));

			if ($ratingDw->get('is_review'))
			{
				$link = XenForo_Link::buildPublicLink('resources/reviews', $resource);
			}
			else
			{
				$link = XenForo_Link::buildPublicLink('resources', $resource);
			}

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$link,
				new XenForo_Phrase('your_rating_has_been_recorded'),
				array(
					'newRating' => $newRating,
					'hintText' => $hintText
				)
			);
		}
		else
		{
			$viewParams = array(
				'resource' => $resource,
				'category' => $category,
				'rating' => $input['rating'],
				'message' => $input['message'],
				'existing' => ($existing && $existing['rating_state'] == 'visible' ? $existing : false),

				'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category),
			);

			return $this->responseView('XenResource_ViewPublic_Resource_Rate', 'resource_rate', $viewParams);
		}
	}

	/**
	 * Inserts/updates/deletes a thread watch.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionWatch()
	{
		list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable();

		$resourceModel = $this->_getResourceModel();
		$watchModel = $this->_getResourceWatchModel();

		if (!$resourceModel->canWatchResource($resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		if ($this->isConfirmedPost())
		{
			if ($this->_input->filterSingle('stop', XenForo_Input::STRING))
			{
				$newState = '';
			}
			else if ($this->_input->filterSingle('email_subscribe', XenForo_Input::UINT))
			{
				$newState = 'watch_email';
			}
			else
			{
				$newState = 'watch_no_email';
			}

			$watchModel->setResourceWatchState(XenForo_Visitor::getUserId(), $resource['resource_id'], $newState);

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('resources', $resource),
				null,
				array('linkPhrase' => ($newState ? new XenForo_Phrase('unwatch_this_resource') : new XenForo_Phrase('watch_this_resource')))
			);
		}
		else
		{
			$watch = $watchModel->getUserResourceWatchByResourceId(
				XenForo_Visitor::getUserId(), $resource['resource_id']
			);

			$viewParams = array(
				'resource' => $resource,
				'category' => $category,
				'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category),
				'watch' => $watch
			);

			return $this->responseView('XenResource_ViewPublic_Resource_Watch', 'resource_watch', $viewParams);
		}
	}

	public function actionDownload()
	{
		$fetchOptions = array(
			'watchUserId' => XenForo_Visitor::getUserId()
		);
		list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable(null, $fetchOptions);

		if ($resource['is_fileless'])
		{
			return $this->responseError(new XenForo_Phrase('fileless_resources_cannot_be_downloaded'));
		}

		$versionModel = $this->_getVersionModel();

		$versionId = $this->_input->filterSingle('version', XenForo_Input::UINT);
		$version = $versionModel->getVersionById($versionId, array(
			'join' => XenResource_Model_Version::FETCH_FILE
		));
		if (!$version || $version['resource_id'] != $resource['resource_id'])
		{
			return $this->responseNoPermission();
		}

		if (!$versionModel->canDownloadVersion($version, $resource, $category, $error))
		{
			throw $this->getErrorOrNoPermissionResponseException($error);
		}

		// watch downloads automatically based on default settings
		$visitor = XenForo_Visitor::getInstance();
		$this->_getResourceWatchModel()->setResourceWatchStateWithUserDefault(
			$visitor['user_id'], $resource['resource_id'], $visitor['default_watch_state']
		);

		$this->_getVersionModel()->logVersionDownload($version, XenForo_Visitor::getUserId());

		if ($version['download_url'])
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
				$version['download_url']
			);
		}
		else
		{
			$this->_request->setParam('attachment_id', $version['attachment_id']);
			return $this->responseReroute('XenForo_ControllerPublic_Attachment', 'index');
		}
	}

	public function actionHistory()
	{
		list($resource, $category) = $this->_getResourceViewInfo();

		$this->canonicalizeRequestUrl(XenForo_Link::buildPublicLink('resources/history', $resource));

		$versionModel = $this->_getVersionModel();

		$versions = $versionModel->getVersions(
			array(
				'resource_id' => $resource['resource_id'],
			) + $this->_getCategoryModel()->getPermissionBasedFetchConditions($category),
			array(
				'join' => XenResource_Model_Version::FETCH_FILE
			)
		);
		$versions = $versionModel->prepareVersions($versions, $resource, $category);

		$canDelete = false;
		foreach ($versions AS $version)
		{
			if ($version['canDelete'])
			{
				$canDelete = true;
				break;
			}
		}

		$viewParams = array(
			'resource' => $resource,
			'category' => $category,
			'versions' => $versions,
			'canDelete' => $canDelete
		);
		return $this->_getResourceViewWrapper('history', $resource, $category,
			$this->responseView('XenResource_ViewPublic_Resource_History', 'resource_history', $viewParams)
		);
	}

	public function actionReviews()
	{
		list($resource, $category) = $this->_getResourceViewInfo();

		$this->canonicalizeRequestUrl(XenForo_Link::buildPublicLink('resources/reviews', $resource));

		$ratingModel = $this->_getRatingModel();

		$page = $this->_input->filterSingle('page', XenForo_Input::UINT);
		$perPage = XenForo_Application::getOptions()->resourceReviewsPerPage;

		$reviewId = $this->_input->filterSingle('resource_rating_id', XenForo_Input::UINT);
		if ($reviewId)
		{
			$review = $ratingModel->getRatingById($reviewId);
			if (!$review || !$review['is_review'] || $review['resource_id'] != $resource['resource_id']
				|| !$ratingModel->canViewRating($review, $resource, $category)
			)
			{
				return $this->responseError(new XenForo_Phrase('requested_review_not_found'), 404);
			}

			$params = array();

			$reviewsAfter = $ratingModel->countReviewsAfterDateInResource($review['resource_id'], $review['rating_date']);

			$page = floor($reviewsAfter / $perPage) + 1;
			if ($page > 1)
			{
				$params['page'] = $page;
			}

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
				XenForo_Link::buildPublicLink('resources/reviews', $resource, $params) . "#review-$review[resource_version_id]-$review[user_id]"
			);
		}

		$reviews = $ratingModel->getRatings(
			array(
				'resource_id' => $resource['resource_id'],
				'is_review' => 1
			) + $this->_getCategoryModel()->getPermissionBasedFetchConditions($category),
			array(
				'join' => XenResource_Model_Rating::FETCH_USER,
				'page' => $page,
				'perPage' => $perPage
			)
		);

		if (!$reviews)
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
				XenForo_Link::buildPublicLink('resources', $resource)
			);
		}

		$reviews = $ratingModel->prepareRatings($reviews, $resource, $category);

		$viewParams = array(
			'resource' => $resource,
			'category' => $category,

			'reviews' => $reviews,
			'ignoredNames' => $this->_getIgnoredContentUserNames($reviews),
			'page' => $page,
			'perPage' => $perPage,

			'canViewWarnings' => $this->getModelFromCache('XenForo_Model_User')->canViewWarnings(),
		);
		return $this->_getResourceViewWrapper('reviews', $resource, $category,
			$this->responseView('XenResource_ViewPublic_Resource_Reviews', 'resource_reviews', $viewParams)
		);
	}

	public function actionReviewsReply()
	{
		list($review, $resource, $category) = $this->_getResourceHelper()->assertReviewValidAndViewable();

		if (!$this->_getRatingModel()->canReplyToRating($review, $resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		if ($this->isConfirmedPost())
		{
			$dw = XenForo_DataWriter::create('XenResource_DataWriter_Rating');
			$dw->setExistingData($review['resource_rating_id']);
			$dw->set('author_response', $this->_input->filterSingle('author_response', XenForo_Input::STRING));
			$dw->save();

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('resources/reviews', $resource, array('review' => $review))
			);
		}
		else
		{
			$viewParams = array(
				'review' => $review,
				'resource' => $resource,
				'category' => $category,
				'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category),
			);

			return $this->responseView('XenResource_ViewPublic_Review_Reply', 'resource_review_reply', $viewParams);
		}
	}

	public function actionReviewsReport()
	{
		list($review, $resource, $category) = $this->_getResourceHelper()->assertReviewValidAndViewable();

		if (!$this->_getRatingModel()->canReportRating($review, $resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		if ($this->isConfirmedPost())
		{
			$reportMessage = $this->_input->filterSingle('message', XenForo_Input::STRING);
			if (!$reportMessage)
			{
				return $this->responseError(new XenForo_Phrase('please_enter_reason_for_reporting_this_message'));
			}

			$this->assertNotFlooding('report');

			$update['resource'] = $resource;
			$update['category'] = $category;

			/* @var $reportModel XenForo_Model_Report */
			$reportModel = XenForo_Model::create('XenForo_Model_Report');
			$reportModel->reportContent('resource_rating', $review, $reportMessage);

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('resources/reviews', $resource, array('review' => $review)),
				new XenForo_Phrase('thank_you_for_reporting_this_message')
			);
		}
		else
		{
			$viewParams = array(
				'review' => $review,
				'resource' => $resource,
				'category' => $category,
				'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category),
			);

			return $this->responseView('XenResource_ViewPublic_Review_Report', 'resource_review_report', $viewParams);
		}
	}

	public function actionReviewsDelete()
	{
		list($review, $resource, $category) = $this->_getResourceHelper()->assertReviewValidAndViewable();

		$responseOnly = $this->_input->filterSingle('response', XenForo_Input::UINT);

		$hardDelete = $this->_input->filterSingle('hard_delete', XenForo_Input::UINT);
		$deleteType = ($hardDelete ? 'hard' : 'soft');

		if ($responseOnly && !$this->_getRatingModel()->canDeleteRatingResponse($review, $resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}
		else if (!$responseOnly && !$this->_getRatingModel()->canDeleteRating($review, $resource, $category, $deleteType, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		if ($this->isConfirmedPost())
		{
			$dw = XenForo_DataWriter::create('XenResource_DataWriter_Rating');
			$dw->setExistingData($review['resource_rating_id']);
			if ($responseOnly)
			{
				$dw->set('author_response', '');
				$dw->save();

				XenForo_Model_Log::logModeratorAction('resource_rating', $review, 'delete_response');

				return $this->responseRedirect(
					XenForo_ControllerResponse_Redirect::SUCCESS,
					XenForo_Link::buildPublicLink('resources/reviews', $resource, array('review' => $review))
				);
			}
			else
			{
				if ($hardDelete)
				{
					$dw->delete();
				}
				else
				{
					$dw->set('rating_state', 'deleted');
					$dw->save();
				}

				XenForo_Model_Log::logModeratorAction('resource_rating', $review, $hardDelete ? 'delete_hard' : 'delete_soft');

				return $this->responseRedirect(
					XenForo_ControllerResponse_Redirect::SUCCESS,
					XenForo_Link::buildPublicLink('resources', $resource)
				);
			}
		}
		else
		{
			$viewParams = array(
				'review' => $review,
				'resource' => $resource,
				'category' => $category,
				'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category),
				'responseOnly' => $responseOnly,
				'canHardDelete' => $this->_getRatingModel()->canDeleteRating($review, $resource, $category, 'hard')
			);

			return $this->responseView('XenResource_ViewPublic_Review_Delete', 'resource_review_delete', $viewParams);
		}
	}

	public function actionReviewsUndelete()
	{
		$this->_checkCsrfFromToken($this->_input->filterSingle('t', XenForo_Input::STRING));

		list($review, $resource, $category) = $this->_getResourceHelper()->assertReviewValidAndViewable();

		$dw = XenForo_DataWriter::create('XenResource_DataWriter_Rating');
		$dw->setExistingData($review['resource_rating_id']);
		$dw->set('rating_state', 'visible');
		$dw->save();

		XenForo_Model_Log::logModeratorAction('resource_rating', $review, 'undelete');

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('resources/reviews', $resource, array('review' => $review))
		);
	}

	public function actionUpdates()
	{
		list($resource, $category) = $this->_getResourceViewInfo();

		$this->canonicalizeRequestUrl(XenForo_Link::buildPublicLink('resources/updates', $resource));

		$updateModel = $this->_getUpdateModel();

		$conditions = array(
			'resource_id' => $resource['resource_id'],
			'resource_update_id_not' => $resource['description_update_id']
		) + $this->_getCategoryModel()->getPermissionBasedFetchConditions($category);

		$totalUpdates = $updateModel->countUpdates($conditions);
		if (!$totalUpdates)
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
				XenForo_Link::buildPublicLink('resources', $resource)
			);
		}

		$page = $this->_input->filterSingle('page', XenForo_Input::UINT);
		$perPage = XenForo_Application::getOptions()->resourceUpdatesPerPage;

		$updates = $updateModel->getUpdates(
			$conditions,
			array(
				'likeUserId' => XenForo_Visitor::getUserId(),
				'setUserId' => $resource['user_id'],
				'order' => 'post_date',
				'direction' => 'desc',
				'page' => $page,
				'perPage' => $perPage
			)
		);
		foreach ($updates AS &$update)
		{
			$update = $updateModel->snippetUpdate($update);
		}
		$updates = $updateModel->getAndMergeAttachmentsIntoUpdates($updates);

		$viewParams = array(
			'resource' => $resource,
			'category' => $category,

			'updates' => $updateModel->prepareUpdates($updates, $resource, $category),
			'totalUpdates' => $totalUpdates,

			'canViewImages' => $updateModel->canViewUpdateImages($resource, $category),
			'canViewWarnings' => $this->getModelFromCache('XenForo_Model_User')->canViewWarnings(),

			'page' => $page,
			'perPage' => $perPage
		);
		return $this->_getResourceViewWrapper('updates', $resource, $category,
			$this->responseView('XenResource_ViewPublic_Resource_Updates', 'resource_updates', $viewParams)
		);
	}

	public function actionUpdate()
	{
		$updateFetchOptions = array(
			'likeUserId' => XenForo_Visitor::getUserId()
		);
		$resourceFetchOptions = array(
			'join' => XenResource_Model_Resource::FETCH_VERSION |
				XenResource_Model_Resource::FETCH_ATTACHMENT,
			'watchUserId' => XenForo_Visitor::getUserId()
		);

		$resourceUpdateId = $this->_input->filterSingle('update', XenForo_Input::UINT);
		if (!$resourceUpdateId)
		{
			$resourceUpdateId = $this->_input->filterSingle('resource_update_id', XenForo_Input::UINT);
		}

		list($update, $resource, $category) = $this->_getResourceHelper()->assertUpdateValidAndViewable(
			$resourceUpdateId, $updateFetchOptions, $resourceFetchOptions
		);

		$this->canonicalizeRequestUrl(XenForo_Link::buildPublicLink('resources/update', $resource));

		$updateModel = $this->_getUpdateModel();
		$resourceModel = $this->_getResourceModel();

		$updateId = $update['resource_update_id'];
		$updates = array($updateId => $update);
		$updates = $updateModel->prepareUpdates($updates, $resource, $category);
		$updates = $updateModel->getAndMergeAttachmentsIntoUpdates($updates);
		$update = $updates[$updateId];

		$options = XenForo_Application::getOptions();
		if ($resource['is_fileless']
			&& $options->get('resourceFilelessViewFull', 'limit')
			&& !$resourceModel->canDownloadResource($resource, $category)
		)
		{
			$limit = $options->get('resourceFilelessViewFull', 'length');
			if ($limit > 0) {
				$trimmed = XenForo_Helper_String::wholeWordTrim($update['message'], $limit);
				$isLimited = strlen($trimmed) < strlen($update['message']);
			} else {
				$isLimited = true;
			}
			if ($isLimited)
			{
				return $this->responseNoPermission();
			}
		}

		$viewParams = array(
			'resource' => $resource,
			'category' => $category,
			'update' => $updates[$updateId],
			'canViewImages' => $updateModel->canViewUpdateImages($resource, $category)
		);

		if ($this->_noRedirect())
		{
			// ajax request for just the update
			return $this->responseView('XenResource_ViewPublic_Update_ViewAjax', 'resource_update', $viewParams);
		}
		else
		{
			if ($update['resource_update_id'] == $resource['description_update_id'])
			{
				return $this->responseRedirect(
					XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
					XenForo_Link::buildPublicLink('resources', $resource)
				);
			}

			return $this->_getResourceViewWrapper('updates', $resource, $category,
				$this->responseView('XenResource_ViewPublic_Update_View', 'resource_update_view', $viewParams)
			);
		}
	}

	public function actionAddUpdate()
	{
		list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable();

		if (!$this->_getUpdateModel()->canAddUpdate($resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		$update = array();
		$attachments = array();
		$attachmentHash = null;

		$draft = $this->_getDraftModel()->getDraftByUserKey("resource-$resource[resource_id]", XenForo_Visitor::getUserId());
		if ($draft)
		{
			$extra = @unserialize($draft['extra_data']);
			$update = array(
				'title' => $extra['title'],
				'message' => $draft['message']
			);

			$attachmentHash = $extra['attachment_hash'];

			$attachmentModel = $this->_getAttachmentModel();
			$attachments = $attachmentModel->prepareAttachments(
				$attachmentModel->getAttachmentsByTempHash($attachmentHash)
			);
		}

		$updateModel = $this->_getUpdateModel();

		$viewParams = array(
			'resource' => $resource,
			'category' => $category,
			'update' => $update,
			'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category),

			'message' => isset($update['message']) ? $update['message'] : '',

			'attachmentParams' => $updateModel->getUpdateAttachmentParams(array(), null, $attachmentHash),
			'attachmentConstraints' => $updateModel->getUpdateAttachmentConstraints(),
			'attachments' => $attachments
		);

		return $this->responseView('XenResource_ViewPublic_Update_Add', 'resource_update_add', $viewParams);
	}

	public function actionEditUpdate()
	{
		list($update, $resource, $category) = $this->_getResourceHelper()->assertUpdateValidAndViewable();

		if (!$this->_getUpdateModel()->canEditUpdate($update, $resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		if ($this->_input->inRequest('more_options'))
		{
			$update['message'] = $this->getHelper('Editor')->getMessageText('message', $this->_input);
		}

		$viewParams = array(
			'resource' => $resource,
			'category' => $category,
			'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category),
			'update' => $update,
		);

		if ($this->_input->filterSingle('inline', XenForo_Input::UINT))
		{
			return $this->responseView('XenResource_ViewPublic_Update_EditInline', 'resource_update_edit_inline', $viewParams);
		}
		else
		{
			$resourceModel = $this->_getResourceModel();
			$updateModel = $this->_getUpdateModel();
			$attachmentModel = $this->_getAttachmentModel();

			$attachments = $attachmentModel->getAttachmentsByContentId('resource_update', $update['resource_update_id']);

			$viewParams = $viewParams + array(
				'attachments' => $attachmentModel->prepareAttachments($attachments),
				'attachmentParams' => $updateModel->getUpdateAttachmentParams(),
				'attachmentConstraints' => $updateModel->getUpdateAttachmentConstraints(),
			);

			return $this->responseView('XenResource_ViewPublic_Update_Edit', 'resource_update_edit', $viewParams);
		}
	}

	public function actionSaveUpdate()
	{
		$this->_assertPostOnly();

		if ($this->_input->inRequest('more_options'))
		{
			$this->_request->setParam('inline', false);

			return $this->responseReroute(__CLASS__, 'edit-update');
		}

		if ($updateId = $this->_input->filterSingle('resource_update_id', XenForo_Input::UINT))
		{
			list($update, $resource, $category) = $this->_getResourceHelper()->assertUpdateValidAndViewable();
			if (!$this->_getUpdateModel()->canEditUpdate($update, $resource, $category, $errorPhraseKey))
			{
				throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
			}
		}
		else
		{
			list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable();
			if (!$this->_getUpdateModel()->canAddUpdate($resource, $category, $errorPhraseKey))
			{
				throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
			}
			$update = false;
		}

		$dw = XenForo_DataWriter::create('XenResource_DataWriter_Update');
		if ($updateId)
		{
			$dw->setExistingData($updateId);
		}
		else
		{
			$dw->set('resource_id', $resource['resource_id']);

			if ($category['always_moderate_update']
				&& !XenForo_Visitor::getInstance()->hasPermission('resource', 'approveUnapprove')
			)
			{
				$dw->set('message_state', 'moderated');
			}
		}

		$message = $this->getHelper('Editor')->getMessageText('message', $this->_input);
		$message = XenForo_Helper_String::autoLinkBbCode($message);
		$dw->set('message', $message);

		if (!$this->_input->filterSingle('inline', XenForo_Input::UINT))
		{
			$title = $this->_input->filterSingle('title', XenForo_Input::STRING);
			$dw->set('title', $title);

			$attachmentHash = $this->_input->filterSingle('attachment_hash', XenForo_Input::STRING);
			$dw->setExtraData(XenResource_DataWriter_Update::DATA_ATTACHMENT_HASH, $attachmentHash);
		}

		$dw->save();

		$update = $dw->getMergedData();

		if ($dw->isUpdate() && XenForo_Visitor::getUserId() != $resource['user_id'])
		{
			$basicLog = $this->_getLogChanges($dw);
			if ($basicLog) {
				XenForo_Model_Log::logModeratorAction('resource_update', $update, 'edit', $basicLog, $resource);
			}
		}

		if ($dw->isInsert())
		{
			$this->_getDraftModel()->deleteDraft("resource-$resource[resource_id]");
		}

		if ($this->_noRedirect() && $updateId)
		{
			return $this->responseReroute(__CLASS__, 'update');
		}
		else
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('resources', $resource)
			);
		}
	}

	public function actionDeleteUpdate()
	{
		list($update, $resource, $category) = $this->_getResourceHelper()->assertUpdateValidAndViewable();

		$hardDelete = $this->_input->filterSingle('hard_delete', XenForo_Input::UINT);
		$deleteType = ($hardDelete ? 'hard' : 'soft');

		if (!$this->_getUpdateModel()->canDeleteUpdate($update, $resource, $category, $deleteType, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		if ($this->isConfirmedPost())
		{
			$updateDw = XenForo_DataWriter::create('XenResource_DataWriter_Update');
			$updateDw->setExistingData($update['resource_update_id']);

			if ($hardDelete)
			{
				$updateDw->delete();
				XenForo_Model_Log::logModeratorAction('resource_update', $update, 'delete_hard');
			}
			else
			{
				$reason = $this->_input->filterSingle('reason', XenForo_Input::STRING);
				$updateDw->setExtraData(XenResource_DataWriter_Update::DATA_DELETE_REASON, $reason);
				$updateDw->set('message_state', 'deleted');
				$updateDw->save();

				if (XenForo_Visitor::getUserId() != $resource['user_id'])
				{
					XenForo_Model_Log::logModeratorAction('resource_update', $update, 'delete_soft', array('reason' => $reason));
				}
			}

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('resources', $resource)
			);
		}
		else
		{
			$viewParams = array(
				'update' => $update,
				'resource' => $resource,
				'category' => $category,
				'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category),
				'canHardDelete' => $this->_getUpdateModel()->canDeleteUpdate($update, $resource, $category, 'hard')
			);

			return $this->responseView('XenResource_ViewPublic_Update_Delete', 'resource_update_delete', $viewParams);
		}
	}

	public function actionLikeUpdate()
	{
		list($update, $resource, $category) = $this->_getResourceHelper()->assertUpdateValidAndViewable();

		if (!$this->_getUpdateModel()->canLikeUpdate($update, $resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		$updateId = $update['resource_update_id'];

		$likeModel = $this->_getLikeModel();

		$existingLike = $likeModel->getContentLikeByLikeUser('resource_update', $updateId, XenForo_Visitor::getUserId());

		if ($this->_request->isPost())
		{
			if ($existingLike)
			{
				$latestUsers = $likeModel->unlikeContent($existingLike);
			}
			else
			{
				$latestUsers = $likeModel->likeContent('resource_update', $updateId, $resource['user_id']);
			}

			$liked = ($existingLike ? false : true);

			if ($this->_noRedirect() && $latestUsers !== false)
			{
				$update['likeUsers'] = $latestUsers;
				$update['likes'] += ($liked ? 1 : -1);
				$update['like_date'] = ($liked ? XenForo_Application::$time : 0);

				$viewParams = array(
					'update' => $update,
					'resource' => $resource,
					'liked' => $liked,
				);

				return $this->responseView('XenResource_ViewPublic_Update_LikeConfirmed', '', $viewParams);
			}
			else
			{
				return $this->responseRedirect(
					XenForo_ControllerResponse_Redirect::SUCCESS,
					XenForo_Link::buildPublicLink('resources/update', $resource, array('update' => $update['resource_update_id']))
				);
			}
		}
		else
		{
			$viewParams = array(
				'update' => $update,
				'resource' => $resource,
				'category' => $category,
				'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category),
				'like' => $existingLike,
			);

			return $this->responseView('XenResource_ViewPublic_Update_Like', 'resource_update_like', $viewParams);
		}
	}

	/**
	 * List of everyone that liked this update.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionUpdateLikes()
	{
		list($update, $resource, $category) = $this->_getResourceHelper()->assertUpdateValidAndViewable();

		$likes = $this->_getLikeModel()->getContentLikes('resource_update', $update['resource_update_id']);
		if (!$likes)
		{
			return $this->responseError(new XenForo_Phrase('no_one_has_liked_this_post_yet'));
		}

		$viewParams = array(
			'update' => $update,
			'resource' => $resource,
			'category' => $category,
			'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category),
			'likes' => $likes
		);

		return $this->responseView('XenResource_ViewPublic_Update_Likes', 'resource_update_likes', $viewParams);
	}

	public function actionReportUpdate()
	{
		list($update, $resource, $category) = $this->_getResourceHelper()->assertUpdateValidAndViewable();

		if (!$this->_getUpdateModel()->canReportUpdate($update, $resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		if ($this->isConfirmedPost())
		{
			$reportMessage = $this->_input->filterSingle('message', XenForo_Input::STRING);
			if (!$reportMessage)
			{
				return $this->responseError(new XenForo_Phrase('please_enter_reason_for_reporting_this_message'));
			}

			$this->assertNotFlooding('report');

			$update['resource'] = $resource;
			$update['category'] = $category;

			/* @var $reportModel XenForo_Model_Report */
			$reportModel = XenForo_Model::create('XenForo_Model_Report');
			$reportModel->reportContent('resource_update', $update, $reportMessage);

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('resources', $resource),
				new XenForo_Phrase('thank_you_for_reporting_this_message')
			);
		}
		else
		{
			$viewParams = array(
				'update' => $update,
				'resource' => $resource,
				'category' => $category,
				'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category),
			);

			return $this->responseView('XenResource_ViewPublic_Update_Report', 'resource_update_report', $viewParams);
		}
	}

	public function actionSaveDraft()
	{
		list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable();

		if (!$this->_getUpdateModel()->canAddUpdate($resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		$extra = $this->_input->filter(array(
			'title' => XenForo_Input::STRING,
			'attachment_hash' => XenForo_Input::STRING,
		));
		$message = $this->getHelper('Editor')->getMessageText('message', $this->_input);

		$forceDelete = $this->_input->filterSingle('delete_draft', XenForo_Input::BOOLEAN);
		$draftId = "resource-$resource[resource_id]";

		if (!strlen($message) || $forceDelete)
		{
			$draftSaved = false;
			$draftDeleted = $this->_getDraftModel()->deleteDraft($draftId) || $forceDelete;
		}
		else
		{
			$this->_getDraftModel()->saveDraft($draftId, $message, $extra);
			$draftSaved = true;
			$draftDeleted = false;
		}

		$viewParams = array(
			'resource' => $resource,
			'category' => $category,
			'draftSaved' => $draftSaved,
			'draftDeleted' => $draftDeleted
		);
		$view = $this->responseView('XenResource_ViewPublic_Resource_SaveDraft', '', $viewParams);
		$view->jsonParams = array(
			'draftSaved' => $draftSaved,
			'draftDeleted' => $draftDeleted
		);
		return $view;
	}

	public function actionAddVersion()
	{
		list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable();

		if (!$this->_getVersionModel()->canAddVersion($resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		$update = array();
		$draft = $this->_getDraftModel()->getDraftByUserKey("resource-$resource[resource_id]", XenForo_Visitor::getUserId());
		if ($draft)
		{
			$extra = @unserialize($draft['extra_data']);
			$update = array(
				'title' => $extra['title'],
				'message' => $draft['message'],
				'attachment_hash' => $extra['attachment_hash']
			);
		}

		return $this->_getVersionAddEditResponse(array(), $resource, $category, $update);
	}

	// note: only supports adding a version currently

	protected function _getVersionAddEditResponse(array $version, array $resource, array $category, array $update = array())
	{
		$attachmentModel = $this->_getAttachmentModel();
		$versionModel = $this->_getVersionModel();
		$updateModel = $this->_getUpdateModel();

		$attachmentHash = null;
		$attachments = array();

		if (!empty($version['resource_update_id']))
		{
			$update = $updateModel->getUpdateById($version['resource_update_id']);
			$attachments = $attachmentModel->getAttachmentsByContentId('resource_update', $version['resource_update_id']);
		}
		else
		{
			if (!empty($update['attachment_hash']))
			{
				$attachmentHash = $update['attachment_hash'];
				$attachments = $attachmentModel->prepareAttachments(
					$attachmentModel->getAttachmentsByTempHash($attachmentHash)
				);
			}
		}

		$uploaderId = 'VersionFile_' . md5(uniqid('', true));

		$allowLocal = ($category['allow_local'] || !$resource['download_url']);
		$allowExternal = ($category['allow_external'] || $resource['download_url']);

		$viewParams = array(
			'resource' => $resource,
			'category' => $category,
			'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category),
			'version' => $version,
			'update' => $update,

			'allowLocal' => $allowLocal,
			'allowExternal' => $allowExternal,

			'attachments' => $attachments,
			'attachmentParams' => $updateModel->getUpdateAttachmentParams(array(), null, $attachmentHash),
			'attachmentConstraints' => $updateModel->getUpdateAttachmentConstraints(),

			'message' => isset($update['message']) ? $update['message'] : '',

			'uploaderId' => $uploaderId,
			'fileParams' => array($uploaderId => $versionModel->getVersionFileParams(array(), array(
				'resource_id' => $resource['resource_id'],
				'resource_category_id' => $resource['resource_category_id']
			))),
			'fileConstraints' => array($uploaderId => $versionModel->getVersionFileConstraints()),
		);

		return $this->responseView('XenResource_ViewPublic_Version_Add', 'resource_version_add', $viewParams);
	}

	public function actionSaveVersion()
	{
		list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable();

		// TODO: only handles adding
		if (!$this->_getVersionModel()->canAddVersion($resource, $category, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		$message = $this->getHelper('Editor')->getMessageText('message', $this->_input);
		$message = XenForo_Helper_String::autoLinkBbCode($message);

		$data = $this->_input->filter(array(
			'version_string' => XenForo_Input::STRING,
			'title' => XenForo_Input::STRING,
			'attachment_hash' => XenForo_Input::STRING,
			'file_hash' => XenForo_Input::STRING,
			'resource_file_type' => XenForo_Input::STRING,
			'download_url' => XenForo_Input::STRING
		));

		$hasVersion = ($data['version_string'] !== '');
		$hasUpdate = ($data['title'] !== '' || $message !== '');

		if (!$hasVersion && !$hasUpdate)
		{
			return $this->responseError(new XenForo_Phrase('you_must_provide_either_update_or_new_version'));
		}

		if ($hasVersion)
		{
			$versionDw = XenForo_DataWriter::create('XenResource_DataWriter_Version');
			$versionDw->bulkSet(array(
				'resource_id' => $resource['resource_id'],
				'version_string' => $data['version_string']
			));
			if ($resource['is_fileless'])
			{
				$versionDw->setOption(XenResource_DataWriter_Version::OPTION_IS_FILELESS, true);
			}
			else
			{
				switch ($data['resource_file_type'])
				{
					case 'file':
						if ($category['allow_local'] || !$resource['download_url'])
						{
							$versionDw->setExtraData(XenResource_DataWriter_Version::DATA_ATTACHMENT_HASH, $data['file_hash']);
						}
						break;

					case 'url':
						if ($category['allow_external'] || $resource['download_url'])
						{
							$versionDw->set('download_url', $data['download_url']);
						}
						break;
				}
			}

			if ($category['always_moderate_update']
				&& !XenForo_Visitor::getInstance()->hasPermission('resource', 'approveUnapprove')
			)
			{
				$versionDw->set('version_state', 'moderated');
			}
		}
		else
		{
			$versionDw = false;
		}

		if ($hasUpdate)
		{
			if ($hasVersion)
			{
				$updateDw = $versionDw->getUpdateDw();
			}
			else
			{
				$updateDw =  XenForo_DataWriter::create('XenResource_DataWriter_Update');
				$updateDw->set('resource_id', $resource['resource_id']);
			}

			if ($category['always_moderate_update']
				&& !XenForo_Visitor::getInstance()->hasPermission('resource', 'approveUnapprove')
			)
			{
				$updateDw->set('message_state', 'moderated');
			}

			$updateDw->set('title', $data['title']);
			$updateDw->set('message', $message);
			$updateDw->setExtraData(XenResource_DataWriter_Update::DATA_ATTACHMENT_HASH, $data['attachment_hash']);
		}
		else
		{
			$updateDw = false;
		}

		if ($hasVersion)
		{
			$versionDw->save(); // this will automatically save the update as well if needed
		}
		else if ($hasUpdate)
		{
			$updateDw->save();
		}

		$this->_getDraftModel()->deleteDraft("resource-$resource[resource_id]");

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('resources', $resource)
		);
	}

	public function actionDeleteVersion()
	{
		list($version, $resource, $category) = $this->_getResourceHelper()->assertVersionValidAndViewable();

		$hardDelete = $this->_input->filterSingle('hard_delete', XenForo_Input::UINT);
		$deleteType = ($hardDelete ? 'hard' : 'soft');

		if (!$this->_getVersionModel()->canDeleteVersion($version, $resource, $category, $deleteType, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		if ($this->isConfirmedPost())
		{
			$versionDw = XenForo_DataWriter::create('XenResource_DataWriter_Version');
			$versionDw->setExistingData($version['resource_version_id']);

			if ($hardDelete)
			{
				$versionDw->delete();
				XenForo_Model_Log::logModeratorAction('resource_version', $version, 'delete_hard');
			}
			else
			{
				$reason = $this->_input->filterSingle('reason', XenForo_Input::STRING);
				$versionDw->setExtraData(XenResource_DataWriter_Version::DATA_DELETE_REASON, $reason);
				$versionDw->set('version_state', 'deleted');
				$versionDw->save();

				if (XenForo_Visitor::getUserId() != $resource['user_id'])
				{
					XenForo_Model_Log::logModeratorAction('resource_version', $version, 'delete_soft', array('reason' => $reason));
				}
			}

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('resources', $resource)
			);
		}
		else
		{
			$viewParams = array(
				'resource' => $resource,
				'category' => $category,
				'categoryBreadcrumbs' => $this->_getCategoryModel()->getCategoryBreadcrumb($category),
				'version' => $version,
				'canHardDelete' => $this->_getVersionModel()->canDeleteVersion($version, $resource, $category, 'hard')
			);

			return $this->responseView('XenResource_ViewPublic_Resource_DeleteVersion', 'resource_version_delete', $viewParams);
		}
	}

	/**
	 * Shows a preview .
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionPreview()
	{
		$this->_assertPostOnly();

		$resourceId = $this->_input->filterSingle('resource_id', XenForo_Input::UINT);
		if ($resourceId)
		{
			list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable($resourceId);

			if (!$this->_getUpdateModel()->canAddUpdate($resource, $category, $errorPhraseKey)
				&& !$this->_getResourceModel()->canEditResource($resource, $category, $errorPhraseKey)
			)
			{
				throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
			}
		}
		else
		{
			$resource = false;
			$category = false;

			if (!$this->_getCategoryModel()->canAddResource(null, $key))
			{
				throw $this->getErrorOrNoPermissionResponseException($key);
			}
		}

		$message = $this->getHelper('Editor')->getMessageText('message', $this->_input);
		$message = XenForo_Helper_String::autoLinkBbCode($message);

		$viewParams = array(
			'resource' => $resource,
			'category' => $category,
			'message' => $message
		);

		return $this->responseView('XenResource_ViewPublic_Resource_Preview', 'resource_preview', $viewParams);
	}

	public function actionWatched()
	{
		$this->_assertRegistrationRequired();

		$resourceModel = $this->_getResourceModel();
		$watchModel = $this->_getResourceWatchModel();

		$page = $this->_input->filterSingle('page', XenForo_Input::UINT);
		$perPage = XenForo_Application::get('options')->resourcesPerPage;

		$resources = $watchModel->getResourcesWatchedByUser(XenForo_Visitor::getUserId(),
			array_merge(
				$this->_getResourceListFetchOptions(),
				array(
					'permissionCombinationId' => XenForo_Visitor::getInstance()->permission_combination_id,
					'page' => $page,
					'perPage' => $perPage
				)
			)
		);
		$resources = $this->_getResourceModel()->filterUnviewableResources($resources);

		$totalResources = $watchModel->countResourcesWatchedByUser(XenForo_Visitor::getUserId());

		$this->canonicalizePageNumber($page, $perPage, $totalResources, 'resources/watched');

		$viewParams = array(
			'resources' => $resourceModel->prepareResources($resources),
			'totalResources' => $totalResources,
			'page' => $page,
			'perPage' => $perPage
		);
		return $this->responseView('XenResource_ViewPublic_Resource_Watched', 'resource_watched', $viewParams);
	}

	/**
	* Update selected watched resources (stop watching, change email notification settings).
	*
	* @return XenForo_ControllerResponse_Abstract
	*/
	public function actionWatchedUpdate()
	{
		$this->_assertPostOnly();

		$input = $this->_input->filter(array(
			'resource_ids' => array(XenForo_Input::UINT, 'array' => true),
			'do' => XenForo_Input::STRING
		));

		$watch = $this->_getResourceWatchModel()->getUserResourceWatchByResourceIds(XenForo_Visitor::getUserId(), $input['resource_ids']);

		foreach ($watch AS $resourceWatch)
		{
			$dw = XenForo_DataWriter::create('XenResource_DataWriter_ResourceWatch');
			$dw->setExistingData($resourceWatch, true);

			switch ($input['do'])
			{
				case 'stop':
					$dw->delete();
					break;

				case 'email':
					$dw->set('email_subscribe', 1);
					$dw->save();
					break;

				case 'no_email':
					$dw->set('email_subscribe', 0);
					$dw->save();
					break;
			}
		}

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			$this->getDynamicRedirect(XenForo_Link::buildPublicLink('resources/watched'))
		);
	}

	public function actionWatchedCategories()
	{
		$categoryModel = $this->_getCategoryModel();
		$watchModel = $this->_getCategoryWatchModel();
		$visitor = XenForo_Visitor::getInstance();

		$categoriesWatched = $watchModel->getUserCategoryWatchByUser($visitor['user_id']);

		if ($categoriesWatched)
		{
			$viewableCategories = $this->_getCategoryModel()->getViewableCategories();
			$categoryList = $categoryModel->groupCategoriesByParent($viewableCategories);
			$categoryList = $categoryModel->applyRecursiveCountsToGrouped($categoryList);

			$categories = $categoryModel->ungroupCategories($categoryList, array_keys($categoriesWatched));
		}
		else
		{
			$categories = array();
		}

		$viewParams = array(
			'categories' => $categoryModel->prepareCategories($categories),
			'categoriesWatched' => $categoriesWatched
		);

		return $this->responseView('XenResource_ViewPublic_Resource_WatchedCategories', 'resource_watched_categories', $viewParams);
	}

	public function actionWatchedCategoriesUpdate()
	{
		$this->_assertPostOnly();

		$input = $this->_input->filter(array(
			'category_ids' => array(XenForo_Input::UINT, 'array' => true),
			'do' => XenForo_Input::STRING
		));

		$watch = $this->_getCategoryWatchModel()->getUserCategoryWatchByCategoryIds(XenForo_Visitor::getUserId(), $input['category_ids']);

		foreach ($watch AS $categoryWatch)
		{
			$dw = XenForo_DataWriter::create('XenResource_DataWriter_CategoryWatch');
			$dw->setExistingData($categoryWatch, true);

			switch ($input['do'])
			{
				case 'stop':
					$dw->delete();
					break;

				case 'email':
					$dw->set('send_email', 1);
					$dw->save();
					break;

				case 'no_email':
					$dw->set('send_email', 0);
					$dw->save();
					break;

				case 'alert':
					$dw->set('send_alert', 1);
					$dw->save();
					break;

				case 'no_alert':
					$dw->set('send_alert', 0);
					$dw->save();
					break;

				case 'include_children':
					$dw->set('include_children', 1);
					$dw->save();
					break;

				case 'no_include_children':
					$dw->set('include_children', 0);
					$dw->save();
					break;
			}
		}

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			$this->getDynamicRedirect(XenForo_Link::buildPublicLink('resources/watched-categories'))
		);
	}

	public static function getSessionActivityDetailsForList(array $activities)
	{
		$resourceIds = array();
		foreach ($activities AS $activity)
		{
			if (!empty($activity['params']['resource_id']))
			{
				$resourceIds[$activity['params']['resource_id']] = intval($activity['params']['resource_id']);
			}
		}

		$resourceData = array();

		if ($resourceIds)
		{
			/* @var $resourceModel XenResource_Model_Resource */
			$resourceModel = XenForo_Model::create('XenResource_Model_Resource');

			$resources = $resourceModel->getResourcesByIds($resourceIds, array(
				'join' => XenResource_Model_Resource::FETCH_CATEGORY,
				'permissionCombinationId' => XenForo_Visitor::getInstance()->permission_combination_id
			));
			foreach ($resources AS $resource)
			{
				$categoryPermissions = XenForo_Permission::unserializePermissions($resource['category_permission_cache']);

				if ($resourceModel->canViewResourceAndContainer($resource, $resource, $null, null, $categoryPermissions))
				{
					$resource['title'] = XenForo_Helper_String::censorString($resource['title']);

					$resourceData[$resource['resource_id']] = array(
						'title' => $resource['title'],
						'url' => XenForo_Link::buildPublicLink('resources', $resource)
					);
				}
			}
		}

		$output = array();
		foreach ($activities AS $key => $activity)
		{
			$resource = false;
			if (!empty($activity['params']['resource_id']))
			{
				$resourceId = $activity['params']['resource_id'];
				if (isset($resourceData[$resourceId]))
				{
					$resource = $resourceData[$resourceId];
				}
			}

			if ($resource)
			{
				$output[$key] = array(
					new XenForo_Phrase('viewing_resource'),
					$resource['title'],
					$resource['url'],
					''
				);
			}
			else
			{
				$output[$key] = new XenForo_Phrase('viewing_resource');
			}
		}

		return $output;
	}

	protected function _getLogChanges(XenForo_DataWriter $dw)
	{
		$newData = $dw->getMergedNewData();
		$oldData = $dw->getMergedExistingData();
		$changes = array();

		foreach ($newData AS $key => $newValue)
		{
			if (isset($oldData[$key]))
			{
				$changes[$key] = $oldData[$key];
			}
		}

		return $changes;
	}






	/**
	 * @return XenResource_ControllerHelper_Resource
	 */
	protected function _getResourceHelper()
	{
		return $this->getHelper('XenResource_ControllerHelper_Resource');
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
	 * @return XenResource_Model_ResourceWatch
	 */
	protected function _getResourceWatchModel()
	{
		return $this->getModelFromCache('XenResource_Model_ResourceWatch');
	}

	/**
	 * @return XenResource_Model_CategoryWatch
	 */
	protected function _getCategoryWatchModel()
	{
		return $this->getModelFromCache('XenResource_Model_CategoryWatch');
	}

	/**
	 * @return XenResource_Model_Update
	 */
	protected function _getUpdateModel()
	{
		return $this->getModelFromCache('XenResource_Model_Update');
	}

	/**
	* @return XenResource_Model_Rating
	*/
	protected function _getRatingModel()
	{
		return $this->getModelFromCache('XenResource_Model_Rating');
	}

	/**
	 * @return XenResource_Model_Version
	 */
	protected function _getVersionModel()
	{
		return $this->getModelFromCache('XenResource_Model_Version');
	}

	/**
	 * @return XenResource_Model_ResourceField
	 */
	protected function _getFieldModel()
	{
		return $this->getModelFromCache('XenResource_Model_ResourceField');
	}

	/**
	 * @return XenResource_Model_Prefix
	 */
	protected function _getPrefixModel()
	{
		return $this->getModelFromCache('XenResource_Model_Prefix');
	}

	/**
	 * @return XenForo_Model_Attachment
	 */
	protected function _getAttachmentModel()
	{
		return $this->getModelFromCache('XenForo_Model_Attachment');
	}

	/**
	 * @return XenForo_Model_Like
	 */
	protected function _getLikeModel()
	{
		return $this->getModelFromCache('XenForo_Model_Like');
	}

	/**
	 * @return XenForo_Model_Draft
	 */
	protected function _getDraftModel()
	{
		return $this->getModelFromCache('XenForo_Model_Draft');
	}
}