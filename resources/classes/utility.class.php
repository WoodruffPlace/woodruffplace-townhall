<?php

class Utility
{

	/**
	 * CONSTRUCTOR
	 *
	 * This instantiates new instances
	 * of class Queries.
	 *
	 */

	/**
	 * Form utilities
	 */

	// Parse individual form fields on the signup form
	static function form_field_parse($value)
	{
		//if (isset($value) && !empty($value))
		if (isset($value))
		{
			if (is_array($value))
			{
				$newArray = Array();
				foreach($value as $item)
				{
					array_push($newArray, htmlspecialchars($item, ENT_NOQUOTES));
				}
				return $newArray;
			}
			else
			{
				return htmlspecialchars($value, ENT_NOQUOTES);
			}
		}
		else
		{
			return FALSE;
		}
	}

	// Returns a checked or selected attribute for an input element
	static function form_check_if_checked($form, $submitted, $flag)
	{
		if ($submitted == $form)
		{
			switch ($flag)
			{
				case 'selected':
					return "selected";
				break;
				case 'checked':
				default:
					return "checked";
				break;
			}
		}
	}


	// Validation for signup form
	static function form_response_validate($responses)
	{
		// Set initials
		$is_valid = TRUE;
		$invalid = Array();

		$fields = Array('name_first', 'name_last', 'email', 'phone', 'event_title', 'wpcl_member', 'is_wp_event');

		foreach ($fields as $field)
		{
			if (!isset($responses[$field]) || empty($responses[$field]))
			{
				$is_valid = FALSE;
				array_push($invalid, "form_" . $field);
			}
		}
		// Sessions
		if (!empty($responses['sessions']))
		{
			$fields = Array('session_name', 'session_start_date', 'session_start_time', 'session_end_date', 'session_end_time', 'session_attendance', 'session_alcohol');
			foreach ($responses['sessions'] as $session)
			{
				foreach ($fields as $field)
				{
					if (!isset($session[$field]) || empty($session[$field]))
					{
						$is_valid = FALSE;
						array_push($invalid, $field);
					}
				}
			}
		}
		else
		{
			$is_valid = FALSE;
			array_push($invalid, "sessions");
		}
		// Ensure a valid email
		if (!self::is_valid_email($responses['email']))
		{
			$is_valid = FALSE;
		}
		// Return
		if ($is_valid == TRUE)
		{
			return TRUE;
		}
		else
		{
			return $invalid;
		}
	}

	/**
	 *  Process a 1/0 as a "Yes" or "No"
	 */
	public static function print_yes_no($value)
	{
		return match ($value)
		{
			"1" => "Yes",
			"0" => "No"
		};
	}

	/**
	 *  Swap a "y" or "n" for a "1" or "0", respectively
	 */
	public static function process_yn_boolean($value)
	{
		return match ($value)
		{
			"y" => "1",
			"n" => "0"
		};
	}

	/**
	 *  Similar to above--will check a checkbox if a value matches
	 */
	public static function checkCheckbox($needle, $haystack, $returnType = NULL)
	{
		try
		{
			if (is_array($haystack))
			{
				if (array_key_exists($needle, $haystack))
				{
					$return = 'checked = "checked"';
					switch ($returnType)
					{
						case 'print':
							print $return;
						break;
						case 'return':
						default:
							return $return;
					}
				}
			}
		}
		catch (Exception $e)
		{
			throw new Exception( 'Error (checkCheckbox)', 0, $e);
		}
	}


	public static function numeric_from_string($string)
	{
		return preg_replace('/[^0-9]/','', html_entity_decode($string));
	}

	/**
	 * Utility to print singular or plural versions of words
	 */
	public static function print_singular_plural($number, $word_singular, $word_plural)
	{
		if (!empty($word_singular) && !empty($word_plural))
		{
			if ($number === 1)
			{
				return $word_singular;
			}
			else
			{
				return $word_plural;
			}
		}
		else
		{
			return NULL;
		}
	}


	/**
	 *  Utility to determine a valid URL
	 */
	public static function is_valid_url($url)
	{
		$url = str_replace(Array("https://", "http://"), "", $url);
		$url = "https://" . $url;
		if (!filter_var($url, FILTER_VALIDATE_URL) === FALSE)
		{
			return $url;
		}
	}

	/**
	 *  Utility to return whether an email is valid
	 */
	public static function is_valid_email($email)
	{
		if (filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 *  Email helper to send a message, accepting basic parameters
	 */
	public static function mailer_helper($mailer, $recipient, $subject, $body, $from_name = NULL, $cc = NULL)
	{
		// Add recipient address
		$mailer->addAddress($recipient);

		// Add subject
		$mailer->Subject = $subject;

		// Add body
		$mailer->Body = $body;

		// Add from name, if supplied
		$from = (!empty($from_name)) ? $from_name : $GLOBALS['config']['mail']['from_name'];
		$mailer->setFrom($mailer->Username, $from);

		if (!empty($cc))
		{
			$mailer->addCC($cc);
		}

		// Send the message

		if ($mailer->send())
		{
			// Clear all recipients from the $mail object
			$mailer->clearAllRecipients();
			// Reset "from name"
			$mailer->setFrom($mailer->Username, $GLOBALS['config']['mail']['from_name']);
			return TRUE;
		}
	}

	/**
	 *  Convert a UNIX epoch to a date/time
	 */
	public static function time_convert($timestamp, $format = 'date')
	{
		if (!is_numeric($timestamp))
		{
			return false;
		}
		else
		{
			$dateTime = new DateTime("@$timestamp");
			$timezone = new DateTimeZone('America/New_York');
			$dateTime->setTimezone($timezone);
			switch ($format)
			{
				case 'datetime':
					return $dateTime->format('M j, Y g:i a');
				break;
				case 'datetime_db':
					return $dateTime->format('Y-m-d H:i:s');
				break;
				case 'date':
				default:
					return $dateTime->format('M j, o');
			}
		}
	}

} // end class
