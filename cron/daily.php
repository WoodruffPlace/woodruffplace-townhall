<?php require_once dirname(__DIR__) . '/resources/_bootstrap.inc'; ?>
<?php
/**
 *
 *  DAILY BATCH JOBS
 *
 */

/**
 *  Check for and process upcoming events
 */

//
// SELECT DISTINCT requestID from ANY event where event_start <= 3 day

$query = "SELECT DISTINCT requests.requestID FROM requests
LEFT JOIN event_sessions on requests.requestID = event_sessions.requestID
WHERE requests.status = 'scheduled' AND requests.customer_reminder_sent = '0' AND
event_sessions.event_start <= DATE_ADD(CURDATE(), INTERVAL ". $GLOBALS['settings']->get('request.reminder_days') ." DAY)";
$result = $db->fetch_assoc($db->query($query));
if (!empty($result))
{
    foreach ($result as $row_array)
    {
        $request = new Request($row_array['requestID']);
        $request->request_process_upcoming();
    }
    unset($result);
}


/**
 *  Check for any events past due for the checklist
 *    + Mark them with status: ended_abandoned
 */
$query = "SELECT DISTINCT requests.requestID, event_sessions.event_end FROM requests
 LEFT JOIN event_sessions on requests.requestID = event_sessions.requestID
 WHERE requests.status = 'in_progress' AND
 (NOW() - INTERVAL ". $GLOBALS['settings']->get('request.checklist_due') ." DAY) > event_sessions.event_end
 ORDER BY event_sessions.event_end DESC
 LIMIT 1";

 $result = $db->fetch_assoc($db->query($query));
 if (!empty($result))
 {
    foreach ($result as $row_array)
    {
        $request = new Request($row_array['requestID']);
        $request->status_update('ended_abandoned');
    }
 }
?>
