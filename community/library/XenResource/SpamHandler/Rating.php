<?php

class XenResource_SpamHandler_Rating extends XenForo_SpamHandler_Abstract
{
	/**
	 * Checks that the options array contains a non-empty 'delete_messages' key
	 *
	 * @param array $user
	 * @param array $options
	 *
	 * @return boolean
	 */
	public function cleanUpConditionCheck(array $user, array $options)
	{
		return !empty($options['delete_messages']);
	}

	/**
	 * @see XenForo_SpamHandler_Abstract::cleanUp()
	 */
	public function cleanUp(array $user, array &$log, &$errorKey)
	{
		/** @var $ratingModel XenResource_Model_Rating */
		$ratingModel = $this->getModelFromCache('XenResource_Model_Rating');
		$ratings = $ratingModel->getRatings(array(
			'user_id' => $user['user_id'],
			'moderated' => true,
			'deleted' => true
		));

		if ($ratings)
		{
			$ratingIds = array_keys($ratings);

			$deleteType = (XenForo_Application::get('options')->spamMessageAction == 'delete' ? 'hard' : 'soft');

			$log['resource_rating'] = array(
				'deleteType' => $deleteType,
				'ratingIds' => $ratings
			);

			foreach ($ratingIds AS $ratingId)
			{
				$dw = XenForo_DataWriter::create('XenResource_DataWriter_Rating', XenForo_DataWriter::ERROR_SILENT);
				if ($dw->setExistingData($ratingId))
				{
					if ($deleteType == 'hard')
					{
						$dw->delete();
					}
					else
					{
						$dw->set('rating_state', 'deleted');
						$dw->save();
					}
				}
			}
		}

		return true;
	}

	/**
	 * @see XenForo_SpamHandler_Abstract::restore()
	 */
	public function restore(array $log, &$errorKey = '')
	{
		if ($log['deleteType'] == 'soft')
		{
			foreach ($log['ratingIds'] AS $ratingId)
			{
				$dw = XenForo_DataWriter::create('XenResource_DataWriter_Rating', XenForo_DataWriter::ERROR_SILENT);
				if ($dw->setExistingData($ratingId))
				{
					$dw->set('rating_state', 'visible');
					$dw->save();
				}
			}
		}

		return true;
	}
}