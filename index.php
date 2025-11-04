<?php
session_start();
include 'data.php';

// লগ আউট হ্যান্ডেল করা
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_destroy();
    header("Location: index.php");
    exit;
}

// যদি ইউজার ইতিমধ্যে লগইন করা থাকে তবে ড্যাশবোর্ডে রিডাইরেক্ট করা হবে
if (isset($_SESSION['user_phone'])) {
    header("Location: dashboard.php");
    exit;
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    // ভ্যালিডেশন
    if (strlen($phone) !== 11 || strlen($password) < 8) {
        $message = '<div class="message error">ফোন বা পাসওয়ার্ড ফরম্যাট ভুল।</div>';
    } else {
        // ডাটাবেসে ইউজার খুঁজে বের করা
        $user = findUserForSignIn($phone, $password);

        if ($user) {
            // সাইন ইন সফল
            $_SESSION['user_phone'] = $user['phone'];
            $_SESSION['username'] = $user['username'];
            
            header("Location: dashboard.php");
            exit;
        } else {
            // সাইন ইন ব্যর্থ
            $message = '<div class="message error">মোবাইল নাম্বার বা পাসওয়ার্ড ভুল।</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সাইন ইন</title>
    <link rel="stylesheet" href="style.css">
    
    <link rel="manifest" href="manifest.json">
    <script>
        if ('serviceWorker' in navigator) {
          window.addEventListener('load', function() {
            navigator.serviceWorker.register('service-worker.js').then(function(registration) {
              console.log('ServiceWorker registration successful with scope: ', registration.scope);
            }, function(err) {
              console.log('ServiceWorker registration failed: ', err);
            });
          });
        }
    </script>
    
</head>
<body>
    <div class="container">
        <h2>সাইন ইন করুন</h2>
        <?php echo $message; ?>
        <form method="post" action="index.php">
            <div class="form-group">
                <label for="phone">১১ ডিজিটের মোবাইল নাম্বার</label>
                <input type="tel" id="phone" name="phone" pattern="[0-9]{11}" title="১১ ডিজিটের মোবাইল নাম্বার দিন" required>
            </div>
            
            <div class="form-group">
                <label for="password_index">পাসওয়ার্ড (কমপক্ষে ৮ ডিজিট)</label>
                <div class="password-container">
                    <input type="password" id="password_index" name="password" minlength="8" required>
                    <span class="toggle-password hidden" id="togglePasswordIndex" onclick="togglePasswordVisibility('password_index', 'togglePasswordIndex')"></span>
                </div>
            </div>
            
            <button type="submit" class="btn">সাইন ইন</button>
        </form>
        
        <br><br>
        
        <div class="links">
            <a href="signup.php">অ্যাকাউন্ট নেই? সাইন আপ করুন</a>
            <a href="search.php">প্রোফাইল খুঁজুন</a>
        </div>
    </div>

    <script>
    function togglePasswordVisibility(fieldId, iconId) {
        const passwordField = document.getElementById(fieldId);
        const toggleIcon = document.getElementById(iconId);
        
        if (passwordField.type === "password") {
            passwordField.type = "text";
            toggleIcon.classList.remove('hidden');
            toggleIcon.classList.add('visible');
        } else {
            passwordField.type = "password";
            toggleIcon.classList.remove('visible');
            toggleIcon.classList.add('hidden');
        }
    }
    </script>
</body>
</html>
