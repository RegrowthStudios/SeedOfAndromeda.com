<?php

class EWRporta_Route_EWRporta implements XenForo_Route_Interface
{
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		$components = explode('/', $routePath);
		$subPrefix = strtolower(array_shift($components));

		$strParams = '';
		$slice = false;

		switch ($subPrefix)
		{
			case 'options':		$controllerName = '_Options';		$strParams = 'option_id';		$slice = true;	break;
			case 'layouts':		$controllerName = '_Layouts';		$strParams = 'layout_id';		$slice = true;	break;
			case 'blocks':		$controllerName = '_Blocks';		$strParams = 'block_id';		$slice = true;	break;
			case 'categories':	$controllerName = '_Categories';	$strParams = 'category_slug';	$slice = true;	break;
			default:			$controllerName = '_Blocks';
		}

		$routePathAction = ($slice ? implode('/', array_slice($components, 0, 2)) : $routePath);

		$action = $router->resolveActionWithStringParam($routePathAction, $request, $strParams);
		return $router->getRouteMatch('EWRporta_ControllerAdmin'.$controllerName, $action, 'EWRporta', $routePath);
	}

	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		$components = explode('/', $action);
		$subPrefix = strtolower(array_shift($components));

		$strParams = '';
		$title = '';
		$slice = false;

		switch ($subPrefix)
		{
			case 'options':		$strParams = 'option_id';		$slice = true;	break;
			case 'layouts':		$strParams = 'layout_id';		$slice = true;	break;
			case 'blocks':		$strParams = 'block_id';		$slice = true;	break;
			case 'categories':	$strParams = 'category_slug';	$slice = true;	break;
		}

		if ($slice)
		{
			$outputPrefix .= '/'.$subPrefix;
			$action = implode('/', $components);
		}

		$action = XenForo_Link::getPageNumberAsAction($action, $extraParams);
		return XenForo_Link::buildBasicLinkWithStringParam($outputPrefix, $action, $extension, $data, $strParams);
	}
}