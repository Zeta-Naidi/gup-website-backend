<!DOCTYPE html>
<html>
<head>
  <title>Reset Password OTP</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f2f2f2;
      padding: 20px;
      color: white;
    }
    .container {
      margin: 0 auto;
      max-width: 600px;
      background-color: #4d4d4d;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(0,0,0,0.2);
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    p {
      margin-bottom: 10px;
    }
    .otp {
      display: flex;
      justify-content: center;
      padding: 8px 16px;
      background-color: #ddd;
      border-radius: 5px;
      font-size: 18px;
      font-weight: bold;
      color: black;
    }
    button {
      background-color: #007bff;
      color: #fff;
      padding: 10px 20px;
      border-radius: 5px;
      border: none;
      cursor: pointer;
      font-size: 16px;
      display: block;
      margin: 20px auto 0;
    }
    button:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>
<div class="container">
  <h2>Mfa OTP</h2>
  <p>Dear {{$username}},</p>
  <p>Please use the One-Time Password (OTP) to access your account using Multi-Factor Authentication (MFA) within <strong>5 minutes</strong> to login:</p>
  <div style="display: flex;justify-content: center">
    <p class="otp">{{ $otp }}</p>
  </div>
  <p>Thank you for using our services.</p>
</div>
</body>
</html>
