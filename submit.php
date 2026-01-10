<?php
/**
 * Newsletter Signup API - Main Form Submission Handler
 * 
 * This script handles the initial newsletter signup form submission,
 * validates data, stores it in the database, and sends verification email.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Database configuration
require_once 'config.php';

// Helper functions
require_once 'functions.php';

try {
    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate required fields
    $errors = validateFormData($data);
    if (!empty($errors)) {
        throw new Exception(implode(', ', $errors));
    }
    
    // Connect to database
    $conn = getDatabaseConnection();
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        
        if ($stmt->fetch()) {
            throw new Exception('Email already registered');
        }
        
        // Generate verification token
        $verificationToken = bin2hex(random_bytes(32));

        // Generate unique short_id
        $shortId = generateUniqueShortId($conn);

        // Insert user data
        $stmt = $conn->prepare("
            INSERT INTO users (
                short_id,
                first_name,
                email,
                phone,
                country_code,
                age_verified,
                email_consent,
                sms_consent,
                language_preference,
                verification_token,
                subscription_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([
            $shortId,
            sanitizeInput($data['firstName']),
            sanitizeInput($data['email']),
            sanitizeInput($data['phone'] ?? null),
            sanitizeInput($data['countryCode'] ?? null),
            $data['ageVerified'] ? 1 : 0,
            $data['emailConsent'] ? 1 : 0,
            $data['smsConsent'] ? 1 : 0,
            sanitizeInput($data['language'] ?? 'en'),
            $verificationToken
        ]);
        
        $userId = $conn->lastInsertId();
        
        // Insert technical data
        $techData = $data['technicalData'] ?? [];
        $jurisdiction = $data['jurisdiction'] ?? [];
        
        $stmt = $conn->prepare("
            INSERT INTO user_technical_data (
                user_id,
                ip_address,
                country,
                country_code,
                city,
                region,
                timezone,
                user_agent,
                browser,
                device_type,
                operating_system,
                platform,
                screen_resolution,
                viewport_size,
                color_depth,
                detected_language,
                languages,
                referrer,
                utm_source,
                utm_medium,
                utm_campaign,
                utm_term,
                utm_content
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            sanitizeInput($jurisdiction['ip'] ?? $_SERVER['REMOTE_ADDR']),
            sanitizeInput($jurisdiction['country'] ?? null),
            sanitizeInput($jurisdiction['countryCode'] ?? null),
            sanitizeInput($jurisdiction['city'] ?? null),
            sanitizeInput($jurisdiction['region'] ?? null),
            sanitizeInput($jurisdiction['timezone'] ?? $techData['timezone'] ?? null),
            sanitizeInput($techData['userAgent'] ?? $_SERVER['HTTP_USER_AGENT']),
            sanitizeInput($techData['browser'] ?? null),
            sanitizeInput($techData['deviceType'] ?? 'Desktop'),
            sanitizeInput($techData['operatingSystem'] ?? null),
            sanitizeInput($techData['platform'] ?? null),
            sanitizeInput($techData['screenResolution'] ?? null),
            sanitizeInput($techData['viewport'] ?? null),
            isset($techData['colorDepth']) ? (int)$techData['colorDepth'] : null,
            sanitizeInput($techData['language'] ?? null),
            sanitizeInput($techData['languages'] ?? null),
            sanitizeInput($techData['referrer'] ?? null),
            sanitizeInput($techData['utmParams']['source'] ?? null),
            sanitizeInput($techData['utmParams']['medium'] ?? null),
            sanitizeInput($techData['utmParams']['campaign'] ?? null),
            sanitizeInput($techData['utmParams']['term'] ?? null),
            sanitizeInput($techData['utmParams']['content'] ?? null)
        ]);
        
        // Log consent
        $stmt = $conn->prepare("
            INSERT INTO consent_log (
                user_id,
                ip_address,
                cookie_consent,
                tracking_consent,
                user_agent
            ) VALUES (?, ?, 'accepted', 'accepted', ?)
        ");
        
        $stmt->execute([
            $userId,
            sanitizeInput($jurisdiction['ip'] ?? $_SERVER['REMOTE_ADDR']),
            sanitizeInput($techData['userAgent'] ?? $_SERVER['HTTP_USER_AGENT'])
        ]);
        
        // Commit transaction
        $conn->commit();
        
        // Send verification email (in production, use a proper email service)
        $verificationLink = "https://yourdomain.com/verify.php?token=" . $verificationToken;
        sendVerificationEmail($data['email'], $data['firstName'], $verificationLink, $data['language'] ?? 'en');
        
        // Log email activity
        $stmt = $conn->prepare("
            INSERT INTO email_activity (user_id, email_type, subject)
            VALUES (?, 'verification', 'Confirm your email address')
        ");
        $stmt->execute([$userId]);
        
        // Return success response with both userId and shortId
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'userId' => $userId,
            'shortId' => $shortId
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
    // Log error (in production, use proper logging)
    error_log('Signup error: ' . $e->getMessage());
}

/**
 * Validate form data
 */
function validateFormData($data) {
    $errors = [];
    
    // First name
    if (empty($data['firstName']) || strlen(trim($data['firstName'])) < 2) {
        $errors[] = 'Valid first name is required';
    }
    
    // Email
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email address is required';
    }
    
    // Phone (if provided)
    if (!empty($data['phone'])) {
        $phone = preg_replace('/[^0-9]/', '', $data['phone']);
        if (strlen($phone) < 6) {
            $errors[] = 'Valid phone number is required';
        }
    }
    
    // Age verification
    if (empty($data['ageVerified']) || $data['ageVerified'] !== true) {
        $errors[] = 'Age verification is required';
    }
    
    // Email consent
    if (empty($data['emailConsent']) || $data['emailConsent'] !== true) {
        $errors[] = 'Email consent is required';
    }
    
    // SMS consent (if phone provided)
    if (!empty($data['phone']) && (empty($data['smsConsent']) || $data['smsConsent'] !== true)) {
        $errors[] = 'SMS consent is required when providing phone number';
    }
    
    return $errors;
}
?>
