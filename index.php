<?php
session_start();
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
#echo ('hi');
//my first MVC application xD

//Do i need to stay at selected page if added new task?
require_once('Model.php');
require_once('Viewer.php');
require_once('Controller.php');


if (!isset($_SESSION['C']) || !(is_object($_SESSION['C']) )) {
    $_SESSION['C'] = new C;
}
$C = &$_SESSION['C'];
//is there can be that $_SESSION['C'] would be empty?
//TODO: add checks that there is our class, but not now
$C->Polling();
$C->EndOfPage();
