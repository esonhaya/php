<?php
include 'database.php';
if (isset($_POST["my_products"])) {
    $array = array();
    $user_id = $_POST["user_id"];
    $query = $con->prepare("select * from product p left join category c on p.Category_ID=c.Category_ID where   p.User_ID=? and deleted=0  
    order by Date_Added desc");
  $query->bind_param("s", $user_id);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $row["seller_name"] = get_seller_name($user_id, $_POST["item_type"]);
        $row["Product_image"] = get_an_image($row["Image_ID"]);
        array_push($array, $row);
    }
    echo json_encode(array("seller_items" => $array));
}