<?php
/**
 * Google Authentication Handler
 * Handles Google Sign-In callback
 */

session_start();
require_once '../config/db.php';

// Google Client ID (same as in sign-in.php)
$google_client_id = "YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com";

header('Content-Type: application/json');

// Function to verify Google token
function verifyGoogleToken($credential, $client_id) {
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . $credential;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return false;
    }
    
    $payload = json_decode($response, true);
    
    // Verify the token is for our app
    if ($payload['aud'] !== $client_id) {
        return false;
    }
    
    return $payload;
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'google_signin') {
        $credential = isset($_POST['credential']) ? $_POST['credential'] : '';
        
        if (empty($credential)) {
            echo json_encode(['success' => false, 'message' => 'No credential provided']);
            exit;
        }
        
        // Verify the token
        $payload = verifyGoogleToken($credential, $google_client_id);
        
        if (!$payload) {
            echo json_encode(['success' => false, 'message' => 'Invalid Google token']);
            exit;
        }
        
        // Extract user info
        $google_id = $payload['sub'];
        $email = $payload['email'];
        $email_verified = $payload['email_verified'] ?? false;
        $name = $payload['name'] ?? '';
        $given_name = $payload['given_name'] ?? '';
        $family_name = $payload['family_name'] ?? '';
        $picture = $payload['picture'] ?? '';
        
        // Check if user exists by email
        $email_escaped = $conn->real_escape_string($email);
        $sql = "SELECT * FROM user WHERE email = '$email_escaped' OR google_id = '$google_id'";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            // User exists - log them in
            $user = $result->fetch_assoc();
            $user_id = $user['user_id'];
            $user_type = $user['user_type'];
            
            // Update Google ID if not set
            if (empty($user['google_id'])) {
                $conn->query("UPDATE user SET google_id = '$google_id', profile_picture = '$picture' WHERE user_id = '$user_id'");
            }
            
            // Set session variables
            $_SESSION['GBDELIVERING_CUSTOMER_USER_2021'] = true;
            $_SESSION['GBDELIVERING_USER_ID_2021'] = $user_id;
            $_SESSION['customer_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_type'] = $user_type;
            
            // Merge cart if temp customer exists
            if (isset($_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'])) {
                $temp_id = $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'];
                // Update cart to use real user ID
                $conn->query("UPDATE cart SET customer_id = '$user_id' WHERE customer_id = '$temp_id'");
            }
            
            $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'] = $user_id;
            
            // Determine redirect
            $redirect = 'index.php';
            if ($user_type === 'ADMIN') {
                $redirect = 'modules/admin/';
            } elseif ($user_type === 'SELLER') {
                $redirect = 'modules/seller/';
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'name' => $user['first_name'],
                'redirect' => $redirect
            ]);
            
        } else {
            // New user - create account
            $user_id = 'USER_' . time() . '_' . rand(1000, 9999);
            $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $given_name)) . rand(100, 999);
            $first_name = $conn->real_escape_string($given_name ?: $name);
            $last_name = $conn->real_escape_string($family_name ?: '');
            $picture_escaped = $conn->real_escape_string($picture);
            
            // Generate a random password (user won't need it for Google login)
            $random_password = bin2hex(random_bytes(16));
            $password_hash = password_hash($random_password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO user (user_id, user_type, first_name, last_name, email, username, password, google_id, profile_picture, approval, register_date) 
                    VALUES ('$user_id', 'CLIENT', '$first_name', '$last_name', '$email_escaped', '$username', '$password_hash', '$google_id', '$picture_escaped', 'APPROVED', NOW())";
            
            if ($conn->query($sql)) {
                // Set session
                $_SESSION['GBDELIVERING_CUSTOMER_USER_2021'] = true;
                $_SESSION['GBDELIVERING_USER_ID_2021'] = $user_id;
                $_SESSION['customer_name'] = $first_name . ' ' . $last_name;
                $_SESSION['user_type'] = 'CLIENT';
                
                // Merge cart if temp customer exists
                if (isset($_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'])) {
                    $temp_id = $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'];
                    $conn->query("UPDATE cart SET customer_id = '$user_id' WHERE customer_id = '$temp_id'");
                }
                
                $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'] = $user_id;
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Account created successfully',
                    'name' => $first_name,
                    'redirect' => 'index.php',
                    'new_user' => true
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create account: ' . $conn->error]);
            }
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>