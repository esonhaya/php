<?php
include 'database.php';
if (isset($_POST["login"])) {
    $pass = md5($_POST["pass"]);
    $exist = 0;
    $query = $con->prepare("select * from repawner where RePawner_email=? and Password=? ");
    $query->bind_param("ss", $_POST["email"], $pass);
    $query->execute();
    $res = $query->get_result();
    while ($row = $res->fetch_assoc()) {
        $exist = $row["User_ID"];
    }
    //  echo $pass;
    echo $exist;
}
if (isset($_POST["register_user"])) {
    $maxf = 0;
    $maxu = 0;
    $check_max = $con->prepare("select max(Followed_ID)+1 as max_followed,max(User_ID)+1 as max_user  from repawner");
    $check_max->execute();
    $res = $check_max->get_result();
    while ($row = $res->fetch_assoc()) {
        $maxu = $row["max_user"];
        $maxf = $row["max_followed"];
    }
    $check_max = $con->prepare("select max(Followed_ID)+1 as max_followed,max(User_ID)+1 as max_user  from pawnshop ");
    $check_max->execute();
    $res = $check_max->get_result();
    while ($row = $res->fetch_assoc()) {
        if ($maxu < $row["max_user"]) {
            $maxu = $row["max_user"];
        }
        if ($maxf < $row["max_followed"]) {
            $maxf = $row["max_followed"];
        }
    }
    $check_email = $con->prepare("select count(*) c from repawner where RePawner_email=? or RePawner_contact=? and if_activated=1");
    $check_email->bind_param("ss", $_POST["email"],$_POST["contact"]);
    $check_email->execute();
    $result = $check_email->get_result();
    while($row=$result->fetch_assoc()){
        $res=$row["c"];
    }
    $pass = md5($_POST["pass"]);
    if ($res == 0) {
        $image_name = "" . $_POST["email"] . ".jpg";
        $path = "images/" . $_POST["email"] . ".jpg";
        file_put_contents($path, base64_decode($_POST["user_image"]));
        $add_user = $con->prepare("insert into repawner(RePawner_Fname,RePawner_Lname,RePawner_Mname,RePawner_bday,
        RePawner_email,Password,RePawner_contact,User_ID,Followed_ID,user_image) values(?,?,?,?,?,?,?,?,?,?)");
        $add_user->bind_param(
            "ssssssssss",
            $_POST["first_name"],
            $_POST["last_name"],
            $_POST["mid_name"],
            $_POST["birth_day"],
            $_POST["email"],
            $pass,
            $_POST["contact"],
            $maxu,
            $maxf,
            $image_name

        );
        $add_user->execute();
        echo "You have now succesfully registered, Now login please";
    }
    else{
        echo "1";
    }

}
if(isset($_POST["gen_code"])){
    $query=$con->prepare("select User_ID from repawner where RePawner_email='".$_POST["email"]."'");
    $query->execute();
    $result=$query->get_result();
    while($row=$result->fetch_assoc()){
            $rep_id=$row["User_ID"];
    }
    echo $rep_id;
    $gen_code = substr(md5(uniqid(mt_rand(), true)), 0, 7);
  //  echo $gen_code;
    $add_code = $con->prepare("insert into access_code(User_ID,code) values(?,?)");
    $add_code->bind_param("ss", $rep_id, $gen_code);
    $add_code->execute();
    echo $gen_code;
}
if (isset($_POST["edit_basic"])) {
    $query = $con->prepare("select user_image from repawner where User_ID=?");
    $query->bind_param("s", $_POST["user_id"]);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $image_name = $row["user_image"];
    }
    //  $image_name+="1";
    $path = "images/" . $image_name;
    echo $image_name;
    file_put_contents($path, base64_decode($_POST["user_image"]));
    $edit_basic = $con->prepare("update repawner set RePawner_Fname=?,RePawner_Lname=?,RePawner_Mname=?,
    RePawner_Contact=?,RePawner_bday=?,user_image=? where User_ID=?");
    $edit_basic->bind_param(
        "sssssss",
        $_POST["fname"],
        $_POST["lname"],
        $_POST["mname"],
        $_POST["con"],
        $_POST["datetext"],
        $image_name,
        $_POST["user_id"]
    );
    $edit_basic->execute();
    echo "Succesfully updated";
}
if (isset($_POST["account_update"])) {
    $user_id = $_POST["user_id"];
    $message = "";
    $proceed = check_pass($_POST["pass"], $user_id);
    if ($proceed == 1) {
        if (isset($_POST["email"])) {
            $exist = check_exist($_POST["email"]);
            if ($exist == 1) {
                $message = "email already exist";
            } else {
                $query = $con->prepare("update repawner set RePawner_email=? where User_ID=?");
                $query->bind_param("ss", $_POST["email"], $user_id);
                $query->execute();
                $message = "Succesfully changed email address";
            }
        }
        if (isset($_POST["npass"])) {
            $npass = md5($_POST["npass"]);
            $query = $con->prepare("update repawner set Password=? where User_ID=?");
            $query->bind_param("ss", $npass, $user_id);
            $query->execute();
            $message = "Succesfully changed password";
        }
        echo $message;
    } else {
        echo "Input correct password";
    }
}
if(isset($_POST["check_active"])){
    $q=$con->prepare("select if_activated from repawner where User_ID=".$_POST["user_id"]."");
    $q->execute();
    $result=$q->get_result();
    while($row=$result->fetch_assoc()){
        echo $row["if_activated"];    
    }

}
if(isset($_POST["check_access"])){
    $q=$con->prepare("select count(*) as c from access_code where User_ID=".$_POST["user_id"]." and code='".$_POST["code"]."'");
    $q->execute();
    $r=$q->get_result();
    while($row=$r->fetch_assoc()){
        $code= $row["c"];
    }
    if($code==1){
        $q=$con->prepare("update repawner set if_activated=1 where User_ID=".$_POST["user_id"]."");
        $q->execute();
    }
    echo $code;
}
if(isset($_POST["gen_nother"])){
    $q=$con->prepare("update access_code set code=? where User_ID=?");
    $gen_code = substr(md5(uniqid(mt_rand(), true)), 0, 7);
    $q->bind_param("ss",$gen_code,$_POST["user_id"]);
    $q->execute();
    echo $gen_code;
}
if(isset($_POST["get_contact"])){
    $q=$con->prepare("select RePawner_contact from repawner where user_id=".$_POST["user_id"]."");
    $q->execute();
    $r=$q->get_result();
    while($row=$r->fetch_assoc()){
        echo $row["RePawner_contact"];
    }
}

