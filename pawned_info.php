<?php
include 'database.php';
if (isset($_POST["check_requests"])) {
    $query = $con->prepare("select * from order_product op left join order_details od on op.Order_Details_ID=od.Order_Details_ID
where op.Product_ID=? and op.User_ID=? and accepted=1 or cancelled=0");
    $query->bind_param("ss", $_POST["Product_ID"], $_POST["User_ID"]);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $reply = "1";
    }
    $query = $con->prepare("select * from reservation_product op left join reservation_details od on
 op.Reservation_Details_ID=od.Reservation_Details_ID
where Product_ID=? and User_ID=? and accepted=1 or cancelled=0");
    $query->bind_param("ss", $_POST["Product_ID"], $_POST["User_ID"]);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $reply .= "2";
    }
    echo $reply;
}
if (isset($_POST["reserder"])) {
    $type = $_POST["type"];
    if ($type == 1) {
        $name = "Order";
        $ptype = $_POST["ptype"];
    } else {
        $name = "Reservation";
    }
    $pid = $_POST["Product_ID"];
    $uid = $_POST["User_ID"];
    if ($type == 1) {
        $query = $con->prepare("select max(Order_Details_ID)+1 as new_id from Order_details ");
        $query->execute();
        $result = $query->get_result();
        $array = array();
        while ($row = $result->fetch_assoc()) {
            $new_id = $row["new_id"];
        }
        $query = $con->prepare("insert into order_product(Order_Details_ID,Product_ID,User_ID) values(?,?,?)");
        $query->bind_param("sss", $new_id, $pid, $uid);
        $query->execute();
        $query = $con->prepare("insert into order_details(Order_Details_ID,Payment_Type) values(?,?)");
        $query->bind_param("ss", $new_id, $ptype);
        $query->execute();
    } else {
        $query = $con->prepare("select max(Reservation_Details_ID)+1 as new_id from reservation_details ");
        $query->execute();
        $result = $query->get_result();
        $array = array();
        while ($row = $result->fetch_assoc()) {
            $new_id = $row["new_id"];
        }
        $query = $con->prepare("insert into reservation_product(Reservation_Details_ID,Product_ID,User_ID) values(?,?,?)");
        $query->bind_param("sss", $new_id, $pid, $uid);
        $query->execute();
        $query = $con->prepare("insert into reservation_details(Reservation_Details_ID) values(" . $new_id . ")");
        $query->execute();
    }
}
if (isset($_POST["order_requests"])) {
    $query = $con->prepare("select * from repawner r left join order_product op on  op.User_ID=r.User_ID left join
     order_details od on op.Order_Details_ID=od.Order_Details_ID where op.Product_ID=? order by Date_Started desc");
    $query->bind_param("s", $_POST["Product_ID"]);
    $query->execute();
    $array = array();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $row["user_name"] = get_seller_name($row["User_ID"], "pawned");
        array_push($array, $row);
    }
    echo json_encode(array("orders" => $array));
}
if (isset($_POST["reserve_requests"])) {
    $query = $con->prepare("select * from repawner r left join reservation_product rp on  rp.User_ID=r.User_ID left join 
    reservation_details rd on rp.Reservation_Details_ID=rd.Reservation_Details_ID where rp.Product_ID=? 
    order by Date_Started desc");
    $query->bind_param("s", $_POST["Product_ID"]);
    $query->execute();
    $array = array();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $row["user_name"] = get_seller_name($row["User_ID"], "pawned");
        array_push($array, $row);
    }
    echo json_encode(array("reservations" => $array));
}
if (isset($_POST["getdays"])) {
    $days_left = 0;
    $getdays = $con->prepare("select DateDiff(Date_To_End,now()) as day_diff FROM promotion_product where 
    Product_ID=? and Date_To_End>now()");
    $getdays->bind_param("s", $_POST["pid"]);
    $getdays->execute();
    $res = $getdays->get_result();
    while ($row = $res->fetch_assoc()) {
        $days_left = $row["day_diff"];
    }
    echo $days_left;
}
if (isset($_POST["get_prom_history"])) {
    $query = $con->prepare("select * from promotion_product as pp join payment as p on pp.Payment_ID=p.Payment_ID 
    where pp.Product_ID=?");
    $query->bind_param("s", $_POST["pid"]);
    $query->execute();
    $result = $query->get_result();
    $array = array();
    while ($row = $result->fetch_assoc()) {
        array_push($array, $row);
    }
    echo json_encode(array("history" => $array));
}
if (isset($_POST["delete_item"])) {
    $query = $con->prepare("update product set deleted=1 where Product_ID=" . $_POST["pid"] . "");
    $query->execute();
    echo "Succesfully deleted" . $_POST["item_name"];
}
if (isset($_POST["get_order_info"])) {
    $query = $con->prepare("select * from  order_product op join order_details od on op.Order_Details_ID=od.Order_Details_ID
    where op.Product_ID=" . $_POST["pid"] . " and Date_End=null and accepted=1 and cancelled=0");
    $query->execute();
    $res = $query->get_result();
    $arr = array();
    while ($row = $res->fetch_assoc()) {
        if ($row["Payment_ID"] == null) {
            $row["Payment_ID"] = 0;
        }
        $row["buyer_name"] = $row["RePawner_Fname"] + " " + $row["RePawner_Lname"];
        array_push($arr, $row);
    }
    $query = $con->prepare("select * from  order_product op join order_details od on op.Order_Details_ID=od.Order_Details_ID
    inner join repawner r on op.User_ID=r.User_ID where op.Product_ID=" . $_POST["pid"] . " and accepted=1 order by Date_Accepted desc");
    $query->execute();
    $res = $query->get_result();
    while ($row = $res->fetch_assoc()) {
        if ($row["Payment_ID"] == null) {
            $row["Payment_ID"] = 0;
        }
        $row["buyer_name"] = get_seller_name($row["User_ID"], "pawned");
        array_push($arr, $row);
    }
    echo json_encode(array("order_history" => $array));
}
if (isset($_POST["reservation_history"])) {
    $query = $con->prepare("select * from  reservation_product rp join reservation_details rd on 
    rp.Reservation_Details_ID=rd.Reservation_Details_ID
    where rp.Product_ID=" . $_POST["pid"] . " and cancelled=0 and Date_End<now() and accepted=1");
    $query->execute();
    $res = $query->get_result();
    $arr = array();
    while ($row = $res->fetch_assoc()) {

        $row["buyer_name"] = $row["RePawner_Fname"] + " " + $row["RePawner_Lname"];
        array_push($arr, $row);
    }
    $query = $con->prepare("select * from  reservation_product rp join reservation_details rd on 
    rp.Reservation_Details_ID=rd.Reservation_Details_ID  where op.Product_ID=" . $_POST["pid"] . " and accepted=1");
    $query->execute();
    $res = $query->get_result();
    while ($row = $res->fetch_assoc()) {

        $row["buyer_name"] = get_seller_name($row["User_ID"], "pawned");
        array_push($arr, $row);
    }
    echo json_encode(array("reservation_history" => $array));
}

if (isset($_POST["cat_products"])) {
    $array = array();
    $other_products = "";
    // $other_products = "p.Product_ID!=" . $_POST["except"] . " and";
    if (isset($_POST["except"])) {
        $other_products = "p.Product_ID!=" . $_POST["except"] . " and";
    }
    $query = $con->prepare("select * from product p inner join promotion_product pp on pp.Product_ID=p.Product_ID
     where $other_products  p.Category_name=? and p.Product_status='promoted' order by max(Date_To_End) desc");
    $query->bind_param("s", $_POST["cat_name"]);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        if ($row["Product_ID"] == null) {
            break;
        }
        $row["seller_name"] = get_seller_name($_POST["User_ID"], $_POST["Product_Type"]);
        $row["Product_image"] = get_an_image($row["Image_ID"]);
        array_push($array, $row);
    }
    echo json_encode(array("cat_items" => $array));
}
