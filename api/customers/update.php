<?php
require '../../model/Customer.php';

header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');

$customer = new Customer();
$customer->id = intval($_GET["id"]);

$customer->fname = trim(strval($_POST["fname"]));
$customer->lname = trim(strval($_POST["lname"]));

$customer->gender = intval($_POST["gender"]);
if (!in_array($customer->gender, [1, 2])) {
    echo json_encode([
        'result' => false,
        'message' => 'Invalid gender. It should be number (1=Male) or(2=Female).'
    ]);
    exit;
}
$customer->branch = intval($_POST["branch"]);
if (!in_array($customer->branch, [1, 2, 3])) {
    echo json_encode(
        [
            'result' => false,
            'message' => 'Branch should be number (1=Kandal),(2=Phnom Penh), or(3=Prey Veng).'
        ]
    );
    exit;
}

$customer->email = trim(strval($_POST["email"]));
if (!filter_var($customer->email, FILTER_VALIDATE_EMAIL) || !preg_match('/@gmail\.com$/', $customer->email)) {
    echo json_encode([
        'result' => false,
        'message' => 'Invalid email. It must be a valid Gmail address.'
    ]);
    exit;
}

$customer->file = $_FILES["photo"] ?? null;
echo $customer->update();
