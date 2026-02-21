<?php
class Event
{
	// Set class variables
	private $db;
	private $eventID;
	private $requestID;
	private $title;
	private $event_start;
	private $event_end;
	private $fee_rental;
	private $fee_alcohol;
	private $fee_waiver_rental;
	private $fee_waiver_alcohol;

	// Constructor

	function __construct($eventID = NULL, $db = NULL)
	{
		if ($db == null)
		{
			global $db;
			$this->db = $db;
		}
		$this->eventID = $eventID;
		if (!empty($this->eventID))
		{
			try
			{
				// Populate object values
				$vars = $this->setinfo_db();
				if (isset($vars))
				{
					$this->title				= $vars[0]['title'];
					$this->requestID			= $vars[0]['requestID'];
					$this->event_start			= $vars[0]['event_start'];
					$this->event_end			= $vars[0]['event_end'];
					$this->fee_rental			= $vars[0]['rental_fee'];
					$this->fee_alcohol			= $vars[0]['alcohol_fee'];
					$this->fee_waiver_rental	= $vars[0]['fee_waiver_rental'];
					$this->fee_waiver_alcohol	= $vars[0]['fee_waiver_alcohol'];
				}
			}
			catch (Exception $e)
			{
				throw new Exception( 'Error: class: "Request" requires valid Request ID', 0, $e);
			}
		}
	}

	/**
	 *  Provide a request's info attributes
	 */
	function event_get($attribute = NULL)
	{
		switch ($attribute)
		{
			case 'eventID':
				if (isset($this->eventID)){ return $this->eventID; }
			break;
			case 'title':
				if (isset($this->title)){ return $this->title; }
			break;
			case 'requestID':
				if (isset($this->requestID)){ return $this->requestID; }
			break;
			case 'event_start':
				if (isset($this->event_start)){ return $this->event_start; }
			break;
			case 'event_end':
				if (isset($this->event_end)){ return $this->event_end; }
			break;
			case 'fee_rental':
				if (isset($this->fee_rental)){ return $this->fee_rental; }
			break;
			case 'fee_alcohol':
				if (isset($this->fee_alcohol)){ return $this->fee_alcohol; }
			break;
			case 'fee_waiver_rental':
				if (isset($this->fee_waiver_rental)){ return $this->fee_waiver_rental; }
			break;
			case 'fee_waiver_alcohol':
				if (isset($this->fee_waiver_alcohol)){ return $this->fee_waiver_alcohol; }
			break;
		}
	}

	/**
	 *  Returns the cost for a specific event session's fee
	 */
	function event_get_fee_cost($fee)
	{
		switch ($fee)
		{
			case 'rental':
				if ($this->fee_waiver_rental == "1")
				{
					return 0;
				}
				else if (!empty($this->fee_rental))
				{
					$fee = new Price($this->fee_rental);
					return $fee->price_get('amount');
				}
			break;
			case 'alcohol':
				if ($this->fee_waiver_alcohol == "1")
				{
					return 0;
				}
				else if (!empty($this->fee_alcohol))
				{
					$fee = new Price($this->fee_alcohol);
					return $fee->price_get('amount');
				}
			break;
		}
	}

	/**
	 *  Returns whether or not specific fees are waived
	 */
	function fee_waived($fee)
	{
		switch ($fee)
		{
			case 'rental':
				return ($this->fee_waiver_rental == "1") ? true : false;
			break;
			case 'alcohol':
				return ($this->fee_waiver_alcohol == "1") ? true : false;
			break;
		}
	}


	/**
	 *  Returns the total cost for a specific event session
	 */
	function event_get_total_cost()
	{
		$fee_rental = 0;
		$fee_alcohol = 0;

		// Set rental
		if (!empty($this->fee_rental) && $this->fee_waiver_rental != "1")
		{
			$fee = new Price($this->fee_rental);
			$fee_rental = $fee->price_get('amount');
			unset($fee);
		}
		// Set alcohol
		if (!empty($this->fee_alcohol) && $this->fee_waiver_alcohol != "1")
		{
			$fee = new Price($this->fee_alcohol);
			$fee_alcohol = $fee->price_get('amount');
			unset($fee);
		}
		// Return the total
		return intval($fee_rental) + intval($fee_alcohol);
	}


	/**
	 *  Returns TRUE if an event's start and end date are the same actual day
	 */
	function event_shares_start_end_date()
	{
		return (date('Y-m-d', strtotime($this->event_start)) == date('Y-m-d', strtotime($this->event_end))) ? TRUE : FALSE;
	}

	/**
	 *  Sets this object's info from a supplied array
	 */
	function setinfo($array)
	{
		$values = Array('title', 'event_start', 'event_end', 'fee_rental', 'fee_alcohol');

		// Loop through the values and assign
		foreach ($values as $value)
		{
			if (isset($array[$value]))
			{
				$this->$value = $array[$value];
			}
		}
	}

	/**
	 *  Sets this object's info from the data store
	 */
	private function setinfo_db()
	{
		if (!empty($this->eventID))
		{
			try
			{
				$query = "SELECT * FROM event_sessions WHERE eventID = '".$this->eventID."'";
				$result = $this->db->fetch_assoc($this->db->query($query));
				if (!empty($result))
				{
					return $result;
				}
				else
				{
					throw new Exception( 'Error: class: "Event" requires valid Event ID');
				}
			}
			catch (Exception $e)
			{
				throw new Exception( 'Error: class: "Event" requires valid Event ID', 0, $e);
			}
		}
	}


	/* * * * * * * * * * * * * * *
	 *
	 *  Class/static methods
	 *
	 * * * * * * * * * * * * * * */

	/**
	 *  Create and insert an event session into the db
	 */
	public static function event_create_session($name, $requestID, $datetime_start, $datetime_end, $rental_fee, $alcohol_fee, $fee_waiver_rental = FALSE, $fee_waiver_alcohol = FALSE)
	{
		global $db;
		// Set default values
		$fee_waiver_rental = (empty($fee_waiver_rental)) ? "0" : "1";
		$fee_waiver_alcohol = (empty($fee_waiver_alcohol)) ? "0" : "1";
		// Insert data
		$data = $db->safe_insert("INSERT INTO event_sessions (title, requestID, event_start, event_end, rental_fee, alcohol_fee, fee_waiver_rental, fee_waiver_alcohol)
		VALUES (?,?,?,?,?,?,?,?)",'sissiiii',
		$name, $requestID, $datetime_start, $datetime_end, $rental_fee, $alcohol_fee, $fee_waiver_rental, $fee_waiver_alcohol);
		if (!empty($db->last_id()))
		{
			return $db->last_id();
		}
		else
		{
			return FALSE;
		}
	}


	/**
	 *  Update an event session
	 */
	public static function event_update_session($column, $value, $eventID)
	{
		global $db;
		$query = "UPDATE event_sessions SET ".$column." = '".$value."' WHERE eventID = '".$eventID."'";
		$result = $db->query($query);
		return $result;
	}


	/**
	 *  Delete an event session
	 */
	public static function event_delete_session($eventID)
	{
		global $db;
		$data = $db->safe_execute("DELETE FROM event_sessions WHERE eventID = ?", "i", $eventID);
	}


	/**
	 *  Accept events as inputs and return whether there are overlapping
	 *  Accepts an associative array of start/end date pairs
	 *  Returns the date of the duplicated session
	 */
	public static function events_multiple_per_day($sessions)
	{
		// Iterate and create an array of dates
		$ranges = Array();
		$return = false;
		foreach ($sessions as $key => $session)
		{
			$temp = Array();
			$temp['key'] = $key;
			$temp['start'] = date('Y-m-d', strtotime($session['session_start_date'])) . ' ' . date('g:i a', strtotime($session['session_start_time']));
			$temp['end'] = date('Y-m-d', strtotime($session['session_end_date'])) . ' ' . date('g:i a', strtotime($session['session_end_time']));
			array_push($ranges, $temp);
			unset($temp);
		}

		// Iterate over the ranges
		$tracker = Array();

		foreach ($ranges as $pair)
		{
			if (in_array(date('Y-m-d', strtotime($pair['start'])), $tracker))
			{
				$return = date('Y-m-d', strtotime($pair['start']));
			}
			else
			{
				array_push($tracker, date('Y-m-d', strtotime($pair['start'])));
			}
		}
		return $return;
	}


	public static function should_be_discounted($date, $this_session, $sessions)
	{
		$price_highest = 0;
		$this_price = new Price($this_session['session_attendance']);
		$date_match = false;
		foreach ($sessions as $key => $session)
		{
			$price = new Price($session['session_attendance']);
			if ($session['session_start_date'] == $date && $date == $this_session['session_start_date'])
			{
				$date_match = true;
			}
			if ($price->price_get('amount') > $price_highest)
			{
				$price_highest = $price->price_get('amount');
			}
			// else if ($price->price_get('amount') == $price_highest && )
			// {
			// 	return false;
			// }
		}
		//echo $this_price->price_get('amount');
		//echo $price_highest;
		return ($date_match && $this_price->price_get('amount') <= $price_highest) ? true : false;
	}



	public static function event_return_sessions_discounted($sessions)
	{
		$waived = Array();
		foreach ($sessions as $key => $session)
		{
			foreach ($sessions as $subkey => $subsession)
			{
				//echo $subkey;
				if ($key == $subkey)
				{
					continue;
				}
				else if ($session['session_start_date'] == $subsession['session_start_date'])
				{
					$session_price = new Price($session['session_attendance']);
					$subsession_price = new Price ($subsession['session_attendance']);
					if ($session_price->price_get('amount') <= $subsession_price->price_get('amount') &&
					!(in_array($subkey, $waived))
					)
					{
						array_push($waived, $key);
					}
				}
			}
		}
		return $waived;
	}


	/**
	 *  Accept events as inputs and return whether there are overlapping
	 *  Aceepts an associative array of start/end date pairs
	 */
	public static function events_are_overlapping($sessions)
	{
		// Iterate and create an array of dates
		$ranges = Array();
		foreach ($sessions as $key => $session)
		{
			$temp = Array();
			$temp['key'] = $key;
			$temp['start'] = date('Y-m-d', strtotime($session['session_start_date'])) . ' ' . date('g:i a', strtotime($session['session_start_time']));
			$temp['end'] = date('Y-m-d', strtotime($session['session_end_date'])) . ' ' . date('g:i a', strtotime($session['session_end_time']));
			array_push($ranges, $temp);
			unset($temp);
		}

		// 1. Sort the ranges by their start times
		usort($ranges, function ($a, $b)
		{
			// Convert string dates to timestamps for reliable comparison
			return strtotime($a['start']) <=> strtotime($b['start']);
		});

		// 2. Iterate through the sorted ranges, comparing each with the next one
		$count = count($ranges);
		for ($i = 0; $i < $count - 1; $i++)
		{
			$current = $ranges[$i];
			$next = $ranges[$i + 1];

			// 3. Check for overlap between current and next range
			// An overlap occurs if the current range's end time is after the next range's start time
			if (strtotime($current['end']) > strtotime($next['start']))
			{
				// Overlap found
				return true;
			}
		}
		// No overlaps found in the entire list
		return false;
	}
}
