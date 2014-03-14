<?php

class EWRporta_Block_Twitter extends XenForo_Model
{
	public function getModule(&$options)
	{
		$chome = array();
	
		if ($options['features']['scroll']) { $chrome[] = 'noscrollbar'; }
		if ($options['features']['header']) { $chrome[] = 'noheader'; }
		if ($options['features']['footer']) { $chrome[] = 'nofooter'; }
		if ($options['features']['border']) { $chrome[] = 'noborders'; }
		if ($options['features']['transparent']) { $chrome[] = 'transparent'; }
		
		$options['features'] = implode(' ', $chrome);

		return;
	}
}