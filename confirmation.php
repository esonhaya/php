<?php
include 'database.php';
if (isset($_POST["promotion_pawned"])) {
    $pay_id = $con->prepare("select max(Payment_ID)+1 as pay_id from payment");
    $pay_id->execute();
    $pid = 0;
    $day = 0;
    $rs = $pay_id->get_result();
    foreach ($rs as $row) {
        $pid = $row["pay_id"];
    }
    switch (intval($_POST["amount"])) {
        case 100:
            $day = 7;
            break;
        case 200:
            $day = 14;
            break;
        case 300:
            $day = 30;
            break;
        default:
            break;
    }
    $promotion = $con->prepare("insert into promotion_product(Product_ID,Date_To_End,Date_Started,Payment_ID)
   values(?,now()+interval ? day,now(),?)");
    $promotion->bind_param("sss", $_POST["pid"], $day, $pid);
    $promotion->execute();
    $payment = $con->prepare("insert into payment(Payment_ID,Paypal_Payment_ID,Amount,Date_Added) values(?,?,?,now())");
    $payment->bind_param("sss", $pid, $_POST["paypal_id"], $_POST["amount"]);
    $payment->execute();
    $update = $con->prepare("update product set Product_status='promoted' where Product_ID=?");
    $update->bind_param("s", $_POST["pid"]);
    $update->execute();
}