<?php
include 'database.php';
if (isset($_POST["all_requests"])) {
    $query = $con->prepare("select * from order_product op left join Order_Details od on op.Order_Details_ID=od.Order_Details_ID
 left join Product p on op.Product_ID=p.Product_ID where op.User_ID=" . $_POST["user_id"] . " order by Date_Sent desc");
    $query->execute();
    $result = $query->get_result();
    $array = array();
    while ($row = $result->fetch_assoc()) {
        $row["request_type"] = "order";
        $row["product_image"] = get_an_image($row["Image_ID"]);
        array_push($array, $row);
    }
    $query = $con->prepare("select * from reservation_product op left join Reservation_Details od on 
    op.Reservation_Details_ID=od.Reservation_Details_ID left join Product p on op.Product_ID=p.Product_ID 
    where op.User_ID=" . $_POST["user_id"] . " order by Date_Started desc");
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $row["request_type"] = "reservation";
        $row["product_image"] = get_an_image($row["Image_ID"]);
        array_push($array, $row);
    }
    echo json_encode(array("orders" => $array));
}
