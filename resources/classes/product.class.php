<?php
class Product
{
	private $db;
	private $productID;
	private $name;
	private $description;
	private $stripe_product_id;

	/**
	 *  Constructor
	 */
	function __construct($productID)
	{
		global $db;
		$this->db = $db;
		$this->productID = $productID;
		try
		{
			/* Populate object values */
			$vars = $this->product_setinfo();
			if (isset($vars))
			{
				$this->productID			= $vars[0]['productID'];
				$this->name					= $vars[0]['name'];
				$this->description			= $vars[0]['description'];
				$this->stripe_product_id	= $vars[0]['stripe_product_id'];
			}
		}
		catch (Exception $e)
		{
			throw new Exception( 'Error: class: "Product" requires valid Product ID', 0, $e);
		}
	}

	/**
	 *  Provide a product's info attributes
	 */
	function product_get($attribute = NULL)
	{
		switch ($attribute)
		{
			case 'productID':
				if (isset($this->productID)){ return $this->productID; }
			break;
			case 'name':
				if (isset($this->name)){ return $this->name; }
			break;
			case 'description':
				if (isset($this->description)){ return $this->description; }
			break;
			case 'stripe_product_id':
				if (isset($this->stripe_product_id)){ return $this->stripe_product_id; }
			break;
		}
	}

	/**
	 *  Get the priceIDs associated with a particular product
	 */
	function product_get_prices()
	{
		$query = "SELECT priceID FROM prices WHERE productID = '".$this->productID."' AND active = '1'";
		$result = $this->db->fetch_assoc($this->db->query($query));
		// Create return array
		$return = [];
		// Loop and fill
		foreach ($result as $row)
		{
			$return[] = $row['priceID'];
		}
		// Return
		return $return;
	}

	/**
	 *  Sets this object's info from the data store
	 */
	private function product_setinfo()
	{
		global $db;
		if (!empty($this->productID))
		{
			$query = "SELECT * FROM `products` WHERE productID = '".$this->productID."'";
			$result = $db->fetch_assoc($db->query($query));
			return $result;
		}
	}


	/* * * * * * * * * * * * * * *
	 *
	 *  Class/static methods
	 *
	 * * * * * * * * * * * * * * */

	// Get a value from the config
	public static function product_get_config($product)
	{
		global $config;
		return match ($product)
		{
			"alcohol" => $config['products']['alcohol'],
			"rental" => $config['products']['rental'],
		};
	}
}
