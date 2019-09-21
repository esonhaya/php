<?php
include 'database.php';
if (isset($_POST["get_all_items"])) {
    $query = $con->prepare("select * from product p inner join promotion_product pp on p.Product_ID=pp.Product_ID
      where  Promoted=1 and active=1 and reserved=0 and ordered=0 
    order by max(Date_To_End) desc");
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        if($row["Product_ID"]==null){
        }else{
            $row["seller_name"] = get_seller_name($row["User_ID"], $row["Product_Type"]);
            $row["Product_image"] = get_an_image($row["Image_ID"]);
            $row["Category_name"]=get_category_name($row["Category_ID"]);
        array_push($array, $row);
        }
    }
    $query = $con->prepare("select * from product p where active=1 and reserved=0 and ordered=0  order by Date_Added asc");
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
    
        
        $row["seller_name"] = get_seller_name($row["User_ID"], $row["Product_Type"]);
        $row["Product_image"] = get_an_image($row["Image_ID"]);
        array_push($array, $row);
        
    }

    echo json_encode(array("all_items" => $array));
}
if (isset($_POST["main_cat"])) {
    $get_cat = $con->prepare("select *,(select count(Category_ID) from product where category.Category_ID=
product.Category_ID)cat_count  from category order by cat_count desc");
    $get_cat->execute();
    $res = $get_cat->get_result();
    $arr = array();
    while ($row = $res->fetch_assoc()) {
        array_push($arr, $row);
    }
    echo json_encode(array("category" => $arr));
}
if (isset($_POST["get_all_pawnshops"])) {
    $get_pawnshop = $con->prepare("select *,(select sum(Rating) from feedback_ratings fr where pawnshop.User_ID=fr.User_ID and 
    Feedback_status!='deleted')as ratings_total,(select count(User_ID) from feedback_ratings fr where 
    pawnshop.User_ID=fr.User_ID and Feedback_status!='deleted')as ratings_count ,(select count(User_ID) from follow_seller fs
    where Followed_ID=pawnshop.Followed_ID)follow_count from pawnshop
    order by follow_count desc ");
    $get_pawnshop->execute();
    $result = $get_pawnshop->get_result();
    $arr = array();
    while ($row = $result->fetch_assoc()) {
        if($row["ratings_total"]==null){
             $row["ratings_total"]=0;
         }
        array_push($arr, $row);
    }
    echo json_encode(array("pawnshops" => $arr));
}
if(isset($_POST["all_repawners"])){
    $query=$con->prepare("select *,(select sum(Rating) from feedback_ratings fr where repawner.User_ID=fr.User_ID and 
    Feedback_status!='deleted')as ratings_total,(select count(User_ID) from feedback_ratings fr where 
    repawner.User_ID=fr.User_ID and Feedback_status!='deleted')as ratings_count ,(select count(User_ID) from follow_seller fs where MONTH(fs.Date_Updated)=MONTH(CURRENT_DATE)
     and Followed_ID=repawner.Followed_ID)follow_count  from repawner order by follow_count desc");
     $query->execute();
     $result=$query->get_result();
     $arr=array();
     while($row=$result->fetch_assoc()){
         if($row["ratings_total"]==null){
             $row["ratings_total"]=0;
         }
         $row["RePawner_name"]=$row["RePawner_Fname"]." ".$row["RePawner_Lname"];
         array_push($arr,$row);
     }
     echo json_encode(array("repawners"=>$arr));
     
}
