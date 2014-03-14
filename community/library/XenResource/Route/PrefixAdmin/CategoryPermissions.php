<?php

/**
 * Route prefix handler for resource categories in the admin control panel.
 */
class XenResource_Route_PrefixAdmin_CategoryPermissions extends XenForo_Route_PrefixAdmin_Nodes
{
	/**
	 * Match a specific route for an already matched prefix.
	 *
	 * @see XenForo_Route_Interface::match()
	 */
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		$action = $router->resolveActionWithIntegerParam($routePath, $request, 'resource_category_id', 'categoryOptions');
		return $router->getRouteMatch('XenResource_ControllerAdmin_CategoryPermission', $action, 'resourceCategories');
	}

	/**
	 * Method to build a link to the specified page/action with the provided
	 * data and params.
	 *
	 * @see XenForo_Route_BuilderInterface
	 */
	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'resource_category_id', 'category_title');
	}
}