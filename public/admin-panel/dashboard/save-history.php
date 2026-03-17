<?php
$data = file_get_contents("php://input");

file_put_contents("/../../datenbank/json/history.json", $data);

echo json_encode(["status"=>"saved"]);
?>
