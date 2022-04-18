<?php
include('connection.php');
include('adminsession.php');
include('../sms/_credentials.php');
include('../sms/_helper.php');
include('getHijriDate.php');

$today = getTodayDateHijri();

if($_POST)
{
  //validate payment type is provided
  if (empty($_POST['payment'])) {
    echo "Provide payment type (Bank or Cash)";
    exit();
  }

  //validate if payment is by bank then transaction ID is also provided.
  if ($_POST['payment'] == 'Bank' && empty($_POST['transaction_id'])) {
    echo "Provide payment transaction ID of the transfer";
    exit();
  }

  // getting receipt number
  $sql = mysqli_query($link,"SELECT MAX(`Receipt_No`) from receipts");
  $row = mysqli_fetch_row($sql);
  $receipt_number = $row[0] + 1;

  // validation
  if ($receipt_number == 1) {
    echo "Receipt Number cannot be 1, check with administrator";
    exit();
  }
  $sql = "select NAME,id from thalilist WHERE thali = '" . $_POST['receipt_thali'] . "'";
  $result = mysqli_query($link, $sql) or die(mysqli_error($link));
  $name = mysqli_fetch_assoc($result);

  // validation
  if (empty($name)) {
    echo "Unable to find details of the thali #".$_POST['receipt_thali'];
    exit;
  }
  $sql = "INSERT INTO receipts (`Receipt_No`, `Thali_No`, `userid` ,`name`, `Amount`, `payment_type`, `trasaction_id`, `Date`, `received_by`) VALUES ('" . $receipt_number . "','" . $_POST['receipt_thali'] . "','" . $name['id'] . "','" . $name['NAME'] . "','" . $_POST['receipt_amount'] . "','" . $_POST['payment']."','" . $_POST['transaction_id']."', '" . $today . "','" . $_SESSION['email'] . "')";
  mysqli_query($link, $sql) or die(mysqli_error($link));

  $sql = "UPDATE thalilist set Paid = Paid + '" . $_POST['receipt_amount'] . "' WHERE thali = '" . $_POST['receipt_thali']."'";
  mysqli_query($link, $sql) or die(mysqli_error($link));

  $sql = mysqli_query($link,"SELECT SUM(`Amount`) from receipts");
  $row = mysqli_fetch_row($sql);
  $amount = $row[0];

  $sql = mysqli_query($link,"SELECT SUM(`Paid`) from thalilist");
  $row = mysqli_fetch_row($sql);
  $paid = $row[0];

  
  if ($amount == $paid){
    echo "Success\n";
  }

$user_amount = $_POST['receipt_amount'];
$user_thali = $_POST['receipt_thali'];
$user_receipt = $receipt_number;
$user_date = $_POST['receipt_date'];
$sql = mysqli_query($link,"SELECT NAME, Email_ID, CONTACT from thalilist where Thali='".$user_thali."'");
$row = mysqli_fetch_row($sql);
$user_name = helper_getFirstNameWithSuffix($row[0]);
$sms_to = $row[2];
$user_pending = helper_getTotalPending($user_thali);
// use \n in double quoted strings for new line character
$sms_body = "Mubarak $user_name for contributing Rs. $user_amount (R.No. $user_receipt) in FMB. Moula TUS nu ehsan che ke apne jamarwa ma shamil kare che.\n"
            ."Thali#:$user_thali\n"
            ."Pending:$user_pending";
$sms_body_encoded = urlencode($sms_body);
$result = file_get_contents("http://sms1.almasaarr.com/sendhttp.php?authkey=$smsauthkey&mobiles=$sms_to&message=$sms_body_encoded&sender=FAIZST&route=Template");

echo $sms_body;
}
