<?php

class EWRporta_Route_Articles implements XenForo_Route_Interface
{
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		$action = $router->resolveActionWithStringParam($routePath, $request, 'category_slug');
		$action = $router->resolveActionAsPageNumber($action, $request);
		return $router->getRouteMatch('EWRporta_ControllerPublic_Articles', 'index', 'portal');
	}

	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		$action = XenForo_Link::getPageNumberAsAction($action, $extraParams);
		return XenForo_Link::buildBasicLinkWithStringParam($outputPrefix, $action, $extension, $data, 'category_slug');
	}
}