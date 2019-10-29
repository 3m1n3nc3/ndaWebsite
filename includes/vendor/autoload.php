<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once(__DIR__ . '/PHPMailer/Exception.php');
require_once(__DIR__ . '/PHPMailer/PHPMailer.php');
require_once(__DIR__ . '/PHPMailer/SMTP.php');
require_once(__DIR__ . '/imageResize.php'); 
require_once(__DIR__ . '/marxtime.class.php'); 
require_once(__DIR__ . '/goCaptcha/goCaptcha.php');
require_once(__DIR__ . '/Twilio/autoload.php'); 

// Create the mail object of class
$mail = new PHPMailer;