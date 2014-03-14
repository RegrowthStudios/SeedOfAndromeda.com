<?php

/**
 * Route prefix handler
 */
class XenResource_Route_Prefix_Resources implements XenForo_Route_Interface
{
	protected $_subComponents = array(
		'categories' => array(
			'intId' => 'resource_category_id',
			'title' => 'category_title',
			'actionPrefix' => 'category'
		),
		'authors' => array(
			'intId' => 'user_id',
			'title' => 'username',
			'controller' => 'XenResource_ControllerPublic_Author'
		),
		'inline-mod' => array(
			'controller' => 'XenResource_ControllerPublic_ResourceInlineMod'
		)
	);

	/**
	 * Match a specific route for an already matched prefix.
	 *
	 * @see XenForo_Route_Interface::match()
	 */
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		$controller = 'XenResource_ControllerPublic_Resource';
		$action = $router->getSubComponentAction($this->_subComponents, $routePath, $request, $controller);
		if ($action === false)
		{
			$action = $router->resolveActionWithIntegerParam($routePath, $request, 'resource_id');
		}

		return $router->getRouteMatch($controller, $action, 'resources');
	}

	/**
	 * Method to build a link to the specified page/action with the provided
	 * data and params.
	 *
	 * @see XenForo_Route_BuilderInterface
	 */
	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		$link = XenForo_Link::buildSubComponentLink($this->_subComponents, $outputPrefix, $action, $extension, $data);
		if (!$link)
		{
			if ($data && isset($data['resource_title']))
			{
				$data['title'] = $data['resource_title'];
			}

			if (isset($extraParams['review']) && is_array($extraParams['review']))
			{
				$extraParams['resource_rating_id'] = $extraParams['review']['resource_rating_id'];
				unset($extraParams['review']);
			}

			$link = XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'resource_id', 'title');
		}
		return $link;
	}
}