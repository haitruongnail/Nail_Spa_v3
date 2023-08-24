<?php
session_start();
require "../vendor/autoload.php";

use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;

require_once  "../../model/config.php";
require_once "../../model/database.php";
require_once  "../../model/company.php";
require_once  "../../model/user.php";
require_once  "../../model/booking.php";

if(isset($_GET['id'])){
    $id_book = $_GET['id'];

    $booking = new Booking();

    $getBookingById = $booking->getBookingById($id_book);

    $username = $getBookingById[0]['fullname'];
    $company = $getBookingById[0]['company_name'];
    $companyHotLine = $getBookingById[0]['hotline'];
    $phoneUser = $getBookingById[0]['phone'];
    $dateBegin = $getBookingById[0]['date_duration'];

    $hour_start = substr($getBookingById[0]['time_duration'],0,2);
    $minute_start = substr($getBookingById[0]['time_duration'],3,2);

    $period_start = $hour_start  >= 12 ? 'PM' : 'AM';
    $hour_start = $hour_start > 12 ? $hour_start - 12 : $hour_start;
    $hour_start = $hour_start < 10 && strlen($hour_start) < 2 ? '0'.$hour_start : $hour_start;

    $timeBegin = $hour_start . ':' . $minute_start . '' . $period_start;
    
    try{
        $twilio = new Client('AC0888e04255208c65df4b5dd5fd1cabf7', '8032bcab81592de36eb03a83dc3f83d1');

        try{
            $messFirst = $twilio->messages
            ->create(
                "$phoneUser", // to 84969747473
                [
                    "body" => "Hi $username, don't forget your appt at $company on $dateBegin at $timeBegin. To Reschedule or Cancel, please call $companyHotLine. DO NOT REPLY TO TEXT",
                    "from" => "+12179968787", //12542685884
                ]
            );

            $_SESSION['success'] = "Send SMS message successful!";
            header("location: ../list_booking.php");exit;

        }catch(Throwable $err){
            $_SESSION['error'] = "Phone number is wrong. Please check twlio account and try it!";
            header("location: ../list_booking.php");exit;
        }

    }catch(TwilioException $err){
        $_SESSION['error'] = "Something went wrong with twilio account. Please check twlio account and try it!";
        header("location: ../list_booking.php");exit;
    }
}else{
    $_SESSION['error'] = "Error! An error occurred. Please try again later";
    header("location: ../list_booking.php");
}
