<?php
session_start();
include 'data.php';

// ফোল্ডার পাথ কনস্ট্যান্ট সেট করা
$photo_dir = 'images/user_photo/';
$default_photo_name = 'default_profile.png'; 
$default_photo_path = 'images/' . $default_photo_name; 

// লগইন করা না থাকলে সাইন ইন পেজে রিডাইরেক্ট
if (!isset($_SESSION['user_phone'])) {
    header("Location: index.php");
    exit;
}

$user_phone = $_SESSION['user_phone'];
$user_info = findUserByPhone($user_phone);
$message = '';

// যদি ইউজার ডেটা না পাওয়া যায়
if (!$user_info) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

// প্রোফাইল ছবির পাথ নির্ণয়
$profileImageFileName = $user_phone . '_profile.jpg';
$profileImagePath = $photo_dir . $profileImageFileName; 

// **নতুন ফ্ল্যাগ:** ব্যবহারকারীর কি ইতিমধ্যেই একটি ছবি সেভ করা আছে?
$profile_has_image = file_exists($profileImagePath);

// ছবির পাথ নির্ণয়: যদি ইউজারের ছবি থাকে, তবে সেটাই দেখাবে, অন্যথায় ডিফল্ট ছবি দেখাবে
$currentProfileImageSrc = $profile_has_image ? $profileImagePath . '?' . time() : $default_photo_path;

// ----------------------------------------------------
// লগআউট
// ----------------------------------------------------
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

// ----------------------------------------------------
// প্রোফাইল আপডেট এবং ছবি আপলোড হ্যান্ডলিং (একীভূত লজিক)
// ----------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    
    $full_name = trim($_POST['full_name']);
    $father_name = trim($_POST['father_name']);
    $mother_name = trim($_POST['mother_name']);
    $address = trim($_POST['address']);
    $religion = trim($_POST['religion']);
    $image_data_base64 = $_POST['cropped_image_data'] ?? ''; 
    
    $update_successful = false;
    $personal_info_changed = false;
    $image_update_successful = false;

    // *১. আবশ্যিক তথ্য ভ্যালিডেশন (REQUIRED FIELD CHECK)*
    if (empty($full_name) || empty($address)) {
        $message = '<div class="message error">সম্পূর্ণ নাম এবং ঠিকানা আবশ্যিক। অনুগ্রহ করে পূরণ করুন।</div>';
    } else {
        
        // *২. ব্যক্তিগত তথ্য পরিবর্তনের চেক*
        $updates = [
            'full_name' => $full_name,
            'father_name' => $father_name,
            'mother_name' => $mother_name,
            'address' => $address,
            'religion' => $religion
        ];

        foreach ($updates as $key => $value) {
            // যদি অন্তত একটি ফিল্ডেও বর্তমান তথ্যের সাথে পার্থক্য থাকে
            if ((string)$value !== (string)($user_info[$key] ?? '')) {
                $personal_info_changed = true;
                break; 
            }
        }
        
        // *৩. ছবি না থাকলে সাবমিট ব্লক করার লজিক (নতুন)*
        // যদি ব্যক্তিগত তথ্যের পরিবর্তন না হয়, এবং নতুন ছবিও না থাকে, এবং ব্যবহারকারীর কোনো ছবি সেভ করা না থাকে (ডিফল্ট ছবি দেখাচ্ছে), তবে ব্লক করো।
        if (!$personal_info_changed && empty($image_data_base64) && !$profile_has_image) {
            $message = '<div class="message error">আপনার প্রোফাইল ছবি আপলোড করা আবশ্যক।</div>';
        } 
        // --------------------------------------------------------------------------
        
        // *৪. যদি ব্লক না হয়, তবে আপডেট চালিয়ে যাও*
        elseif ($personal_info_changed || !empty($image_data_base64)) {

            // ব্যক্তিগত তথ্য আপডেট লজিক
            if ($personal_info_changed) {
                if (updateProfile($user_phone, $updates)) { 
                    $update_successful = true;
                } else {
                     $message = '<div class="message error">ব্যক্তিগত তথ্য আপডেট ব্যর্থ হয়েছে।</div>';
                }
            }
            
            // ছবি আপলোড হ্যান্ডলিং
            if (!empty($image_data_base64)) {
                // ... (base64 ডিকোডিং এবং সেভ লজিক)
                if (strpos($image_data_base64, 'data:image') === 0) {
                    list($type, $image_data_base64) = explode(';', $image_data_base64);
                    list(, $image_data_base64) = explode(',', $image_data_base64);
                }
                $imageData = base64_decode($image_data_base64);

                if ($imageData !== false) {
                    $fileName = $user_phone . '_profile.jpg';
                    $uploadDir = $photo_dir; 

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    if (file_put_contents($uploadDir . $fileName, $imageData)) {
                        $image_update_successful = true;
                        $update_successful = true;
                        // ছবি সফলভাবে সেভ হলে, ফ্ল্যাগটি আপডেট করতে হবে (যদিও রিডাইরেক্টের সময় এটি পুনরায় চেক হবে)
                        $profile_has_image = true; 
                    } else {
                        $message = '<div class="message error">ছবি সেভ করার সময় ত্রুটি হয়েছে। ফোল্ডার পারমিশন (0777) চেক করুন।</div>';
                    }
                }
            }
            
            // চূড়ান্ত বার্তা নিয়ন্ত্রণ
            if ($personal_info_changed && $image_update_successful) {
                $message = '<div class="message success">প্রোফাইল তথ্য ও ছবি সফলভাবে আপডেট হয়েছে!</div>';
            } elseif ($personal_info_changed) {
                $message = '<div class="message success">ব্যক্তিগত তথ্যাদি সফলভাবে আপডেট হয়েছে।</div>';
            } elseif ($image_update_successful) {
                $message = '<div class="message success">প্রোফাইল ছবি সফলভাবে আপডেট হয়েছে!</div>';
            } elseif (empty($message)) {
                $message = '<div class="message error">কোনো তথ্য পরিবর্তন করা হয়নি।</div>';
            }
            
            // যদি আপডেট সফল হয় তবে ডেটা পুনরায় লোড করুন
            if ($update_successful) {
                $user_info = findUserByPhone($user_phone);
            }
        } 
        
        // এই else ব্লকটি পূর্বের কোনো পরিবর্তন না হওয়ার ক্ষেত্রে মেসেজটি দেখাবে।
        // যদি এটি ব্লক না হয়, কিন্তু personal_info_changed বা image_data_base64 না থাকে, তখন এই মেসেজ আসবে
        else {
            $message = '<div class="message error">কোনো তথ্য পরিবর্তন করা হয়নি।</div>';
        }
    }
}

// এই কোডটি POST রিকোয়েস্টের পরে ছবির সঠিক পাথ পুনরায় নিশ্চিত করে।
$currentProfileImageSrc = $profile_has_image ? $profileImagePath . '?' . time() : $default_photo_path;
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ইউজার ড্যাশবোর্ড</title>
    <link rel="stylesheet" href="style.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>স্বাগতম, <?php echo htmlspecialchars($user_info['username']); ?>!</h2>
        <?php echo $message; ?>
        
        <form id="profileUpdateForm" method="post" action="dashboard.php">
            
            <div class="profile-box" style="margin-top: 0px; margin-bottom: 20px;">
                <h3>প্রোফাইল ছবি আপডেট</h3>
                
                <div style="text-align: center; margin-bottom: 15px;">
                    <img id="profile_display_image" 
                         src="<?php echo htmlspecialchars($currentProfileImageSrc); ?>" 
                         alt="প্রোফাইল ছবি" 
                         style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #ccc;">
                </div>
                
                <div class="form-group">
                    <label for="profile_image">ছবি নির্বাচন করুন</label>
                    <input type="file" id="profile_image" accept="image/*"> 
                </div>

                <div id="image_crop_area" style="display: none; text-align: center; margin-top: 20px;">
                    <div id="crop_container" style="max-width: 100%; max-height: 400px; overflow: hidden; margin: 0 auto;">
                        <img id="image_to_crop" style="max-width: 100%; display: block;">
                    </div>
                    
                    <button type="button" id="crop_button" class="btn" style="background-color: #ff9800; margin-top: 15px; display: block;">ছবি ক্রপ করুন ও সেভ করুন</button>
                </div>
                
                <input type="hidden" id="cropped_image_data" name="cropped_image_data" value="">
                
                <p id="image_status" style="color: green; font-weight: bold; margin-top: 10px; display: none;">✅ ছবি আপলোড সফল হয়েছে। </p>
            </div>
            
            <hr>
            
            <div class="form-group">
                <label>ইউজার নেম</label>
                <input type="text" value="<?php echo htmlspecialchars($user_info['username']); ?>" readonly style="background-color: #eee;">
            </div>
            <div class="form-group">
                <label>মোবাইল নাম্বার</label>
                <input type="tel" value="<?php echo htmlspecialchars($user_info['phone']); ?>" readonly style="background-color: #eee;">
            </div>

            <div class="form-group">
                <label for="full_name">সম্পূর্ণ নাম <span style="color: red;">*</span></label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_info['full_name'] ?: ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="father_name">বাবার নাম</label>
                <input type="text" id="father_name" name="father_name" value="<?php echo htmlspecialchars($user_info['father_name'] ?: ''); ?>">
            </div>
            <div class="form-group">
                <label for="mother_name">মায়ের নাম</label>
                <input type="text" id="mother_name" name="mother_name" value="<?php echo htmlspecialchars($user_info['mother_name'] ?: ''); ?>">
            </div>
            <div class="form-group">
                <label for="address">ঠিকানা <span style="color: red;">*</span></label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user_info['address'] ?: ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="religion">ধর্ম</label>
                <input type="text" id="religion" name="religion" value="<?php echo htmlspecialchars($user_info['religion'] ?: ''); ?>">
            </div>
            
            <button type="submit" class="btn">তথ্য আপডেট করুন</button>
        </form>
        <br>
        <div class="links">
            <a href="?logout=true">লগ আউট</a>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageInput = document.getElementById('profile_image');
        const imageToCrop = document.getElementById('image_to_crop');
        const cropArea = document.getElementById('image_crop_area');
        const cropButton = document.getElementById('crop_button');
        const croppedImageDataInput = document.getElementById('cropped_image_data');
        const imageStatus = document.getElementById('image_status');
        const profileDisplayImage = document.getElementById('profile_display_image');
        let cropper = null; 

        // 1. ছবি সিলেক্ট করা হলে
        imageInput.addEventListener('change', (e) => {
            const files = e.target.files;
            if (files && files.length > 0) {
                const reader = new FileReader();
                reader.onload = () => {
                    imageToCrop.src = reader.result;
                    cropArea.style.display = 'block';
                    cropButton.style.display = 'block';
                    imageStatus.style.display = 'none';
                    croppedImageDataInput.value = ''; 
                    
                    if (cropper) {
                        cropper.destroy();
                    }
                    
                    setTimeout(() => {
                        cropper = new Cropper(imageToCrop, {
                            aspectRatio: 1 / 1, 
                            viewMode: 1, 
                            movable: true,
                            zoomable: true,
                            scalable: true,
                        });
                    }, 100);
                };
                reader.readAsDataURL(files[0]);
            }
        });

        // 2. 'ছবি ক্রপ করুন ও সেভ করুন' বাটনে ক্লিক করা হলে
        cropButton.addEventListener('click', () => {
            if (cropper) {
                const croppedDataURL = cropper.getCroppedCanvas({
                    width: 256, 
                    height: 256,
                }).toDataURL('image/jpeg', 0.8); 

                // তাৎক্ষণিক ছবি আপডেট
                profileDisplayImage.src = croppedDataURL; 
                
                cropper.destroy(); 
                cropper = null;

                // ক্রপ এলাকা লুকানো
                cropButton.style.display = 'none';
                cropArea.style.display = 'none';
                imageStatus.style.display = 'block';
                
                // ক্রপ করা ডেটা হিডেন ইনপুট ফিল্ডে সেভ করা
                croppedImageDataInput.value = croppedDataURL;
            }
        });
    });
</script>
</body>
</html>