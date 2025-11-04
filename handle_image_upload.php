<?php
// ইমেজ ডেটা এবং ফোন নাম্বার POST রিকোয়েস্ট থেকে নেওয়া
$imageData = $_POST['image_data'] ?? '';
$phone = $_POST['phone'] ?? '';

// সাধারণ ভ্যালিডেশন
if (empty($imageData) || empty($phone)) {
    die("Error: No image data or phone number received.");
}
if (!preg_match('/^[0-9]{11}$/', $phone)) {
    die("Error: Invalid phone number format.");
}

// 1. ইমেজ ফাইলটির বেস৬৪ অংশ আলাদা করা
if (strpos($imageData, 'data:image') === 0) {
    list($type, $imageData) = explode(';', $imageData);
    list(, $imageData) = explode(',', $imageData);
}

// 2. বেস৬৪ থেকে বাইনারি ডেটা ডিকোড করা
$imageData = base64_decode($imageData);

if ($imageData === false) {
    die("Error: Failed to decode image data.");
}

// 3. সেভ করার জন্য ফাইলের নাম তৈরি করা
$fileName = $phone . '_profile.jpg';
$uploadDir = 'images/'; 

// নিশ্চিত করুন যে images ফোল্ডারটি বিদ্যমান
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 4. ফাইলটি সেভ করা
if (file_put_contents($uploadDir . $fileName, $imageData)) {
    echo "Success! Profile image saved.";
} else {
    echo "Error: Could not save the image file. Check folder permissions (0777).";
}
?>