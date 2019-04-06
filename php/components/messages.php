<?php

define('MSG_NOTIFICATION', 0);
define('MSG_SUCCESS', 1);
define('MSG_WARNING', 2);
define('MSG_ERROR', 3);

/**
 * Creates HTML for a notification box with the passed message.
 * The type argument decides the style of the message box.
 * @param message: A string that will be shown inside the box
 * @param type: The type of the message box. Defaults to MSG_NOTIFICATION.
 * Available types are also MSG_SUCCESS, MSG_WARNING, MSG_ERROR
 */
function create_message($message, $type){
    switch($type){
        case MSG_SUCCESS:
            $typeclass = "is-success";
            break;
        case MSG_WARNING:
            $typeclass = "is-warning";
            break;
        case MSG_ERROR:
            $typeclass = "is-danger";
            break;
        case MSG_NOTIFICATION:
        default:
            $typeclass = "";
            break;
    }
    ?>
    <div class="notification <?php echo $typeclass; ?>">
        <?php echo $message; ?>
    </div>
    <?php
}