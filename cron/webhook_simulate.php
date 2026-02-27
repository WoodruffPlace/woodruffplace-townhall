<?php require_once dirname(__DIR__) . '/resources/_bootstrap.inc'; ?>
<?php
$action = get_user_input('Enter action:');
action_is_valid($action);


function action_is_valid($action)
{
    $return = false;
    switch ($action)
    {
        case 'invoice.paid':
            $invoice = get_user_input('Enter invoice number: ');
            if (!empty($invoice))
            {
                invoice_process($invoice);
            }
        break;
        case 'request.advance';
            $request = get_user_input('Enter request number:');
            request_advance($request);
        break;
        default:
            echo 'Invalid action' . PHP_EOL;
    }
}


// Process an invoice number
function invoice_process($inv)
{
    global $mail;
    /**
     *  Process a paid invoice
     *  Function returns the requestID
     */
    $return = Invoice::invoice_update_status('paid', $inv);
    if ($return)
    {
        // Update the status of the request
        $request = new Request($return);
        $request->status_update('scheduled');

        // Send notification email
        $message = "Town Hall Committee,\n\nThis email is to notify you that the invoice for the following Town Hall rental has been paid:\n\n". $request->request_get('title') . "\n\n***\nView and manage requests:\n". $GLOBALS['config']['env']['protocol'] . "://" . $GLOBALS['config']['env']['host'] . "\n***\n\n--\nWoodruff Place Civic League\nhttps://WoodruffPlace.org";
        Utility::mailer_helper($mail, $GLOBALS['config']['notification_internal_requests'], 'Town Hall rental paid: ' . $request->request_get('title'), $message);
        // Respond to user
        echo 'Invoice processed.' . PHP_EOL;
    }
}


function request_advance($requestID)
{
    global $mail;
    $request = new Request($requestID);
    $customer = new Customer($request->request_get('customer'));
    $message = Messages::customer_upcoming_event_notification($request, $customer);
    if (Utility::mailer_helper($mail, $customer->customer_get('email'), "Your upcoming event at Town Hall", $message))
    {
        $request->status_update('in_progress');
        $request->request_update('customer_reminder_sent', '1');

        // Respond to user
        echo 'Request advanced to \'in_progress\' date' . PHP_EOL;
    }
}



// Get user input from the command line
function get_user_input($prompt = "Enter something: ")
{
    // Check if the readline extension is available (recommended for better functionality)
    if (extension_loaded('readline'))
    {
        $line = readline($prompt);
        // Optional: add the input to the command history (saved in ~/.php_history)
        if ($line !== false)
        {
            readline_add_history($line);
        }
        return $line;
    }
    else
    {
        // Fallback for systems without readline (e.g., some Windows setups without WSH)
        echo $prompt;
        // Read from standard input (STDIN)
        return trim(fgets(STDIN));
    }
}
//
// // Example usage within a script
// $username = get_user_input("Enter your username: ");
// echo "Hello, " . $username . "!" . PHP_EOL;
?>
