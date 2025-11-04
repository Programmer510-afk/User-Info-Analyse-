<?php
include 'data.php';

// ফোল্ডার পাথ কনস্ট্যান্ট সেট করা
$photo_dir = 'images/user_photo/';
$default_photo_name = 'default_profile.png'; 
$default_photo_path = 'images/' . $default_photo_name; 

$message = '';
$user_info = null;
$searched_phone = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = trim($_POST['phone']);
    $searched_phone = $phone;

    if (strlen($phone) !== 11 || !ctype_digit($phone)) {
        $message = '<div class="message error">অনুগ্রহ করে ১১ ডিজিটের সঠিক মোবাইল নাম্বার দিন।</div>';
    } else {
        $user_info = findUserByPhone($phone);
        if (!$user_info) {
            $message = '<div class="message error">এই মোবাইল নাম্বারের কোনো তথ্য খুঁজে পাওয়া যায়নি।</div>';
        } else {
            $message = '<div class="message success">ইউজার তথ্য খুঁজে পাওয়া গেছে।</div>';
        }
    }
}

// ছবির পাথ নির্ণয় (যদি ইউজার খুঁজে পাওয়া যায়)
$currentImageSrc = '';
if ($user_info) {
    $user_phone = $user_info['phone'];
    $profileImageFileName = $user_phone . '_profile.jpg';
    $profileImagePath = $photo_dir . $profileImageFileName;
    // ক্যাশ এড়ানোর জন্য query string ব্যবহার করা
    $currentImageSrc = file_exists($profileImagePath) ? $profileImagePath . '?' . time() : $default_photo_path;
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ইউজার তথ্য অনুসন্ধান</title>
    <link rel="stylesheet" href="style.css">
    
    <style>
    .profile-box {
        position: relative; /* ছবির অবস্থান সেট করার জন্য */
        padding-top: 20px; /* ছবি উপরে দেখানোর জন্য কিছুটা জায়গা বাড়ানো */
    }
    .search-profile-image {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 80px; 
        height: 80px; /* বর্গাকার নিশ্চিত করতে একই সাইজ */
        object-fit: cover;
        border: 2px solid #ccc;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
    }
    /* প্রোফাইল তথ্যাদি যাতে ছবির নিচে না চলে যায় */
    .profile-box p {
        margin-right: 110px; /* ছবির জন্য জায়গা রাখা */
    }
    </style>
    
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
        <h2>ইউজার তথ্য অনুসন্ধান</h2>
        <form method="post" action="search.php">
            <div class="form-group">
                <label for="phone">মোবাইল নাম্বার</label>
                <input type="tel" id="phone" name="phone" pattern="[0-9]{11}" title="১১ ডিজিটের মোবাইল নাম্বার দিন" required value="<?php echo htmlspecialchars($searched_phone); ?>">
            </div>
            <button type="submit" class="btn">Ok</button>
        </form>
        <?php echo $message; ?>

        <?php if ($user_info): ?>
        <div class="profile-box">
            <img src="<?php echo htmlspecialchars($currentImageSrc); ?>" alt="প্রোফাইল ছবি" class="search-profile-image">
            
            <h3>ব্যক্তিগত তথ্যাদি</h3>
            <p><strong>ইউজার নেম:</strong> <?php echo htmlspecialchars($user_info['username']); ?></p>
            <p><strong>মোবাইল নাম্বার:</strong> <?php echo htmlspecialchars($user_info['phone']); ?></p>
            <p><strong>সম্পূর্ণ নাম:</strong> <?php echo htmlspecialchars($user_info['full_name'] ?: '---'); ?></p>
            <p><strong>বাবার নাম:</strong> <?php echo htmlspecialchars($user_info['father_name'] ?: '---'); ?></p>
            <p><strong>মায়ের নাম:</strong> <?php echo htmlspecialchars($user_info['mother_name'] ?: '---'); ?></p>
            <p><strong>ঠিকানা:</strong> <?php echo htmlspecialchars($user_info['address'] ?: '---'); ?></p>
            <p><strong>ধর্ম:</strong> <?php echo htmlspecialchars($user_info['religion'] ?: '---'); ?></p>
        </div>
        
        <div style="display: flex; justify-content: center; margin-top: 15px;">
            <a href="print_report.php?phone=<?php echo htmlspecialchars($user_info['phone']); ?>" class="btn" style="background-color: #007bff; width: auto;" target="_blank">
                View Printable Report
            </a>
        </div>
        
        <?php endif; ?>
        
        <br>
        
        <div class="links">
            <a href="index.php">সাইন ইন পেজে ফিরে যান</a>
        </div>
    </div>
</body>
</html>