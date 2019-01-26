<?php
    if( !isset($_SESSION) ){
        session_start();
    }
    $logged = false;
    if( isset($_SESSION['logged']) ){
        $logged = $_SESSION['logged'];
    }
?>