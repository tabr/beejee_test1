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
?>
<section class="a">
    <div class="container">
        <div class="row">
            <div class="a1 col-lg-6">A1</div>
            <div class="a2 col-lg-6">A2</div>
        </div>
    </div>
</section>

<section class="b">
    <div class="container">
        B
    </div>
</section>

<section class="c">
    <div class="container">
        <div class="row">
            <div class="c1 col-sm-6 col-lg-4">C1</div>
            <div class="c2 col-sm-6 col-lg-4">C2</div>
            <div class="c3 col-lg-4">C3</div>
        </div>
    </div>
</section>

<section class="d">
    <div class="container">
        <div class="row">
            <div class="d1 col-sm-6 col-lg-3">D1</div>
            <div class="d2 col-sm-6 col-lg-3">D2</div>
            <div class="d3 col-sm-6 col-lg-3">D3</div>
            <div class="d4 col-sm-6 col-lg-3">D4</div>
        </div>
    </div>
</section>
