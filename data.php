<?php
// ডাটা ফাইল পাথ
define('DATA_FILE', 'user_data.json');

// ডাটা ফাইল তৈরি করে যদি না থাকে
if (!file_exists(DATA_FILE)) {
    file_put_contents(DATA_FILE, json_encode([]));
}

/**
 * সকল ইউজার ডাটা লোড করে
 * @return array
 */
function loadUsers() {
    $json = file_get_contents(DATA_FILE);
    return json_decode($json, true);
}

/**
 * নতুন ইউজার সেভ করে
 * @param array $newUser
 * @return bool
 */
function saveUser($newUser) {
    $users = loadUsers();
    
    // ফোন নাম্বার যাচাই
    foreach ($users as $user) {
        if ($user['phone'] === $newUser['phone']) {
            return false; // একই ফোন নাম্বার দিয়ে সাইন আপ হবে না
        }
    }

    $users[] = $newUser;
    return file_put_contents(DATA_FILE, json_encode($users, JSON_PRETTY_PRINT));
}

/**
 * সাইন ইন এর জন্য ইউজার খুঁজে বের করে
 * @param string $phone
 * @param string $password
 * @return array|null
 */
function findUserForSignIn($phone, $password) {
    $users = loadUsers();
    foreach ($users as $user) {
        // পাসওয়ার্ড চেক: নিরাপত্তার জন্য এখানে `password_verify` ব্যবহার করা উচিত, 
        // কিন্তু সরলীকরণের জন্য সরাসরি মিলানো হচ্ছে।
        if ($user['phone'] === $phone && $user['password'] === $password) {
            return $user;
        }
    }
    return null;
}

/**
 * প্রোফাইল আপডেট করে
 * @param string $phone
 * @param array $updates
 * @return bool
 */
function updateProfile($phone, $updates) {
    $users = loadUsers();
    $updated = false;

    foreach ($users as $key => $user) {
        if ($user['phone'] === $phone) {
            // কেবল আপডেটযোগ্য ফিল্ডগুলো আপডেট করা হবে
            $users[$key]['full_name'] = $updates['full_name'] ?? $user['full_name'];
            $users[$key]['father_name'] = $updates['father_name'] ?? $user['father_name'];
            $users[$key]['mother_name'] = $updates['mother_name'] ?? $user['mother_name'];
            $users[$key]['address'] = $updates['address'] ?? $user['address'];
            $users[$key]['religion'] = $updates['religion'] ?? $user['religion'];
            $updated = true;
            break;
        }
    }

    if ($updated) {
        return file_put_contents(DATA_FILE, json_encode($users, JSON_PRETTY_PRINT));
    }
    return false;
}

/**
 * ফোন নাম্বার দিয়ে ইউজার খুঁজে বের করে
 * @param string $phone
 * @return array|null
 */
function findUserByPhone($phone) {
    $users = loadUsers();
    foreach ($users as $user) {
        if ($user['phone'] === $phone) {
            return $user;
        }
    }
    return null;
}
?>