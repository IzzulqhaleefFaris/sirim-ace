<?php
/**
 * Updates event statuses based on current date and event dates.
 * This function should be called on page loads to keep statuses current.
 * 
 * Status Logic:
 * - Current: today >= event_startDate AND today <= event_endDate
 * - Upcoming: today < event_startDate
 * - Completed: today > event_endDate
 */
function updateEventStatuses($conn) {
    if (!$conn || !($conn instanceof mysqli)) {
        return false;
    }

    $today = date('Y-m-d'); // Get today's date (date only, no time)
    
    // Update to 'Current' if today is between start and end date (inclusive)
    $conn->query("UPDATE att_event 
                  SET event_status = 'Current' 
                  WHERE DATE(event_startDate) <= '$today' 
                    AND DATE(event_endDate) >= '$today'");
    
    // Update to 'Upcoming' if today is before start date
    $conn->query("UPDATE att_event 
                  SET event_status = 'Upcoming' 
                  WHERE DATE(event_startDate) > '$today'");
    
    // Update to 'Completed' if today is after end date
    $conn->query("UPDATE att_event 
                  SET event_status = 'Completed' 
                  WHERE DATE(event_endDate) < '$today'");
    
    return true;
}
?>