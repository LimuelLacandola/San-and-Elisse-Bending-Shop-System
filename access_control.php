<?php

function restrictAccessToEmployee($userRole) {
    if ($userRole === 'frontdesk') {
        // If the user is an employee, restrict access
        header("Location: access_error_modal.php");
        exit();
    }
}

function restrictAccessToCashier($userRole) {
    if ($userRole === 'cashier') {
        // If the user is a cashier, restrict access
        header("Location: access_error_modal.php");
        exit();
    }
}
?>
