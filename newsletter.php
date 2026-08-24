<?php
include 'config.php';

require __DIR__ . "/PHPMailer/PHPMailer.php";
require __DIR__ . "/PHPMailer/SMTP.php";
require __DIR__ . "/PHPMailer/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$name  = $_POST['name'];
$email = $_POST['email'];
$city  = $_POST['city'];

$stmt = $conn->prepare("INSERT INTO subscribers (name, email, city) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $city);
$stmt->execute();

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'bhumilkhunt8@gmail.com';
    $mail->Password   = 'otvz ffry orxw shvu';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('bhumilkhunt8@gmail.com', 'Burger House');

    $mail->addAddress('burgerhousebk28@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'New Subscribe';

    $mail->Body = "
        Hello,<br><br>

        New User Subscribed:<br><br>

        Name: $name <br>
        Email: $email <br>
        City: $city <br>
    ";

    $mail->send();

    header("location: mail_success.php");
    exit();

} catch (Exception $e) {
    echo "Mail Error: " . $mail->ErrorInfo;
}