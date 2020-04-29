<?php
require_once('Model.php');
require_once('Viewer.php');
require_once('Controller.php');
session_start();
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
#echo ('hi');
//my first MVC application xD

//Do i need to stay at selected page if added new task?


if (!isset($_SESSION['C']) || !(is_object($_SESSION['C']->GetUser())) ) {
#    echo 'Creating new obj!';
    $_SESSION['C'] = new C;
}
$C = &$_SESSION['C'];
$C->Polling();
$C->EndOfPage();
