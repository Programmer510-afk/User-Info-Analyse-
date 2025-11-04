<?php
// print_report.php
include 'data.php';

// ফোল্ডার পাথ কনস্ট্যান্ট সেট করা
$photo_dir = 'images/user_photo/';
$default_photo_name = 'default_profile.png'; 
$default_photo_path = 'images/' . $default_photo_name; 

if (!isset($_GET['phone']) || strlen($_GET['phone']) !== 11) {
    die('Invalid phone number.');
}

$phone = $_GET['phone'];
$user_info = findUserByPhone($phone);

if (!$user_info) {
    die('User data not found.');
}

// ছবির পাথ লজিক
$profileImageFileName = $phone . '_profile.jpg';
$profileImagePath = $photo_dir . $profileImageFileName;
$currentImageSrc = file_exists($profileImagePath) ? $profileImagePath . '?' . time() : $default_photo_path; // ক্যাশ এড়ানো
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ইউজার প্রোফাইল রিপোর্ট</title>
    <style>
        /* A4 সাইজের মতো দেখতে প্রিন্ট স্টাইল */
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px 0;
            font-size: 11pt;
            box-sizing: border-box;
            background-color: #f0f0f0;
            text-align: center;
        }
        .report-page {
            position: relative; 
            width: 210mm; 
            min-height: auto; 
            margin: 20mm auto; 
            background-color: white;
            padding: 20mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: left; 
        }
        
        /* 1. ছবির জন্য ফিক্সড কন্টেইনার (সেন্টিমিটারে সাইজ) */
        .image-container {
            /* absolute পজিশনিং যা প্রিন্টেও কাজ করবে */
            position: absolute;
            top: 20mm;
            /* ডানদিক কাটা রোধ করতে মার্জিন বাড়ানো হলো */
            right: 20mm; /* 20mm প্যাডিং + 5mm অতিরিক্ত মার্জিন */
            left: auto;
            width: 4cm; /* ~100px */
            height: 4cm; /* বর্গাকার নিশ্চিত করতে */
            border: 1px solid #000;
            overflow: hidden;
            z-index: 10;
        }
        /* 2. ছবিকে কন্টেইনারের ভিতরে ফিট করা */
        .print-profile-image {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover; 
            /*object-fit: contain; যদি কাটে তবে এটি ব্যবহার করুন*/
        }
        
        h1 { 
            text-align: center; 
            color: #333; 
            border-bottom: 2px solid #ccc; 
            padding-bottom: 10px; 
            margin-top: 0; 
            margin-bottom: 30px; 
            padding-right: 120px; 
            box-sizing: border-box;
        }
        
        /* 3. ডেটা টেবিল: ছবির নিচে শুরু হবে */
        .data-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 55mm; 
        }
        
        .data-table tr:nth-child(even) { background-color: #f9f9f9; }
        .data-table th, .data-table td { 
            border: 1px solid #ddd; 
            padding: 10px; 
            text-align: left; 
        }
        .data-table th { background-color: #eee; width: 35%; }
        
        /* *** প্রিন্ট মিডিয়ার জন্য চূড়ান্ত সংশোধিত স্টাইল *** */
        
        .print-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16pt;
        }
    </style>
</head>
<body>
    <div class="report-page">
        <div class="image-container">
              <table>
                <tr>
                    <td><img src="<?php echo htmlspecialchars($currentImageSrc); ?>" alt="প্রোফাইল ছবি" class="print-profile-image"></td>
                </tr>
              </table>
        </div>
        
        <div>
          <h1>ব্যক্তিগত তথ্যাদির রিপোর্ট</h1>
        </div>
        

        <table class="data-table">
            <tr>
                <th>ইউজার নেম</th>
                <td><?php echo htmlspecialchars($user_info['username']); ?></td>
            </tr>
            <tr>
                <th>মোবাইল নাম্বার</th>
                <td><?php echo htmlspecialchars($user_info['phone']); ?></td>
            </tr>
            <tr>
                <th>সম্পূর্ণ নাম</th>
                <td><?php echo htmlspecialchars($user_info['full_name'] ?: '---'); ?></td>
            </tr>
            <tr>
                <th>বাবার নাম</th>
                <td><?php echo htmlspecialchars($user_info['father_name'] ?: '---'); ?></td>
            </tr>
            <tr>
                <th>মায়ের নাম</th>
                <td><?php echo htmlspecialchars($user_info['mother_name'] ?: '---'); ?></td>
            </tr>
            <tr>
                <th>ঠিকানা</th>
                <td><?php echo htmlspecialchars($user_info['address'] ?: '---'); ?></td>
            </tr>
            <tr>
                <th>ধর্ম</th>
                <td><?php echo htmlspecialchars($user_info['religion'] ?: '---'); ?></td>
            </tr>
        </table>
        
        <p style="margin-top: 30px; font-size: 10pt; text-align: right;">রিপোর্ট তৈরির সময়: <?php echo date('Y-m-d H:i:s'); ?></p>

        <button class="print-button" onclick="window.print()">
            Print / Save as PDF
        </button>

    </div>
</body>
</html>