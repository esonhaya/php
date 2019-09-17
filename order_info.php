<?php
include 'database.php';
if (isset($_POST["cancel_order"])) {
    $query = $con->prepare("select Seller_confirmation,Buyer_confirmation,accepted from order_details where Order_Details_ID=" . $_POST["odi"] . "");
    $query->execute();
    $result = $query->get_result();
    $yes = 0;
    while ($row = $result->fetch_assoc()) {
        if ($row["Seller_confirmation"] == 1) {
            $yes = 1;
        }
        if ($row["Buyer_confirmation"] == 1) {
            $yes = 1;
        }
        $acc = $row["accepted"];
    }
    if ($yes == 0) {
        $query = $con->prepare("update  order_details set cancelled=1 where Order_Details_ID=" . $_POST["odi"] . "");
        $query->execute();
        if ($acc == 1) {
            $query = $con->prepare("update product set Ordered=0 where Product_Id=" . $_POST["pid"] . "");
            $query->execute();
        }
    }
    echo $yes;
}
if (isset($_POST["cancel_reserve"])) {
        $query = $con->prepare("update  reservation_details set cancelled=1 where Reservation_Details_ID=" . $_POST["odi"] . "");
        $query->execute();
        if ($acc == 1) {
            $query = $con->prepare("update product set Reserved=0 where Product_Id=" . $_POST["pid"] . "");
            $query->execute();
        }
    }


if (isset($_POST["update_payment"])) {
    $query = $con->prepare("update order_details set Payment_Type=" . $_POST["ptype"] . " where 
    Order_Details_ID=" . $_POST["odi"] . "");
    $query->execute();
}
if (isset($_POST["get_order_info"])) {
    $query = $con->prepare("select * from order_product op left join order_details od on op.Order_Details_ID
    =od.Order_Details_ID left join repawner r on op.User_ID=r.User_ID where op.Product_ID=" . $_POST["pid"] . "
     and accepted=1");
    $query->execute();
    $result = $query->get_result();
    $array = array();
    while ($row = $result->fetch_assoc()) {
        $row["seller_name"] = get_seller_name($row["User_ID"], "pawned");

        array_push($array, $row);
    }
    echo json_encode(array("order_info" => $array));
}
if (isset($_POST["get_reservation_info"])) {
    $query = $con->prepare("select * from reservation_product op left join Reservation_details od on
     op.Reservation_Details_ID=od.Reservation_Details_ID left join repawner r on op.User_ID=r.User_ID 
    where op.Product_ID=" . $_POST["pid"] . " and accepted=1 order by Date_End desc limit 1");
    $query->execute();
    $result = $query->get_result();
    $array = array();
    while ($row = $result->fetch_assoc()) {
        $row["seller_name"] = get_seller_name($row["User_ID"], "pawned");
        array_push($array, $row);
    }
    echo json_encode(array("reservation_info" => $array));
}
if (isset($_POST["get_payment_info"])) {
    $query = $con->prepare("select * from order_details od left join payment p on od.Payment_ID=p.Payment_ID where 
    od.Order_Details_ID=" . $_POST["oid"] . "");
    $query->execute();
    $result = $query->get_result();
    $array = array();
    while ($row = $res->fetch_assoc()) {
        array_push($array, $row);
    }
    echo json_encode(array("reservation_info" => $arr));
}
if (isset($_POST["confirm_transaction_buyer"])) {
    $query = $con->prepare("update order_details set Buyer_confirm=1 where Order_Details_ID=" . $_POST["odi"] . "");
    $query->execute();
    $query = $con->prepare("select Seller_confirmation from order_details where Order_Details_ID" . $_POST["odi"] . "");
    $query->execute();
    $result = $query->get_result();
    while ($row = $res->fetch_assoc()) {
        $exist = $row["Seller_confirmation"];
    }
    if ($exist == 1) {
        $query=$con->prepare("update order_details set Date_End where Order_Details_ID=".$_POST["odi"]."");
        $query->execute();
        $query = $con->prepare("update product set active=0 where Product_ID=" . $_POST["pid"] . "");
        $query->execute();
        $message = "Order Transaction complete";
    } else {
        $message = "succesfully confirmed payment";
    }
    echo $message;
}
if (isset($_POST["product_info"])) {
    $sql = $con->prepare("select Product_Type from product where Product_ID=" . $_POST["pid"]);
    $sql->execute();
    $result = $sql->get_result();
    while ($row = $result->fetch_assoc()) {
        $type = $row["Product_Type"];
    }
    if ($type == "pawned") {
        $sql = $con->prepare("select * from product left join Repawner r on r.User_ID=product.User_ID where Product_ID=" . $_POST["pid"] . "");
        $sql->execute();
        $subresult = $sql->get_result();
        $array = array();
        while ($row = $subresult->fetch_assoc()) {
            $row["seller_name"] = $row["RePawner_Fname"] . " " . $row["RePawner_Lname"];
            $row["Product_image"] = get_an_image($row["Image_ID"]);
            array_push($array, $row);
        }
    } else {
        $sql = $con->prepare("select * from product p left join Pawnshop pa on p.User_ID=pa.User_ID where Product_ID=" . $_POST["pid"] . "");
        $sql->execute();
        $subresult = $sql->get_result();
        $array = array();
        while ($row = $subresult->fetch_assoc()) {
            $row["seller_name"] = $row["Pawnshop_name"];
            $row["Product_image"] = get_an_image($row["Image_ID"]);
            array_push($array, $row);
        }
    }
    echo json_encode(array("product_info" => $array));
}

