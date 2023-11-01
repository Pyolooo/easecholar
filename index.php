<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer-master/src/Exception.php';
require_once 'PHPMailer-master/src/PHPMailer.php';
require_once 'PHPMailer-master/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get sender's name and email from the form
  $senderName = $_POST['sender-name'];
  $senderEmail = $_POST['sender-email'];
  $message = $_POST['message'];

  // Create a new PHPMailer instance
  $mail = new PHPMailer(true);

  try {
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'easecholar@gmail.com';
    $mail->Password = 'benz pupq lkxj amje';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port = 587;

    // Set the recipient's name and email
    $mail->addAddress('easecholar@gmail.com', 'Admin');

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'New Message from Chatbox';

    // Concatenate sender name, sender email, and message into the email body
    $emailBody = "From: $senderName &lt;$senderEmail&gt;<br><br>Message:<br>$message";
    $mail->Body = $emailBody;

    // Send email
    $mail->send();
    $successMessage = 'Message sent successfully!';
  } catch (Exception $e) {
    echo 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <link rel="stylesheet" href="portal.css">
  <title>Portal</title>
  <style>
    input{
      margin-bottom: 10px;
      border-radius: 5px;
      border: none;
      padding: 5px;
    }
    .chat-box {
      margin: 0;
    }
    .isulogo {
      width: 20px;
      margin-right: 10px;
    }
    #open-chatbox {
  position: absolute;
  bottom: 0;
  left: 0;
  cursor: pointer;
  padding: 5px 55px;
  background-color: green;
  color: white;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 2px;
  border: none;
  margin: 0;
  box-shadow: 0 0 5px rgba(0, 0, 0, 0.50);
  transition: transform 0.5s;
}
.slide-up {
      transform: translateY(0); 
    }

    #open-chatbox img {
      margin-right: 10px;
    }
    #chatbox-container {
      position: absolute;
      bottom: 0;
      left: 0;
      background-color: rgb(237, 236, 236);
      padding: 10px;
      border-radius: 2px;
      width: 300px;
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.50);
      transition: transform 0.5s; 
      transform: translateY(100%); 
    }

    #chatbox-container.show {
      transform: translateY(0); /* Slide up to visible position */
    }

    .chatbox-content {
      display: flex;
      flex-direction: column;
      justify-content: right;
      margin-bottom: 4px;
    }

    .sender-info {
      display: flex;
      flex-direction: column;
    }

    .sender-name {
      text-align: left;
    }

    #send-message {
      cursor: pointer;
      background-color: #4545b9;
      border-radius: 3px;
      border: none;
      color: white;
      padding: 5px 4px;
      margin-top: 20px;
      transition: opacity 0.15s;
      
    }
    #send-message:hover {
      opacity: 0.8;
    }
    #alert-message{
      margin: 0;
      color: red;
      font-size: 15px;
      text-align: center;
      margin-top: 10px;
    }

  </style>
</head>

<body>
  <nav>
    <?php 
    if (isset($successMessage)) {
      echo '<script>
  Swal.fire({
      position: "center",
      icon: "success",
      title: "' . $successMessage . '",
      showConfirmButton: false,
      timer: 1500
  }).then((result) => {
      if (result.dismiss === Swal.DismissReason.timer) {
          window.location.href = "index.php";
      }
  });
</script>';
}
?>
    <div class="container">
      <div class="portal-header">
        <img src="img/isulogo.png" alt="" class="isu-logo">
        <p class="header-title">A Web-Based Scholarship Application Management System</p>
      </div>
      <div class="user-portal">
        <a href="Admin WSASystem/admin_login.php" title="Admin">
          <button class="admin-button"><i class='fas fa-user-lock'></i>ADMIN</button>
        </a>
        <a href="OSA WSASystem/osa_login.php" title="OSA">
          <button class="osa-button"><i class='fas fa-id-badge'></i>OSA</button>
        </a>
        <a href="Applicant WSASystem/applicant_login.php" title="Student">
          <button class="student-button"><i class='fas fa-address-card'></i>STUDENT</button>
        </a>
        <a href="Applicant WSASystem/applicant_register.php" title="Registration">
          <button class="registrar-button"><i class='fas fa-id-card-alt'></i>REGISTRATION</button>
        </a>
      </div>
      <div class="portal-footer">
        <p class="footer-content">ISABELA STATE UNIVERSITY SANTIAGO EXTENSION</p>
        <p class="footer-content">Made by SELECTA</p>
      </div>
    </div>

    <div class="chatbox">
    <button id="open-chatbox" title="Send message">
      <img class="isulogo" src="./img/isulogo.png">
      EASECHOLAR
    </button>
    <div class="chatbox-container" id="chatbox-container">
      <div class="chatbox-header">
        <h3 style="color: #646464;text-align: center;">New Message</h3>
      </div>
      <div class="chatbox-content">
        <div id="chat-messages"></div>
        <div class="sender-info">
          <label class="sender-name" for="sender-name">From:</label>
          <input type="text" id="sender-name" placeholder="Name" required>
          <input type="email" id="sender-email" placeholder="Email address" required>
        </div>
        <textarea id="message-input" name="message" placeholder="Type your message..." required></textarea>

        <button id="send-message" onclick="sendMessage()">Send</button>

      </div>
    </div>
  </div>

  </nav>

  <script>
       document.addEventListener("DOMContentLoaded", function() {
      const chatboxContainer = document.getElementById("chatbox-container");
      const openChatboxButton = document.getElementById("open-chatbox");
      

      function toggleChatbox() {
  if (chatboxContainer.classList.contains("show")) {
    chatboxContainer.classList.remove("show");
    openChatboxButton.classList.remove("slide-up");
    openChatboxButton.style.transform = "translateY(0)"; // Reset the transformation
    openChatboxButton.style.width = 'auto'; // Reset the width
    openChatboxButton.style.height = 'auto';
  } else {
    chatboxContainer.classList.add("show");
    
    // Toggle the slide-up animation class on the button
    openChatboxButton.classList.add("slide-up");
    
    // Set the width of the button based on the chatbox container's width
    const chatboxWidth = chatboxContainer.clientWidth;
    openChatboxButton.style.width = `${chatboxWidth}px`;
  }
        
        // Set the transformation value based on the chatbox container's height
        const chatboxHeight = chatboxContainer.clientHeight;
        openChatboxButton.style.transform = `translateY(-${chatboxHeight}px)`;
      }

      // Add a click event listener to the open chatbox button
      openChatboxButton.addEventListener("click", toggleChatbox);

      // Add a click event listener to close the chatbox when clicking outside
      document.addEventListener("click", function(event) {
        if (
          chatboxContainer.classList.contains("show") &&
          !chatboxContainer.contains(event.target) &&
          event.target !== openChatboxButton
        ) {
          chatboxContainer.classList.remove("show");
          openChatboxButton.classList.remove("slide-up"); // Remove the slide-up animation class
          openChatboxButton.style.transform = "translateY(0)"; // Reset the transformation
        }
      });
    });


    function sendMessage() {
  const senderName = document.getElementById('sender-name').value;
  const senderEmail = document.getElementById('sender-email').value;
  const message = document.getElementById('message-input').value;

  // Check if any of the required fields is empty
  if (senderName.trim() === '' || senderEmail.trim() === '' || message.trim() === '') {
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: 'Please fill in all required fields!',
    });
    return; // Don't send the message if any field is empty
  }

  // AJAX request to send the data to the PHP script
  $.ajax({
    type: "POST",
    url: "send_message.php", // Replace with the actual path to your PHP script
    data: {
      'sender-name': senderName,
      'sender-email': senderEmail,
      'message': message
    },
    success: function(response) {
      if (response === 'Message sent successfully!') {
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: response,
        }).then((result) => {
          if (result.dismiss === Swal.DismissReason.timer) {
            window.location.href = "index.php";
          }
        });
      } else {
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: 'Message sent successfully!',
        });
      }
    }
  });
}

  </script>
</body>

</html>