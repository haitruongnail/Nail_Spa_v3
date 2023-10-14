<?php
session_start();
require "./vendor/autoload.php";

use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;

require_once  "../model/config.php";
require_once "../model/database.php";
require_once  "../model/company.php";
require_once  "../model/user.php";
require_once  "../model/booking.php";


$user = new User();

if(isset($_SESSION['error-ip'])){
    unset($_SESSION['error-ip']);
}

if (isset($_POST['fullname']) && isset($_POST['phone']) && isset($_POST['email']) && isset($_POST['description'])) {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $description = $_POST['description'];

    // if user from the share internet  
    if(!empty($_SERVER['HTTP_CLIENT_IP'])) {  
        $ip_user = $_SERVER['HTTP_CLIENT_IP'];  
    }  
    //if user is from the proxy  
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  
        $ip_user = $_SERVER['HTTP_X_FORWARDED_FOR'];  
    }  
    //if user is from the remote address  
    else{  
        $ip_user = $_SERVER['REMOTE_ADDR'];  
    }  

    $user = new User();
    $getUserByPhone = $user->getUserByPhone($phone);

    if(isset($_POST['id_company'])){
        $id_com = $_POST['id_company'];

        $company = new Company();
        $getCompanyById = $company->getCompanyByRandId($id_com);
        $companyName = $getCompanyById[0]['company_name'];

        if(count($getUserByPhone) > 0){
            
            foreach($getUserByPhone as $item){
                if($ip_user == $item['ip_user']){
                    $_SESSION['error-ip'] = "This phone number has been booked with us, please wait until the appointment is over";
                    header("location: step3.php?id=$id_com");die();
                }else{
                    unset($_SESSION['error-ip']);
                }
                $booking = new Booking();
                $getBookingByUser = $booking->getBookingByUser($item['id']);
                foreach($getBookingByUser as $data){
                    $current_date = date('Y-m-d');
                    if($data['date_duration'] < $current_date){
                        $_SESSION['error-phone'] = "You missed an appointment with us at a certain service. This phone number is currently not allowed to make an appointment for our service";
                        header("location: step3.php?id=$id_com");
                        die();
                    }else if($data['date_duration'] == $current_date){
                        $current_time = date('H:i:s');
                        if($data['time_duration'] <= $current_time){
                            $_SESSION['error-phone'] = "You missed an appointment with us at a certain service. This phone number is currently not allowed to make an appointment for our service";
                            header("location: step3.php?id=$id_com");
                            die();
                        }
                    }
                    else{
                        if(isset($_SESSION['error-phone'])){
                            unset($_SESSION['error-phone']);
                        }
                    }
                }
            }
        }
    }

    try {
        $ID_COM;
        if (isset($_SESSION['register'])) {
            unset($_SESSION['register']);
        }
        try{
            $twilio = new Client('AC0888e04255208c65df4b5dd5fd1cabf7', '8032bcab81592de36eb03a83dc3f83d1');
            $messFirst = $twilio->messages
            ->create(
                "$phone", // to 84969747473
                [
                    "body" => "Hello!Welcome to $companyName!",
                    "from" => "+12179968787", //12542685884
                    ]
                );
                
                
            $insert_id = $user->insert($fullname, $phone, $email, $description, $ip_user);
            if($insert_id){
                $_SESSION['user-book'] = [$fullname, $phone];
            }
            if (isset($_SESSION['cart'])) {
                $end_date = [];
                foreach ($_SESSION['cart'] as $item) {
                    $company_id = $item['company_id'];
                    $service_id = $item['service_id'];
                    $staff_id = $item['staff_id'];
                    $date_duration = $item['date_duration'];
                    $time_duration = $item['time_duration'];
    
                    if(substr($time_duration, -2, 2) == 'PM'){
                        $hour = substr($time_duration, 0, 2);
                        $minute = substr($time_duration, 3, 2);
                        $hour = $hour + 12;
                        $time_duration = $hour . ':' . $minute . ':00';
                    }else if(substr($time_duration, -2, 2) == 'AM'){
                        $hour = substr($time_duration, 0, 2);
                        $minute = substr($time_duration, 3, 2);
                        $time_duration = $hour . ':' . $minute . ':00';
                    }
                    $ID_COM = $id_com;

                    $str = $date_duration . ' at ' . $time_duration;

                    array_push($end_date, $str);
    
                    $booking = new Booking();

                    $getCompanyByRandId = $company->getCompanyByRandId($id_com);
                    $lastIdCom = $getCompanyByRandId[0]['id'];
                    // var_dump($company_id, $service_id,$staff_id,$date_duration,$time_duration,$insert_id);die();
                    // var_dump($_SESSION['cart']);die();
                    try{
                        $insertBooking = $booking->insert($lastIdCom, $service_id, $staff_id, $date_duration, $time_duration, $insert_id);
                    }catch(Throwable $err){
                        $_SESSION['error'] = $err;
                        header("location: step3.php?id=$id_com");exit;
                    }
                }
                $countService = count($_SESSION['cart']);
                
                $listService = "";
                foreach ($_SESSION['cart'] as $item) {
                    $listService .= $item['name_service'] . ", ";
                }
                
                
                // Find your Account SID and Auth Token at twilio.com/console
                // and set the environment variables. See http://twil.io/secure
    
                // $twilio = new Client('AC0888e04255208c65df4b5dd5fd1cabf7', '397ab1f5c5d7dd92bc111319ba70c4d2');
                //Get phone of company
                $phoneCom = $getCompanyById[0]['phone_booking'];
                // //AC5917b53b6fe53799a1f4f06dd14d65fa 324e8886538d3046e211b039871ba69c
                $listTime = implode(', ', $end_date);

                $message = $twilio->messages
                ->create(
                    "$phone", // to 84969747473
                    [
                        "body" => "Your appointment with $companyName has been confirmed on $listTime. To Reschedule or Cancel, please call $phoneCom. DO NOT REPLY TO TEXT",
                        "from" => "+12179968787", //12542685884
                        "statusCallback" => "http://postb.in/1234abcd" //option
                    ]
                );
                


                try{
                    $messageCom = $twilio->messages
                    ->create(
                        "$phoneCom", // to 84969747473
                        [ 
                            "body" => "User $fullname have phone is $phone appointment confirmed on $listTime . DO NOT REPLY TO TEXT",
                            "from" => "+12179968787", //12542685884
                            "statusCallback" => "http://postb.in/1234abcd" //option
                        ]
                    );
                }catch(Throwable $err){
                    header("location: ./step4.php?id=$id_com");exit;
                }
                
                header("location: ./step4.php?id=$id_com");exit;
            }else{
                header("location: ./index.php?id=$id_com");exit;
            }

        }catch(Throwable $err){
            // $_SESSION['error'] =  $err->getMessage()."\n";
            $_SESSION['error'] =  "Something went wrong with twilio account or your phone number wrong. Please check again!";
            header("location: step3.php?id=$id_com");exit;
        }

        // }
    } catch (Throwable $err) {
        echo $err;
    }
}
