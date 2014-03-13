<?php

class XenResource_ViewPublic_Resource_Category extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$categoriesGrouped = $this->_params['categoriesGrouped'];

		$categorySidebarHtml = '';
		$parentIds = array_keys($this->_params['categoryBreadcrumbs']);
		$parentIds = array_reverse($parentIds);
		$parentIds[] = 0;
		array_unshift($parentIds, $this->_params['category']['resource_category_id']);
		$lastParentId = $this->_params['category']['resource_category_id'];

		foreach ($parentIds AS $parentId)
		{
			if (empty($categoriesGrouped[$parentId]))
			{
				continue;
			}

			$categorySidebarHtml = $this->_renderer->createTemplateObject('resource_category_sidebar_list', array(
				'categories' => $categoriesGrouped[$parentId],
				'category' => $this->_params['category'],
				'childCategoryHtml' => $categorySidebarHtml,
				'showChildId' => $lastParentId
			));
			$lastParentId = $parentId;
		}

		$this->_params['categorySidebarHtml'] = $categorySidebarHtml;
	}
}