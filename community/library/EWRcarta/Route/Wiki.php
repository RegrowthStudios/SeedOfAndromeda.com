<?php

class EWRcarta_Route_Wiki implements XenForo_Route_Interface
{
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		$components = explode('/', $routePath);
		$subPrefix = strtolower(array_shift($components));

		$controllerName = '';
		$action = '';
		$intParams = 'page_id';
		$strParams = '';
		$slice = false;

		switch ($subPrefix)
		{
			case 'special':		if (!empty($components[1]) && ($components[0] == 'edit-template' || $components[0] == 'delete-template'))
								{
									unset($components[1]);
								}
								$controllerName = '_Special';									$slice = true;	break;
			case 'archive':		$controllerName = '_Archive';	$intParams = 'history_id';		$slice = true;	break;
			default : 											$strParams = 'page_slug';
		}

		$routePathAction = implode('/', array_slice(($slice ? $components : explode('/', $routePath)), 0, 2)).'/';
		$routePathAction = str_replace('//', '/', $routePathAction);

		if ($strParams)
		{
			$action = $router->resolveActionWithStringParam($routePathAction, $request, $strParams);
		}
		else
		{
			$action = $router->resolveActionWithIntegerParam($routePathAction, $request, $intParams);
		}

		$action = $router->resolveActionAsPageNumber($action, $request);
		return $router->getRouteMatch('EWRcarta_ControllerPublic_Wiki'.$controllerName, $action, 'wiki', $routePath);
	}

	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		$components = explode('/', $action);
		$subPrefix = strtolower(array_shift($components));

		$intParams = '';
		$strParams = '';
		$title = '';
		$slice = false;

		switch ($subPrefix)
		{
			case 'special':		$subPrefix = strtolower(array_shift($components));
								if ($subPrefix == 'edit-template' || $subPrefix == 'delete-template')
								{
									$outputPrefix .= '/special';
									$strParams = 'template_name';
								}
								$slice = true;	break;
			case 'archive':		$intParams = 'history_id';		$slice = true;	break;
			default:			$strParams = 'page_slug';
		}

		if ($slice)
		{
			$outputPrefix .= '/'.$subPrefix;
			$action = implode('/', $components);
		}

		$action = XenForo_Link::getPageNumberAsAction($action, $extraParams);

		if ($strParams)
		{
			return XenForo_Link::buildBasicLinkWithStringParam($outputPrefix, $action, $extension, $data, $strParams);
		}
		else
		{
			return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, $intParams, $title);
		}
	}
}