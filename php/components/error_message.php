<?php
/**
 * show_message_on_error($error)
 * Shows $error in a message box if the argument is not null/false
 */
function show_message_on_error($error){
    if( $error ){
        ?>
        <div class="notification is-danger">
            There was an error showing this page: <?php echo $error; ?>
        </div>
        <?php
    }
}
?>