<?php
include 'database.php';
if (isset($_POST["update_product"])) {
    // Product_name,Product_price,User_ID,Category_name,
    //     Product_description, Date_Added,Product_Type,reservable,Image_ID
    $sql = $con->prepare("update product set Product_name=?,Product_price=?,Category_name=?,Product_description=?,
    reservable=? where Product_ID=" . $_POST["product_id"] . "");
    $sql->bind_param("sssss", $_POST["product_name"], $_POST["price"], $_POST["category"], $_POST["description"], $_POST["res"]);
    $sql->execute();

    $sql = $con->prepare("select Image_ID from product where Product_ID=" . $_POST["product_id"] . "");
    $sql->execute();
    $result = $sql->get_result();
    while ($row = $result->fetch_assoc()) {
        $image_id = $row["Image_ID"];
    }
    $image_name = "" . $_POST["product_name"] . ".jpg";
    $path = "images/" . $_POST["product_name"] . ".jpg";
    file_put_contents($path, base64_decode($_POST["image"]));
    $receipt_name = "" . $_POST["product_name"] . "rec.jpg";
    $path = "images/" . $_POST["product_name"] . "rec.jpg";
    file_put_contents($path, base64_decode($_POST["receipt"]));
    $query = $con->prepare("insert into gallery(Image_ID,Purpose,Image)values(?,'slideshow',?)");
    $query->bind_param("ss", $image_id, $image_name);
    $query->execute();
    $query = $con->prepare("insert into gallery(Image_ID,Purpose,Image)values(?,'receipt',?)");
    $query->bind_param("ss", $image_id, $receipt_name);
    $query->execute();
}