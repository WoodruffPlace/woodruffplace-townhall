<?php
class API
{
	// Class variables
	private $db;

	// Constructor
	function __construct($db = NULL)
	{
		if ($db == null)
		{
			global $db;
			$this->db = $db;
		}
	}

	// Main function
	function execute($call, $data)
	{
		switch ($call)
		{
			case 'event-get-session':
				//echo $data;
				$event = new Event($data);
				$event_return = Array();
				$event_return['session_eventID']			= $event->event_get('eventID');
				$event_return['session_name']				= $event->event_get('title');
				$event_return['session_start_date']			= date('Y-m-d', strtotime($event->event_get('event_start')));
				$event_return['session_start_time']			= date('H:i:s', strtotime($event->event_get('event_start')));
				$event_return['session_end_date']			= date('Y-m-d', strtotime($event->event_get('event_end')));
				$event_return['session_end_time']			= date('H:i:s', strtotime($event->event_get('event_end')));
				$event_return['session_fee_rental']			= $event->event_get('fee_rental');
				$event_return['session_fee_alcohol']		= ($event->event_get('fee_alcohol') == '0' ? "n" : "y");
				$event_return['session_fee_waiver_rental']	= ($event->event_get('fee_waiver_rental') == '1' ? "y" : "n");
				$event_return['session_fee_waiver_alcohol']	= ($event->event_get('fee_waiver_alcohol') == '1' ? "y" : "n");
				return json_encode($event_return);
			break;
			case 'session-get-session':
				if (!empty($_SESSION['form']['sessions'][$data]))
				{
					return json_encode($_SESSION['form']['sessions'][$data], JSON_FORCE_OBJECT);
				}
			break;
		}
	}
}
