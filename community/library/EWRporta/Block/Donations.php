<?php

class EWRporta_Block_Donations extends XenForo_Model
{
	public function getModule(&$options)
	{
	//	$options['payPalUrl'] = 'https://www.sandbox.paypal.com/cgi-bin/websrc';
		$options['payPalUrl'] = 'https://www.paypal.com/cgi-bin/websrc';
		$options['amounts'] = explode("\n", $options['amounts']);

		foreach ($options['amounts'] AS &$amount)
		{
			$relation = explode('=', $amount);
			$amount = array(
				'value' => (float)$relation[0],
				'text' => isset($relation[1]) ? $relation[1] : $relation[0],
			);
		}

		foreach ($options['drives'] AS &$donation)
		{
			$donation['sum'] = $this->getSumByDriveId($donation['id']);
			$donation['drive_id'] = $donation['id'];

			if ($donation['goal'])
			{
				$donation['data1'] = min(100, floor(($donation['sum'] / $donation['goal']) * 100));
				$donation['data2'] = 100 - $donation['data1'];				
			}
		}

		return;
	}

	public function getSumByDriveId($driveId)
	{
		if (!$donation = $this->_getDb()->fetchRow('
			SELECT SUM(amount) AS amount
				FROM EWRporta_donations
			WHERE drive_id = ?
				AND transaction_type IN (\'payment\', \'cancel\')
		', $driveId))
		{
			$donation = array('amount' => 0);
		}

		return money_format('%i', $donation['amount']);
	}

	public function getDonationsByDriveId($driveId)
	{
		if (!$donations = $this->_getDb()->fetchAll("
			SELECT *
				FROM EWRporta_donations
				LEFT JOIN xf_user ON (xf_user.user_id = EWRporta_donations.user_id)
			WHERE drive_id = ?
				AND transaction_type = 'payment'
			ORDER BY log_date DESC
		", $driveId))
		{
			return array();
		}

		foreach ($donations AS &$donation)
		{
			$donation['amount'] = money_format('%i', $donation['amount']);
		}

		return $donations;
	}

	public function getDriveById($driveId)
	{
		$drives = $this->_getDb()->fetchRow("
			SELECT *
				FROM EWRporta_options
			WHERE option_id = ?
		", 'donations_drives');

		$drives = unserialize($drives['option_value']);

		foreach ($drives AS $drive)
		{
			if ($drive['id'] == $driveId)
			{
				$drive['sum'] = $this->getSumByDriveId($driveId);
				return $drive;
			}
		}

		return array();
	}

	protected $_request;
	protected $_input;
	protected $_filtered = null;
	protected $_driveId;
	protected $_userId;

	public function initCallbackHandling(Zend_Controller_Request_Http $request)
	{
		$this->_request = $request;
		$this->_input = new XenForo_Input($request);

		$this->_filtered = $this->_input->filter(array(
			'test_ipn' => XenForo_Input::UINT,
			'business' => XenForo_Input::STRING,
			'txn_type' => XenForo_Input::STRING,
			'txn_id' => XenForo_Input::STRING,
			'mc_currency' => XenForo_Input::STRING,
			'mc_gross' => XenForo_Input::UNUM,
			'payment_status' => XenForo_Input::STRING,
			'custom' => XenForo_Input::STRING,
		));
	}

	public function validateRequest(&$errorString)
	{
		try
		{
			if ($this->_filtered['test_ipn'] && XenForo_Application::debugMode())
			{
				$validator = XenForo_Helper_Http::getClient('http://www.sandbox.paypal.com/cgi-bin/webscr');
			}
			else
			{
				$validator = XenForo_Helper_Http::getClient('http://www.paypal.com/cgi-bin/webscr');
			}
			$validator->setParameterPost('cmd', '_notify-validate');
			$validator->setParameterPost($_POST);
			$validatorResponse = $validator->request('POST');

			if (!$validatorResponse || $validatorResponse->getBody() != 'VERIFIED' || $validatorResponse->getStatus() != 200)
			{
				$errorString = 'Request not validated';
				return false;
			}
		}
		catch (Zend_Http_Client_Exception $e)
		{
			$errorString = 'Connection to PayPal failed';
			return false;
		}

		return true;
	}

	public function validatePreConditions(&$errorString)
	{
		$itemParts = explode(',', $this->_filtered['custom'], 4);
		if (count($itemParts) != 4)
		{
			$errorString = 'Invalid item (custom)';
			return false;
		}

		list($userId, $driveId, $validationType, $validation) = $itemParts;
		$user = XenForo_Model::create('XenForo_Model_User')->getFullUserById($userId);
		$this->_userId = $userId;
		$this->_driveId = $driveId;

		if ($this->_userId)
		{
			$tokenParts = explode(',', $validation);
			if (count($tokenParts) != 3 || sha1($tokenParts[1] . $user['csrf_token']) != $tokenParts[2])
			{
				$errorString = 'Invalid validation';
				return false;
			}
		}

		if (!$this->_filtered['txn_id'])
		{
			$errorString = 'No txn_id';
			return false;
		}

		$transaction = $this->getProcessedTransactionLog($this->_filtered['txn_id']);
		if ($transaction)
		{
			$errorString = 'Transaction already processed';
			return false;
		}

		return true;
	}

	public function processTransaction()
	{
		switch ($this->_filtered['txn_type'])
		{
			case 'web_accept':
				if ($this->_filtered['payment_status'] == 'Completed')
				{
					return array('payment', 'Donation received');
				}
				break;
		}

		if ($this->_filtered['payment_status'] == 'Refunded' || $this->_filtered['payment_status'] == 'Reversed')
		{
			return array('cancel', 'Donation refunded/reversed');
		}

		return array('info', 'OK, no action');
	}

	public function getProcessedTransactionLog($transactionId)
	{
		if ($transactionId === '')
		{
			return array();
		}

		return $this->fetchAllKeyed('
			SELECT *
			FROM EWRporta_donations
			WHERE transaction_id = ?
				AND transaction_type IN (\'payment\', \'cancel\')
			ORDER BY log_date
		', 'donation_id', $transactionId);
	}

	public function logProcessorCallback($driveId, $userId, $transactionId, $transactionType, $message, array $details)
	{
		$dw = XenForo_DataWriter::create('EWRporta_Block_DataWriter_Donations');
		$dw->bulkSet(array(
			'drive_id' => $driveId,
			'amount' => $details['mc_gross'],
			'user_id' => $userId,
			'transaction_id' => $transactionId,
			'transaction_type' => $transactionType,
			'message' => substr($message, 0, 255),
			'transaction_details' => serialize($details),
			'log_date' => XenForo_Application::$time
		));
		$dw->save();

		return $dw->get('donation_id');
	}

	public function getLogDetails()
	{
		$details = $_POST;
		$details['_callbackIp'] = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false);

		return $details;
	}

	public function log($type, $message, array $extra)
	{
		if (!$driveId = $this->_driveId) { return false; }
		$userId = $this->_userId;
		$transactionId = $this->_filtered['txn_id'];
		$details = $this->getLogDetails() + $extra;

		$this->logProcessorCallback($driveId, $userId, $transactionId, $type, $message, $details);
	}
}