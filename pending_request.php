<?php
include 'database.php';
if (isset($_POST["request_decision"])) {
    if (isset($_POST["decision"]) == 1) {
        $decision = "accepted";
    } else {
        $decision = "declined";
    }
$product=$_POST["pid"];
$rdi=$_POST["request_details"];
        if ($_POST["type"]=="order") {

            $query = $con->prepare("update order_details set Status=? where Order_Details_ID=?");
            $query->bind_param("ss", $decision, $_POST["request_details"]);
            $query->execute();
            $message = "Succesfully " . $decision . " request";
            if ($decision == "accepted") {
                if ($_POST["pay_type"] == "paypal") {
                    $query = $con->prepare("select max(Payment_ID)+1 as new_id from payment");
                    $query->execute();
                    $result = $query->get_result();
                    while ($row = $result->fetch_assoc()) {
                        $new_id = $row["new_id"];
                    }
                    $query = $con->prepare("insert into payment(Payment_ID)values($new_id)");
                    $query->execute();
                    $query = $con->prepare("update order_details set  Date_Accepted=now(),Payment_ID=$new_id where Order_Details_ID=?");
                    $query->bind_param("s", $rdi);
                    $query->execute();
                }
                $query = $con->prepare("update order_details set Date_Accepted=now() where Order_Details_ID=?");
                $query->bind_param("s", $rdi);
                $query->execute();
                $query2 = $con->prepare("update product set Product_Status='ordered' where Product_ID=$product");
                $query2->execute();
            }
        } 
        if($_POST["type"]=="reserve"){
            $query = $con->prepare("update reservation_details set Status=? where Reservation_Details_ID=?");
            $query->bind_param("ss", $decision, $rdi);
            $query->execute();
            if ($decision == "accepted") {
                $query = $con->prepare("update reservation_details set Date_End=now()+ 3, Date_Accepted=now() where Reservation_Details_ID=?");
                $query->bind_param("s", $rdi);
                $query->execute();
                $query2 = $con->prepare("update product set Product_status='reserved' where Product_ID=".$product."");
                $query2->execute();
            }
        }
        $notif=$con->prepare("insert into notification(Link_ID,User_ID,Type)values(?,?,?)");
        $notif->bind_param("sss",$_POST["seller_id"],$_POST["user_id"],2);
        $message = "Succesfully " . $decision . " request".$_POST["type"];
 
    echo $message;
}

?>