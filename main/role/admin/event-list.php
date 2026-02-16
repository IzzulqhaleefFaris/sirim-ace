<?php
session_start();
include "../../../include/permissions.php";

require_admin();

include "../organiser/event-list.php";
