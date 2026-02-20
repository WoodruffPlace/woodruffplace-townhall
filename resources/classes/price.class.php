<?php
class Price
{
	private $db;
	private $priceID;
	private $amount;
	private $description;
	private $description_invoice;
	private $field_request_label;
	private $attendance_min;
	private $productID;
	private $stripe_price_id;
	private $stripe_price_lookup;

	/**
	 *  Constructor
	 */
	function __construct($priceID)
	{
		global $db;
		$this->db = $db;
		$this->priceID = $priceID;
		try
		{
			/* Populate object values */
			$vars = $this->price_setinfo();
			if (isset($vars))
			{
				$this->priceID				= $vars[0]['priceID'];
				$this->amount				= $vars[0]['amount'];
				$this->description			= $vars[0]['description'];
				$this->description_invoice	= $vars[0]['description_invoice'];
				$this->field_request_label	= $vars[0]['field_request_attendance_short_label'];
				$this->attendance_min		= $vars[0]['attendance_min_number'];
				$this->productID			= $vars[0]['productID'];
				$this->stripe_price_id		= $vars[0]['stripe_price_id'];
				$this->stripe_price_lookup	= (!empty($vars[0]['stripe_price_lookup']) ? $vars[0]['stripe_price_lookup'] : NULL);
			}
		}
		catch (Exception $e)
		{
			throw new Exception( 'Error: class: "Price" requires valid Price ID', 0, $e);
		}
	}


	/**
	 *  Provide a product's info attributes
	 */
	function price_get($attribute = NULL)
	{
		switch ($attribute)
		{
			case 'priceID':
				if (isset($this->priceID)){ return $this->priceID; }
			break;
			case 'amount':
				if (isset($this->amount)){ return $this->amount; }
			break;
			case 'description':
				if (isset($this->description)){ return $this->description; }
			break;
			case 'description_invoice':
				if (isset($this->description_invoice)){ return $this->description_invoice; }
			break;
			case 'field_request_label':
				if (isset($this->field_request_label)){ return $this->field_request_label; }
			break;
			case 'attendance_min':
				if (isset($this->attendance_min)){ return $this->attendance_min; }
			break;
			case 'stripe_price_lookup':
				if (isset($this->stripe_price_lookup)){ return $this->stripe_price_lookup; }
			break;
			case 'stripe_price_id':
				if (isset($this->stripe_price_id)){ return $this->stripe_price_id; }
			break;
			case 'productID':
				if (isset($this->productID)){ return $this->productID; }
			break;
		}
	}

	/**
	 *  Sets this object's info from the data store
	 */
	private function price_setinfo()
	{
		global $db;
		if (!empty($this->priceID))
		{
			$query = "SELECT * FROM prices WHERE priceID = '".$this->priceID."'";
			$result = $db->fetch_assoc($db->query($query));
			return $result;
		}
	}


	/* * * * * * * * * * * * * * *
	 *
	 *  Class/static methods
	 *
	 * * * * * * * * * * * * * * */


	static function price_get_amount($priceID)
	{
		$price = new Price($priceID);
		return $price->price_get('amount');
	}
}
