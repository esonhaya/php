<<<<<<< HEAD
<?php
include 'database.php';
if (isset($_POST["repawner_info"])) {
    $seller_database = "repawner";
    $type = "pawned";


    $user_id = $_POST["user_id"];
    $query = $con->prepare("select * ,(select COALESCE(sum(Rating),0) from feedback_ratings where feedback_ratings.User_ID=
    $seller_database.User_ID and feedback_status!='deleted')  as ratings_total,(select count(fr.User_ID) from feedback_ratings as fr where 
    fr.User_ID=$seller_database.User_ID and feedback_status!='deleted') as ratings_count,(select count(Follow_Seller_ID) from follow_seller as fs 
    where fs.Followed_ID=$seller_database.Followed_ID and fs.Follow_Status='following')as follow_count,
    (select count(Product_ID) from product p where p.User_ID=User_ID and p.active=0 and p.deleted=0)as items_sold 
    from $seller_database  where  User_ID=?");
    $query->bind_param("s", $user_id);
    $query->execute();
    $result = $query->get_result();
    $array = array();
    while ($row = $result->fetch_assoc()) {

        $row["seller_name"] = get_seller_name($user_id, $type);
        array_push($array, $row);
    }
    echo json_encode(array("repawner_info" => $array));
}
if (isset($_POST["delete_feedback"])) {
    $rid = get_rep_id($_POST["rid"]);
    $query = $con->prepare("update feedback_ratings set Feedback_status='deleted' where User_ID=? and  RePawner_ID=?");
    $query->bind_param("ss", $_POST["user_id"], $rid);
    $query->execute();
    echo "Removed your review succesfully";
}
if (isset($_POST["repawner_feedback"])) {
    $rid = get_rep_id($_POST["rid"]);
    $query = $con->prepare("select * from feedback_ratings where RePawner_ID=? and User_ID=? and Feedback_status!='deleted'");
    $query->bind_param("ss", $rid, $_POST["user_id"]);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        array_push($array, $row);
    }
    echo json_encode(array("repawner_feedback" => $array));
}
if (isset($_POST["pawnshop_info"])) {
    $seller_database = "pawnshop";
    $type = "rematado";
    $user_id = $_POST["user_id"];
    $query = $con->prepare("select * ,(select COALESCE(sum(Rating),0) from feedback_ratings where feedback_ratings.User_ID=
$seller_database.User_ID and feedback_status!='deleted')  as ratings_total,(select count(fr.User_ID) from feedback_ratings as fr where 
fr.User_ID=$seller_database.User_ID and feedback_status!='deleted') as ratings_count,(select count(Follow_Seller_ID) from follow_seller as fs 
where fs.Followed_ID=$seller_database.Followed_ID and fs.Follow_Status='following')as follow_count,
(select count(Product_ID) from product p where p.User_ID=User_ID and p.active=0)as items_sold 
from $seller_database  where  User_ID=?");
    $query->bind_param("s", $user_id);
    $query->execute();
    $result = $query->get_result();
    $array = array();
    while ($row = $result->fetch_assoc()) {
        $row["seller_name"] = get_seller_name($user_id, $type);
        array_push($array, $row);
    }
    echo json_encode(array("pawnshop_info" => $array));
}
if (isset($_POST["post_feedback"])) {
    $message = "";
    $rid = get_rep_id($_POST["rid"]);
    if ($_POST["update_feedback"] == 1) {
        $query = $con->prepare("update feedback_ratings set Feedback=?,Rating=? where User_ID=? and  RePawner_ID=?");
        $message = "Thank you for the update";
    } else {
        $query = $con->prepare("insert into feedback_ratings(Feedback,Rating,User_ID,RePawner_ID)
        values(?,?,?,?)");
        $message = "Thank you for your Feedback & Rating";
    }
    $query->bind_param("ssss", $_POST["feedback"], $_POST["rating"], $_POST["user_id"], $rid);
    $query->execute();
    $notif=$con->prepare("insert into notification(Link_ID,User_ID,Type)values(?,?,?)");
    $notif->bind_param("sss",$rid,$_POST["user_id"],6);
    $notif->execute();
    echo $message;
}
if (isset($_POST["feedback_ratings"])) {
    $query = $con->prepare("select * from feedback_ratings fr inner join repawner r on fr.RePawner_ID=r.RePawner_ID
    where fr.User_ID=? and r.User_ID!=? and Feedback_status!='deleted' order by Date_Added desc");
    $query->bind_param("ss", $_POST["user_id"], $_POST["rid"]);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        array_push($array, $row);
    }
    echo json_encode(array("feedback_ratings" => $array));
}
if (isset($_POST["follow_this"])) {
    $message = "";
    $query = $con->prepare("select count(User_ID) as following from follow_seller where Followed_ID=?
    and User_ID=?");
    $user_id=$_POST["user_id"];
    $query->bind_param("ss", $_POST["followed_id"], $user_id);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $follow_history = $row["following"];
    }

    $to_follow = $_POST["to_follow"];

    if ($follow_history == 1) {
        if ($to_follow == 1) {
            $query = $con->prepare("update follow_seller set Follow_Status='following' where Followed_ID=? and
            User_ID=?");
            $message = "Now following this pawnshop";
        } else {
            $query = $con->prepare("update follow_seller set Follow_Status='unfollowed' where Followed_ID=? and 
            User_ID=?");
            $message = "Unfollowed this pawnshop";
        }
    } else {
        $query = $con->prepare("insert into follow_seller(Followed_ID,User_ID)values(?,?)");
    }
    $query->bind_param("ss", $_POST["followed_id"], $user_id);
    $query->execute();
    $notif=$con->prepare("insert into notification(Link_ID,User_ID,Type)values(?,?,?)");
    $notif->bind_param("sss",$user_id,$_POST["seller_id"],5);
    $notif->execute();
    echo $message;
}
if (isset($_POST["check_following"])) {
    $query = $con->prepare("select count(User_ID) as following from follow_seller where Followed_ID=? 
    and User_ID=? and Follow_Status='following'");
    $query->bind_param("ss", $_POST["followed_id"], $_POST["rid"]);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $following = $row["following"];
    }

    echo $following;
}
if (isset($_POST["get_image"])) {
    $query = $con->prepare("select user_image from repawner where User_ID=?");
    $query->bind_param("s", $_POST["user_id"]);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        echo $row["user_image"];
    }
=======
<?php
include 'database.php';
if (isset($_POST["repawner_info"])) {
    $seller_database = "repawner";
    $type = "pawned";


    $user_id = $_POST["user_id"];
    $query = $con->prepare("select * ,(select COALESCE(sum(Rating),0) from feedback_ratings where feedback_ratings.User_ID=
    $seller_database.User_ID and feedback_status!='deleted')  as ratings_total,(select count(fr.User_ID) from feedback_ratings as fr where 
    fr.User_ID=$seller_database.User_ID and feedback_status!='deleted') as ratings_count,(select count(Follow_Seller_ID) from follow_seller as fs 
    where fs.Followed_ID=$seller_database.Followed_ID and fs.Follow_Status='following')as follow_count,
    (select count(Product_ID) from product p where p.User_ID=User_ID and p.active=0 and p.deleted=0)as items_sold 
    from $seller_database  where  User_ID=?");
    $query->bind_param("s", $user_id);
    $query->execute();
    $result = $query->get_result();
    $array = array();
    while ($row = $result->fetch_assoc()) {

        $row["seller_name"] = get_seller_name($user_id, $type);
        array_push($array, $row);
    }
    echo json_encode(array("repawner_info" => $array));
}
if (isset($_POST["delete_feedback"])) {
    $rid = get_rep_id($_POST["rid"]);
    $query = $con->prepare("update feedback_ratings set Feedback_status='deleted' where User_ID=? and  RePawner_ID=?");
    $query->bind_param("ss", $_POST["user_id"], $rid);
    $query->execute();
    echo "Removed your review succesfully";
}
if (isset($_POST["repawner_feedback"])) {
    $rid = get_rep_id($_POST["rid"]);
    $query = $con->prepare("select * from feedback_ratings where RePawner_ID=? and User_ID=? and Feedback_status!='deleted'");
    $query->bind_param("ss", $rid, $_POST["user_id"]);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        array_push($array, $row);
    }
    echo json_encode(array("repawner_feedback" => $array));
}
if (isset($_POST["pawnshop_info"])) {
    $seller_database = "pawnshop";
    $type = "rematado";
    $user_id = $_POST["user_id"];
    $query = $con->prepare("select * ,(select COALESCE(sum(Rating),0) from feedback_ratings where feedback_ratings.User_ID=
$seller_database.User_ID and feedback_status!='deleted')  as ratings_total,(select count(fr.User_ID) from feedback_ratings as fr where 
fr.User_ID=$seller_database.User_ID and feedback_status!='deleted') as ratings_count,(select count(Follow_Seller_ID) from follow_seller as fs 
where fs.Followed_ID=$seller_database.Followed_ID and fs.Follow_Status='following')as follow_count,
(select count(Product_ID) from product p where p.User_ID=User_ID and p.active=0)as items_sold 
from $seller_database  where  User_ID=?");
    $query->bind_param("s", $user_id);
    $query->execute();
    $result = $query->get_result();
    $array = array();
    while ($row = $result->fetch_assoc()) {
        $row["seller_name"] = get_seller_name($user_id, $type);
        array_push($array, $row);
    }
    echo json_encode(array("pawnshop_info" => $array));
}
if (isset($_POST["post_feedback"])) {
    $message = "";
    $rid = get_rep_id($_POST["rid"]);
    if ($_POST["update_feedback"] == 1) {
        $query = $con->prepare("update feedback_ratings set Feedback=?,Rating=? where User_ID=? and  RePawner_ID=?");
        $message = "Thank you for the update";
    } else {
        $query = $con->prepare("insert into feedback_ratings(Feedback,Rating,User_ID,RePawner_ID)
        values(?,?,?,?)");
        $message = "Thank you for your Feedback & Rating";
    }
    $query->bind_param("ssss", $_POST["feedback"], $_POST["rating"], $_POST["user_id"], $rid);
    $query->execute();
    $notif=$con->prepare("insert into notification(Link_ID,User_ID,Type)values(?,?,?)");
    $notif->bind_param("sss",$rid,$_POST["user_id"],6);
    $notif->execute();
    echo $message;
}
if (isset($_POST["feedback_ratings"])) {
    $query = $con->prepare("select * from feedback_ratings fr inner join repawner r on fr.RePawner_ID=r.RePawner_ID
    where fr.User_ID=? and r.User_ID!=? and Feedback_status!='deleted' order by Date_Added desc");
    $query->bind_param("ss", $_POST["user_id"], $_POST["rid"]);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        array_push($array, $row);
    }
    echo json_encode(array("feedback_ratings" => $array));
}
if (isset($_POST["follow_this"])) {
    $message = "";
    $query = $con->prepare("select count(User_ID) as following from follow_seller where Followed_ID=?
    and User_ID=?");
    $user_id=$_POST["user_id"];
    $query->bind_param("ss", $_POST["followed_id"], $user_id);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $follow_history = $row["following"];
    }

    $to_follow = $_POST["to_follow"];

    if ($follow_history == 1) {
        if ($to_follow == 1) {
            $query = $con->prepare("update follow_seller set Follow_Status='following' where Followed_ID=? and
            User_ID=?");
            $message = "Now following this pawnshop";
        } else {
            $query = $con->prepare("update follow_seller set Follow_Status='unfollowed' where Followed_ID=? and 
            User_ID=?");
            $message = "Unfollowed this pawnshop";
        }
    } else {
        $query = $con->prepare("insert into follow_seller(Followed_ID,User_ID)values(?,?)");
    }
    $query->bind_param("ss", $_POST["followed_id"], $user_id);
    $query->execute();
    $notif=$con->prepare("insert into notification(Link_ID,User_ID,Type)values(?,?,?)");
    $notif->bind_param("sss",$user_id,$_POST["seller_id"],5);
    $notif->execute();
    echo $message;
}
if (isset($_POST["check_following"])) {
    $query = $con->prepare("select count(User_ID) as following from follow_seller where Followed_ID=? 
    and User_ID=? and Follow_Status='following'");
    $query->bind_param("ss", $_POST["followed_id"], $_POST["rid"]);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $following = $row["following"];
    }

    echo $following;
}
if (isset($_POST["get_image"])) {
    $query = $con->prepare("select user_image from repawner where User_ID=?");
    $query->bind_param("s", $_POST["user_id"]);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        echo $row["user_image"];
    }
>>>>>>> e89db2508ec6ea2672f8545efe7891a9cd6b1e50
}