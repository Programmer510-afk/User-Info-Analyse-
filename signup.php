<?php
session_start();
include 'data.php';

// যদি ইউজার ইতিমধ্যে লগইন করা থাকে তবে ড্যাশবোর্ডে রিডাইরেক্ট করা হবে
if (isset($_SESSION['user_phone'])) {
    header("Location: dashboard.php");
    exit;
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // ভ্যালিডেশন
    if (empty($username) || strlen($phone) !== 11 || strlen($password) < 8) {
        $message = '<div class="message error">ইউজারনেম, ফোন বা পাসওয়ার্ড ফরম্যাট ভুল।</div>';
    } elseif ($password !== $confirm_password) {
        $message = '<div class="message error">পাসওয়ার্ড দুটি মেলেনি।</div>';
    } elseif (findUserByPhone($phone)) {
        $message = '<div class="message error">এই মোবাইল নাম্বার দিয়ে ইতিমধ্যে অ্যাকাউন্ট তৈরি করা হয়েছে।</div>';
    } else {
        // সাইন আপ সফল - নতুন ইউজার তৈরি করা
        if (createUser($username, $phone, $password)) {
            $message = '<div class="message success">অ্যাকাউন্ট তৈরি সফল হয়েছে। অনুগ্রহ করে সাইন ইন করুন।</div>';
            // আপনি চাইলে সফল হওয়ার পর সরাসরি index.php তে রিডাইরেক্ট করতে পারেন
            // header("Location: index.php");
            // exit;
        } else {
            $message = '<div class="message error">অ্যাকাউন্ট তৈরি করতে ব্যর্থ।</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সাইন আপ</title>
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
        <h2>নতুন অ্যাকাউন্ট তৈরি করুন</h2>
        <?php echo $message; ?>
        <form method="post" action="signup.php">
            <div class="form-group">
                <label for="username">ইউজারনেম</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="phone">১১ ডিজিটের মোবাইল নাম্বার</label>
                <input type="tel" id="phone" name="phone" pattern="[0-9]{11}" title="১১ ডিজিটের মোবাইল নাম্বার দিন" required>
            </div>
            
            <div class="form-group">
                <label for="password_signup">পাসওয়ার্ড (কমপক্ষে ৮ ডিজিট)</label>
                <div class="password-container">
                    <input type="password" id="password_signup" name="password" minlength="8" required>
                    <span class="toggle-password hidden" id="togglePasswordSignup" onclick="togglePasswordVisibility('password_signup', 'togglePasswordSignup')"></span>
                </div>
            </div>
            <div class="form-group">
                <label for="confirm_password">পাসওয়ার্ড নিশ্চিত করুন</label>
                <div class="password-container">
                    <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
                    <span class="toggle-password hidden" id="toggleConfirmPassword" onclick="togglePasswordVisibility('confirm_password', 'toggleConfirmPassword')"></span>
                </div>
            </div>
            
            <button type="submit" class="btn">সাইন আপ</button>
        </form>
        
        <br>
        
        <div class="links">
            <a href="index.php">ইতিমধ্যে অ্যাকাউন্ট আছে? সাইন ইন করুন</a>
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
