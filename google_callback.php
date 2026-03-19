<?php
/**
 * Google OAuth Callback Handler
 * Handles redirect from Google OAuth
 */

session_start();
require_once '../config/db.php';

// Google OAuth Configuration
$google_client_id = "YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com";
$google_client_secret = "YOUR_GOOGLE_CLIENT_SECRET";
$redirect_uri = "https://yourdomain.com/includes/google_callback.php";

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Exchange code for access token
    $token_url = 'https://oauth2.googleapis.com/token';
    $token_data = [
        'code' => $code,
        'client_id' => $google_client_id,
        'client_secret' => $google_client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $token_response = json_decode($response, true);
    
    if (isset($token_response['access_token'])) {
        $access_token = $token_response['access_token'];
        
        // Get user info
        $user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $access_token;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $user_info_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $user_response = curl_exec($ch);
        curl_close($ch);
        
        $user_info = json_decode($user_response, true);
        
        if (isset($user_info['email'])) {
            $google_id = $user_info['id'];
            $email = $conn->real_escape_string($user_info['email']);
            $name = $user_info['name'] ?? '';
            $given_name = $user_info['given_name'] ?? $name;
            $family_name = $user_info['family_name'] ?? '';
            $picture = $user_info['picture'] ?? '';
            
            // Check if user exists
            $sql = "SELECT * FROM user WHERE email = '$email' OR google_id = '$google_id'";
            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                // User exists - log them in
                $user = $result->fetch_assoc();
                $user_id = $user['user_id'];
                $user_type = $user['user_type'];
                
                // Update Google ID if not set
                if (empty($user['google_id'])) {
                    $picture_escaped = $conn->real_escape_string($picture);
                    $conn->query("UPDATE user SET google_id = '$google_id', profile_picture = '$picture_escaped' WHERE user_id = '$user_id'");
                }
                
                // Set session
                $_SESSION['GBDELIVERING_CUSTOMER_USER_2021'] = true;
                $_SESSION['GBDELIVERING_USER_ID_2021'] = $user_id;
                $_SESSION['customer_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_type'] = $user_type;
                
                // Merge cart
                if (isset($_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'])) {
                    $temp_id = $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'];
                    $conn->query("UPDATE cart SET customer_id = '$user_id' WHERE customer_id = '$temp_id'");
                }
                
                $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'] = $user_id;
                
                header('Location: ../index.php?google_success=1');
                exit;
                
            } else {
                // Create new user
                $user_id = 'USER_' . time() . '_' . rand(1000, 9999);
                $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $given_name)) . rand(100, 999);
                $first_name = $conn->real_escape_string($given_name);
                $last_name = $conn->real_escape_string($family_name);
                $picture_escaped = $conn->real_escape_string($picture);
                
                $random_password = bin2hex(random_bytes(16));
                $password_hash = password_hash($random_password, PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO user (user_id, user_type, first_name, last_name, email, username, password, google_id, profile_picture, approval, register_date) 
                        VALUES ('$user_id', 'CLIENT', '$first_name', '$last_name', '$email', '$username', '$password_hash', '$google_id', '$picture_escaped', 'APPROVED', NOW())";
                
                if ($conn->query($sql)) {
                    $_SESSION['GBDELIVERING_CUSTOMER_USER_2021'] = true;
                    $_SESSION['GBDELIVERING_USER_ID_2021'] = $user_id;
                    $_SESSION['customer_name'] = $first_name . ' ' . $last_name;
                    $_SESSION['user_type'] = 'CLIENT';
                    
                    if (isset($_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'])) {
                        $temp_id = $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'];
                        $conn->query("UPDATE cart SET customer_id = '$user_id' WHERE customer_id = '$temp_id'");
                    }
                    
                    $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'] = $user_id;
                    
                    header('Location: ../index.php?google_success=1&new_user=1');
                    exit;
                }
            }
        }
    }
    
    // Error
    header('Location: ../index.php?sign-in&google_error=auth_failed');
    exit;
    
} elseif (isset($_GET['error'])) {
    header('Location: ../index.php?sign-in&google_error=' . urlencode($_GET['error']));
    exit;
} else {
    header('Location: ../index.php?sign-in');
    exit;
}
?>