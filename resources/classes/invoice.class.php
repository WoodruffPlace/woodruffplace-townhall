<?php
class Invoice
{
	private $db;
	private $invoiceID;
	private $requestID;
	private $stripe_invoice_id;
	private $status;

	/**
	 *  Constructor
	 */
	function __construct()
	{
		global $db;
		$this->db = $db;
		$this->invoiceID = $invoiceID;
		try
		{
			/* Populate object values */
			$vars = $this->price_setinfo();
			if (isset($vars))
			{
				$this->requestID			= $vars[0]['requestID'];
				$this->stripe_invoice_id	= $vars[0]['stripe_invoice_id'];
				$this->status				= $vars[0]['status'];
			}
		}
		catch (Exception $e)
		{
			throw new Exception( 'Error: class: "Invoice" requires valid Invoice ID', 0, $e);
		}
	}


	/**
	 *  Provide a product's info attributes
	 */
	function invoice_get($attribute = NULL)
	{
		switch ($attribute)
		{
			case 'invoiceID':
				if (isset($this->invoiceID)){ return $this->invoiceID; }
			break;
			case 'requestID':
				if (isset($this->requestID)){ return $this->requestID; }
			break;
			case 'stripe_invoice_id':
				if (isset($this->stripe_invoice_id)){ return $this->stripe_invoice_id; }
			break;
			case 'status':
				if (isset($this->status)){ return $this->status; }
			break;
		}
	}

	/**
	 *  Sets this object's info from the data store
	 */
	private function invoice_setinfo()
	{
		global $db;
		if (!empty($this->priceID))
		{
			$query = "SELECT * FROM invoices WHERE invoiceID = '".$this->invoiceID."'";
			$result = $db->fetch_assoc($db->query($query));
			return $result;
		}
	}

	/**
	 *  Updates columns in the db for this invoice
	 */
	function invoice_update($column, $value)
	{
		global $db;
		$query = "UPDATE invoices SET ".$column." = '".$value."' WHERE invoiceID = '".$this->invoiceID."'";
		$result = $db->query($query);
		// Reset the object
		$this->invoice_setinfo();
		return $result;
	}


	/* * * * * * * * * * * * * * *
	 *
	 *  Class/static methods
	 *
	 * * * * * * * * * * * * * * */

	/**
	 *  Updates columns in the db for this invoice, given a stripe invoice_id
	 */
	public static function invoice_update_status($status, $stripe_invoice_id)
	{
		// Update the db
		global $db;
		$query = "UPDATE invoices SET status = '".$status."' WHERE stripe_invoice_id = '".$stripe_invoice_id."'";
		$result = $db->query($query);

		// Retrieve and return the requestID
		$query = "SELECT requestID FROM invoices WHERE stripe_invoice_id = '".$stripe_invoice_id."'";
		$result = $db->fetch_row($db->query($query));
		return $result[0];
	}

	// Create a new invoice record locally
	public static function invoice_create($requestID, $stripe_invoice_id, $status)
	{
		global $db;
		$data = $db->safe_insert(
		"INSERT INTO invoices (requestID, stripe_invoice_id, status)
		VALUES (?,?,?)",'iss',
		$requestID, $stripe_invoice_id, $status);
		if (!empty($db->last_id()))
		{
			return $db->last_id();
		}
		else
		{
			return FALSE;
		}
	}


}
