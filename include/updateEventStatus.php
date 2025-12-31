<?php
/**
 * Updates event statuses based on current date and event dates.
 * This function should be called periodically or on relevant pages to automate status changes.
 */
function updateEventStatuses($conn) {
    $now = date('Y-m-d H:i:s');

    // 1. Set to 'Active' when registration opens (from 'Draft')
    $conn->query("UPDATE att_event SET event_status = 'Active' WHERE event_status = 'Draft' AND event_openRegistration <= '$now'");

    // 2. Set to 'Inactive' when registration closes but event hasn't started yet
    $conn->query("UPDATE att_event SET event_status = 'Inactive' WHERE event_status = 'Active' AND event_closeRegistration < '$now' AND event_startDate > '$now'");

    // 3. Set back to 'Active' when event starts (from 'Inactive')
    $conn->query("UPDATE att_event SET event_status = 'Active' WHERE event_status = 'Inactive' AND event_startDate <= '$now'");

    // 4. Set to 'Completed' when event ends
    $conn->query("UPDATE att_event SET event_status = 'Completed' WHERE event_status = 'Active' AND event_endDate < '$now'");
}
?>