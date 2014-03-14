<?php

class XenResource_Listener_Proxy_ModelUser extends XFCP_XenResource_Listener_Proxy_ModelUser
{
	public function prepareUserOrderOptions(array &$fetchOptions, $defaultOrderSql = '')
	{
		$choices = array(
			'resource_count' => 'user.resource_count'
		);
		$order = $this->getOrderByClause($choices, $fetchOptions);
		if ($order)
		{
			return $order;
		}

		return parent::prepareUserOrderOptions($fetchOptions, $defaultOrderSql);
	}
}