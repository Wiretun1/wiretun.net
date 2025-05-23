<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to make API requests
function makeRequest($url, $headers, $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    
    curl_close($ch);
    
    return [
        'body' => $response,
        'headers' => $headers,
        'httpCode' => $httpCode
    ];
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $number = $_POST['number'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    
    // Process the number
    if (strpos($number, '011') === 0) {
        $num = substr($number, 1);
    } else {
        $num = $number;
    }
    
    // Encode credentials
    $auth = base64_encode($email . ':' . $password);
    $xauth = 'Basic ' . $auth;
    
    // Login headers
    $headerslog = [
        "applicationVersion: 2",
        "applicationName: MAB",
        "Accept: text/xml",
        "Authorization: $xauth",
        "APP-BuildNumber: 964",
        "APP-Version: 27.0.0",
        "OS-Type: Android",
        "OS-Version: 12",
        "APP-STORE: GOOGLE",
        "Is-Corporate: false",
        "Content-Type: text/xml; charset=UTF-8",
        "Content-Length: 1375",
        "Host: mab.etisalat.com.eg:11003",
        "Connection: Keep-Alive",
        "Accept-Encoding: gzip",
        "User-Agent: okhttp/5.0.0-alpha.11",
        "ADRUM_1: isMobile:true",
        "ADRUM: isAjax:true"
    ];
    
    $datalog = '<?xml version=\'1.0\' encoding=\'UTF-8\' standalone=\'yes\' ?>
    <loginRequest><deviceId></deviceId><firstLoginAttempt>true</firstLoginAttempt><platform>Android</platform><udid></udid></loginRequest>';
    
    // Make login request
    $loginResponse = makeRequest('https://mab.etisalat.com.eg:11003/Saytar/rest/authentication/loginWithPlan', $headerslog, $datalog);
    
    if (strpos($loginResponse['body'], 'true') !== false) {
        // Extract cookies and auth token
        $headers = $loginResponse['headers'];
        preg_match('/Set-Cookie: (.*?);/', $headers, $cookieMatch);
        $cookie = $cookieMatch[1] ?? '';
        preg_match('/auth: (.*)/', $headers, $authMatch);
        $bearerToken = trim($authMatch[1] ?? '');
        
        // Prepare activation request
        $headersActivate = [
            'Host: mab.etisalat.com.eg:11003',
            'User-Agent: okhttp/5.0.0-alpha.11',
            'Connection: Keep-Alive',
            'Accept: text/xml',
            'Accept-Encoding: gzip',
            'Content-Type: text/xml; charset=UTF-8',
            'applicationVersion: 2',
            'applicationName: MAB',
            'Language: ar',
            'APP-BuildNumber: 10625',
            'APP-Version: 32.0.0',
            'OS-Type: Android',
            'OS-Version: 12',
            'APP-STORE: GOOGLE',
            'auth: Bearer ' . $bearerToken,
            'Is-Corporate: false',
            'headerSignature: dcdd3e0ea28715a1a94ff44431497c05bc4ed566d57d995c33dc9a7fd7e6c9622c0ec667bbedb6f3ee8001b225bf4edef57c1c4985ee1758159811f4eff2f1ef',
            'urlSignature: 7ac9329688c5a85eb045e20ab3b9a6565ce5b31281414cc1da1f0da2abe9ce5d0fff8112b714778a408e6a6c783c764f396680de64e40296db80097f97b2a8d9',
            'bodySignature: c4e4cfaa3769f5a9fc19518f30bd2af2abb27c761b451db15981be7a34c2402cc1c6243e1318f9bdb59aa6f88bda48a2dbf960259ef4cf795c97084879da2e77',
            'ADRUM_1: isMobile:true',
            'ADRUM: isAjax:true',
            'Cookie: ' . $cookie
        ];
        
        $dataActivate = '<?xml version=\'1.0\' encoding=\'UTF-8\' standalone=\'yes\' ?>
        <submitOrderRequest>
            <mabOperation></mabOperation>
            <msisdn>' . $num . '</msisdn>
            <operation>ACTIVATE</operation>
            <parameters>
                <parameter>
                    <name>Offer_ID</name>
                    <value>23214</value>
                </parameter>
                <parameter>
                    <name>isRTIM</name>
                    <value>Y</value>
                </parameter>
            </parameters>
            <productName>TWIST_TV</productName>
        </submitOrderRequest>';
        
        // Make activation request
        $activateResponse = makeRequest('https://mab.etisalat.com.eg:11003/Saytar/rest/zero11/submitOrder', $headersActivate, $dataActivate);
        
        if (strpos($activateResponse['body'], 'true') !== false) {
            $success = "تم تفعيل باقة Twist TV بنجاح";
        } else {
            $error = "فشل في تفعيل العرض، يرجى التحقق من البيانات";
        }
    } else {
        $error = "بيانات الدخول غير صحيحة";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etisalat Twist TV</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #007f5f;
            --secondary-color: #d4edda;
            --accent-color: #28a745;
            --error-color: #dc3545;
            --text-color: #333;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text-color);
            line-height: 1.6;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 100%;
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 10px;
            background: linear-gradient(to right, #007f5f, #28a745);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .header p {
            color: var(--dark-color);
            font-size: 1.1rem;
        }
        
        .divider {
            height: 3px;
            background: linear-gradient(to right, transparent, var(--primary-color), transparent);
            margin: 20px 0;
            border: none;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        input[type="text"],
        input[type="password"],
        input[type="email"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="email"]:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 127, 95, 0.2);
            outline: none;
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 127, 95, 0.3);
        }
        
        .btn:active {
            transform: translateY(1px);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .alert-success {
            background-color: var(--secondary-color);
            color: var(--primary-color);
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: var(--error-color);
            border: 1px solid #f5c6cb;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo img {
            max-width: 150px;
            height: auto;
        }
        
        .animation {
            animation-duration: 1s;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .card {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.8rem;
            }
            
            input[type="text"],
            input[type="password"],
            input[type="email"] {
                padding: 10px 12px;
                font-size: 14px;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo animate__animated animate__fadeInDown">
            <!-- You can add your logo here -->
            <h1 style="color: #007f5f;">Etisalat</h1>
        </div>
        
        <div class="card animate__animated animate__fadeInUp">
            <div class="header">
                <h1 class="animate__animated animate__pulse animate__infinite" style="animation-duration: 2s;">Twist TV</h1>
                <p>تفعيل باقة Twist TV</p>
            </div>
            
            <hr class="divider">
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success animate__animated animate__bounceIn">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger animate__animated animate__shakeX">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="number">رقم الهاتف:</label>
                    <input type="text" id="number" name="number" required placeholder="أدخل رقم الهاتف">
                </div>
                
                <div class="form-group">
                    <label for="password">كلمة المرور:</label>
                    <input type="password" id="password" name="password" required placeholder="أدخل كلمة المرور">
                </div>
                
                <div class="form-group">
                    <label for="email">البريد الإلكتروني:</label>
                    <input type="email" id="email" name="email" required placeholder="أدخل البريد الإلكتروني">
                </div>
                
                <button type="submit" class="btn animate__animated animate__pulse" style="animation-delay: 0.5s;">تفعيل الباقة</button>
            </form>
        </div>
    </div>
    
    <script>
        // Add animation on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.card, .header h1, .form-group');
            
            elements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, 100 * index);
            });
        });
    </script>
</body>
</html>
