<?php
require '../../model/Customer.php';
header("content-type: application/json");
header('Access-Control-Allow-Origin: *');
$customer = new Customer();
echo $customer -> index();

 