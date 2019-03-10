<?php
/**
 * show_message_on_error($error)
 * Shows $error in a message box if the argument is not null/false
 */
function show_message_on_error($error){
    if( $error ){
        ?>
        <div class="notification is-danger">
            <?php echo $error; ?>
        </div>
        <?php
    }
}
?>