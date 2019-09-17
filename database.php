<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$array = array();

$con = new mysqli("localhost", "root", "", "repawn");
//checking if email/username already been used

function check_exist($gmail)
{

    $con = new mysqli("localhost", "root", "", "repawn");
    $check_any = $con->prepare("select * from repawner where RePawner_email=?");
    $check_any->bind_param("s", $gmail);
    $check_any->execute();
    $rs = $check_any->get_result();
    $rs = $rs->num_rows;
    return $rs;
}
if (isset($_POST["get_followers"])) {
    $user_id = $_POST["user_id"];
    $query = $con->prepare("select Followed_ID from repawner where User_ID=?");
    $query->bind_param("s", $user_id);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $followed_id = $row["Followed_ID"];
    }
    $query = $con->prepare("select r.user_id,user_image,Date_Updated as datef from repawner r  left join follow_seller fs on
    r.User_ID=fs.User_ID where fs.Followed_ID=?");
    $query->bind_param("s", $followed_id);
    $query->execute();
    $array = array();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $row["seller_name"] = get_seller_name($row["user_id"], "pawned");
        array_push($array, $row);
    }
    echo json_encode(array("followers" => $array));
}


if (isset($_POST["check_name_for_update"])) {
    $check_any = $con->prepare("select * from product where Product_name=? and Product_ID!=? and Product_status!='deleted'");
    $check_any->bind_param("ss", $_POST["product_name"], $_POST["pid"]);
    $check_any->execute();
    $rs = $check_any->get_result();
    $rs = $rs->num_rows;
    return $rs;
}
if(isset($_POST["get_category"])){
    $query=$con->prepare("select * from category");
    $query->execute();
    $res=$query->get_result();
    $array=array();
    while($row=$result->fetch_assoc()){
        array_push($array,$row);
    }
    echo json_encode(array("categories"=>$array));
}

function check_pass($pass, $user_id)
{
    $pass = md5($pass);
    $con = new mysqli("localhost", "root", "", "repawn");
    $query = $con->prepare("select * from repawner where Password=? and User_ID=?");
    $query->bind_param("ss", $pass, $user_id);
    $query->execute();
    $result = $query->get_result();
    return $result->num_rows;
}


// register repawner's account

//generate code to activate account
if (isset($_POST["gen_code"])) {
    $gen_code = "";
    $add_code = $con->prepare("insert into access_code(RePawner_ID,code) values(?,?)");
    $gen_code = substr(md5(uniqid(mt_rand(), true)), 0, 7);
    $add_code->bind_param("ss", $_POST["rep_id"], $gen_code);
    $add_code->execute();
}
//get repawner_id based on email then sent access code
if (isset($_POST["get_email"])) {
    $con = new mysqli("localhost", "root", "", "repawn");
    $get_id = $con->prepare("select RePawner_ID from repawner where RePawner_email=?");
    $get_id = bind_param("s", $_POST["email"]);
    $get_id->execute();
    $res = $get_id->get_result();
    while ($row = $res->fetch_assoc()) {
        $rid = $row["RePawner_ID"];
    }
    $gen_code = "";
    $add_code = $con->prepare("insert into access_code(RePawner_ID,code) values(?,?)");
    $gen_code = substr(md5(uniqid(mt_rand(), true)), 0, 7);
    $add_code->bind_param("ss", $rid, $gen_code);
    $add_code->execute();
    return $rid;
}
// //get repawner profile info
// if (isset($_POST["repawner_profile"])) {
//     $con = new mysqli("localhost", "root", "", "repawn");
//     $get_prof = $con->prepare("select * from repawner where RePawner_email=?");
//     $get_prof = $con->bind_param("s", $_POST["email"]);
//     $get_prof=
//     while ($row = $res->fetch_assoc()) {
//         $row["RePawner_image"] = base64_encode($row["RePawner_image"]);
//         array_push($arr, $row);
//     }
//     echo json_encode(array("rep_profile" => $arr));
// }

if (isset($_POST["confirm_transaction_seller"])) {
    $query = $con->prepare("update order_details set Seller_confirmation=1 where Order_Details_ID=" . $_POST["odi"] . "");
    $query->execute();
    $query = $con->prepare("select Buyer_confirmation from order_details where Order_Details_ID" . $_POST["odi"] . "");
    $query->execute();
    $result = $query->get_result();
    while ($row = $res->fetch_assoc()) {
        $exist = $row["Seller_confirmation"];
    }
    if ($exist == 1) {
        $query = $con->prepare("update product set Product_status='bought' where Product_ID=" . $_POST["pid"] . "");
        $query->execute();
        $query = $con->prepare("update order_details set Status='expired where Order_Details_ID=" . $_POST["odi"] . "");
        $query->execute();
        $message = "Order Transaction complete";
    } else {
        $message = "succesfully confirmed payment";
    }
    echo $message;
}


function get_email($user_id)
{
    $con = new mysqli("localhost", "root", "", "repawn");
    $query = $con->prepare("select RePawner_email from repawner where User_ID=?");
    $query->bind_param("s", $user_id);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        return $row["RePawner_email"];
    }
}
if (isset($_POST["delete_item"])) {
    $query = $con->prepare("update product set Product_status='deleted' where Product_ID=" . $_POST["pid"] . "");
    $query->execute();
    echo "Succesfully deleted" . $_POST["item_name"];
}
if (isset($_POST["order_payment"])) {
    $query = $con->prepare("update payment set Paypal_Payment_ID=?,Amount=? where Payment_ID=?");
    $query->bind_param("sss", $_POST["paypal_id"], $_POST["amount"], $_POST["pay_id"]);
    $query->execute();
}


//get feedback and ratings of the repawner
if (isset($_POST["seller_products"])) {
    $array = array();
    $user_id = "";
    if ($_POST["item_type"] == "pawned") {
        $type = "pawned";
    }
    if ($_POST["item_type"] == "rematado") {
        $type = "rematado";
    }
    $user_id = $_POST["user_id"];
    $other_products = null;
    // $other_products = "p.Product_ID!=" . $_POST["except"] . " and";
    if (isset($_POST["except"])) {
        $other_products = "p.Product_ID!=" . $_POST["except"] . " and";
    }
    $query = $con->prepare("select * from product p 
        left join promotion_product pp on p.Product_ID=pp.Product_ID where $other_products  p.User_ID=? 
        and promoted=1 and active=1 and deleted=0 and reserved=0 and ordered=0 order by max(Date_To_End) desc");

    $next_query = $con->prepare("select * from product p where $other_products  p.User_ID=? and  
        active=1 and deleted=0 and reserved=0 and ordered=0");

    $query->bind_param("s", $user_id);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        if ($row["Product_ID"] == null) {
            break;
        }
        $row["seller_name"] = get_seller_name($user_id, $type);
        $row["Product_image"] = get_an_image($row["Image_ID"]);
        array_push($array, $row);
    }
    $next_query->bind_param("s", $user_id);
    $next_query->execute();
    $result = $next_query->get_result();
    while ($row = $result->fetch_assoc()) {
        if ($row["Product_ID"] == null) {
            break;
        }
        $row["seller_name"] = get_seller_name($user_id, $type);
        $row["Product_image"] = get_an_image($row["Image_ID"]);

        array_push($array, $row);
    }

    echo json_encode(array("seller_items" => $array));
}
//update or insert feedback ratings


// get feedback ratings of repawners excluding the mobile user

//check if user is following the pawnshop

//to follow or unfollow user


function get_rep_id($uid)
{
    $con = new mysqli("localhost", "root", "", "repawn");
    $query = $con->prepare("select RePawner_ID from repawner where User_ID=?");
    $query->bind_param("s", $uid);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        return $row["RePawner_ID"];
    }
}



//get seller other products, first the promoted then the rest



function get_an_image($image_id)
{
    $con = new mysqli("localhost", "root", "", "repawn");
    $subquery = $con->prepare("select Image from gallery where Image_ID=? and Purpose='slideshow' limit 1");
    $subquery->bind_param("s", $image_id);
    $subquery->execute();
    $subresult = $subquery->get_result();
    while ($subrow = $subresult->fetch_assoc()) {
        return $subrow["Image"];
    }
}

function get_seller_name($user_id, $prod_type)
{
    $con = new mysqli("localhost", "root", "", "repawn");
    if ($prod_type == "pawned") {
        $sub_query_user = $con->prepare("select RePawner_Fname,RePawner_Lname from repawner  where User_ID=?");
        $sub_query_user->bind_param("s", $user_id);
        $sub_query_user->execute();
        $result = $sub_query_user->get_result();
        while ($subrow = $result->fetch_assoc()) {
            return $subrow["RePawner_Fname"] . " " . $subrow["RePawner_Lname"];
        }
    } else {
        $sub_query_user = $con->prepare("select Pawnshop_name from pawnshop where User_ID=?");
        $sub_query_user->bind_param("s", $user_id);
        $sub_query_user->execute();
        $result = $sub_query_user->get_result();
        while ($subrow = $result->fetch_assoc()) {
            return $subrow["Pawnshop_name"];
        }
    }
}
function get_category_name($cat_id)
{
    $con = new mysqli("localhost", "root", "", "repawn");
    $sub_query_user = $con->prepare("select Category_name  from category  where Category_ID=?");
    $sub_query_user->bind_param("s", $user_id);
    $sub_query_user->execute();
    $result = $sub_query_user->get_result();
    while ($subrow = $result->fetch_assoc()) {
        return $subrow["Category_name"];
    }
}
