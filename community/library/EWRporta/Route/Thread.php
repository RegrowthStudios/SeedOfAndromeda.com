<?php

class EWRporta_Route_Thread extends XFCP_EWRporta_Route_Thread
{
	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		if (!empty($data['format']))
		{
			$extraParams['format'] = $data['format'];
		}

		return parent::buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, $extraParams);
	}
}