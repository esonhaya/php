<?php
if (isset($_POST["add_product"])) {
    $con = new mysqli("localhost", "root", "", "repawn");
    $check_any = $con->prepare("select * from product where Product_name=? and Product_status!='deleted'");
    $check_any->bind_param("s", $_POST["product_name"]);
    $check_any->execute();
    $rs = $check_any->get_result();
    $rs = $rs->num_rows;

    if ($rs < 1) {
        $query = $con->prepare("select max(Image_ID)+1 new_image from product");
        $query->execute();
        $result = $query->get_result();
        while ($row = $result->fetch_assoc()) {
            $image_id = $row["new_image"];
        }

        $add_product = $con->prepare("insert into product(Product_name,Product_price,User_ID,Category_name,
        Product_description, Date_Added,Product_Type,reservable,Image_ID)  values(?,?,?,?,?,now(),'pawned',?,?)");
        $add_product->bind_param(
            "sssssss",
            $_POST["product_name"],
            floatval($_POST["price"]),
            $_POST["user_id"],
            $_POST["category"],
            $_POST["description"],
            $_POST["res"],
            $image_id
        );
        $add_product->execute();
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
    echo $rs;
}
?>