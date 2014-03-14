<?php

class EWRporta_Block_NewsCategories extends XenForo_Model
{
	public function getModule(&$options)
	{
		$categories = $this->getModelFromCache('EWRporta_Model_Categories')->getCategoriesCount();
		
		if ($options['restrict'])
		{
			foreach ($categories AS $key => $category)
			{
				if ($category['category_type'] != 'major')
				{
					unset($categories[$key]);
				}
			}
		}

		return $categories;
	}
}