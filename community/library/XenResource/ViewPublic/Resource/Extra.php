<?php

class XenResource_ViewPublic_Resource_Extra extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		XenForo_Application::set('view', $this);
	}
}