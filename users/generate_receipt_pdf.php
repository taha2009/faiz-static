<?php
require_once('connection.php');
include('_authCheck.php');
require 'get_receipt_html.php';
require 'get_receipt_pdf.php';


$receiptTemplate = file_get_contents("receipt.html");

// Determine the correct receipts table name based on year
$current_year = mysqli_fetch_assoc(mysqli_query($link, "SELECT value FROM settings where `key`='current_year'"));
$year = !empty($_GET['year']) ? $_GET['year'] : $current_year['value'];
$receipts_tablename = ($current_year['value'] == $year) ? "receipts" : "receipts_" . $year;

$sql = "";

if (!empty($_GET['thalino'])) {
    $sql = "select * from $receipts_tablename WHERE Thali_No = ". mysqli_real_escape_string($link,$_GET['thalino']) . " ORDER BY Receipt_No ASC";
} else {
    $sql = "select * from $receipts_tablename ORDER BY Receipt_No ASC";
}

$result= mysqli_query($link,$sql);

$pdfContent = "";
while($values = mysqli_fetch_assoc($result))
{
    $pdfContent .=  getReceiptHtml($link,$receiptTemplate, $values);
}

generate_pdf($pdfContent);

exit(0);
