<?php

/**
 * Shows a notification box for a successfully executed operation
 * message with the passed argument as the message.
 * $message: A string that will be shown inside the box
 */
function show_success_message($message){
    ?>
    <div class="notification is-success">
        <?php echo $message; ?>
    </div>
    <?php
}