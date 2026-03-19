<?php
session_start();
require_once 'db_connection.php'; // Include your database connection

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$google_id = $conn->real_escape_string($input['google_id'] ?? '');
$email = $conn->real_escape_string($input['email'] ?? '');
$first_name = $conn->real_escape_string($input['first_name'] ?? '');
$last_name = $conn->real_escape_string($input['last_name'] ?? '');
$picture = $conn->real_escape_string($input['picture'] ?? '');
$email_verified = $input['email_verified'] ?? false;

if (empty($email) || empty($google_id)) {
    echo json_encode(['success' => false, 'message' => 'Missing required information']);
    exit;
}

try {
    // Check if user exists with this Google ID
    $sql = "SELECT * FROM customer WHERE google_id = '$google_id' OR email = '$email'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        // User exists - log them in
        $user = $result->fetch_assoc();
        $customer_id = $user['customer_id'];
        
        // Update Google ID if not set
        if (empty($user['google_id'])) {
            $update_sql = "UPDATE customer SET google_id = '$google_id', profile_picture = '$picture' WHERE customer_id = '$customer_id'";
            $conn->query($update_sql);
        }
        
        // Set session
        $_SESSION['GBDELIVERING_CUSTOMER_USER_2021'] = $customer_id;
        $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'] = $customer_id;
        
        // Set cookie for 30 days
        setcookie('GBDELIVERING_CUSTOMER_USER_2021', $customer_id, time() + (30 * 24 * 60 * 60), '/');
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => 'index.php?dashboard',
            'user' => [
                'id' => $customer_id,
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'email' => $user['email']
            ]
        ]);
        
    } else {
        // New user - create account
        $customer_id = md5(uniqid(rand(), true));
        $username = strtolower($first_name . '.' . substr($google_id, 0, 6));
        $random_password = md5($google_id . time()); // Random password for Google users
        
        $insert_sql = "INSERT INTO customer (
            customer_id, 
            google_id,
            username, 
            first_name, 
            last_name, 
            email, 
            password,
            profile_picture,
            status,
            register_date
        ) VALUES (
            '$customer_id',
            '$google_id',
            '$username',
            '$first_name',
            '$last_name',
            '$email',
            '$random_password',
            '$picture',
            'Active',
            NOW()
        )";
        
        if ($conn->query($insert_sql)) {
            // Set session
            $_SESSION['GBDELIVERING_CUSTOMER_USER_2021'] = $customer_id;
            $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'] = $customer_id;
            
            // Set cookie
            setcookie('GBDELIVERING_CUSTOMER_USER_2021', $customer_id, time() + (30 * 24 * 60 * 60), '/');
            
            echo json_encode([
                'success' => true,
                'message' => 'Account created and logged in',
                'redirect' => 'index.php?dashboard',
                'new_user' => true
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create account: ' . $conn->error]);
        }
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>