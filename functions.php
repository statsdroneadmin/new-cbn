<?php
/**
 * Helper Functions
 * 
 * Common utility functions used across the application
 */

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    if ($data === null) {
        return null;
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    return $data;
}

/**
 * Send verification email
 */
function sendVerificationEmail($email, $firstName, $verificationLink, $language = 'en') {
    // Email templates by language
    $templates = [
        'en' => [
            'subject' => 'Confirm Your Email Address',
            'body' => "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: linear-gradient(135deg, #FFB800 0%, #E52D27 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                        .button { display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #FFB800 0%, #E52D27 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
                        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>Welcome to Gaming Newsletter!</h1>
                        </div>
                        <div class='content'>
                            <p>Hi {$firstName},</p>
                            <p>Thank you for signing up! We're excited to bring you exclusive casino offers, VIP bonuses, and insider deals.</p>
                            <p>To start receiving offers, please verify your email address by clicking the button below:</p>
                            <p style='text-align: center;'>
                                <a href='{$verificationLink}' class='button'>Verify Email Address</a>
                            </p>
                            <p>Or copy and paste this link into your browser:</p>
                            <p style='word-break: break-all; background: white; padding: 10px; border-radius: 5px;'>{$verificationLink}</p>
                            <p>This link will expire in 24 hours.</p>
                            <p>If you didn't sign up for this newsletter, please ignore this email.</p>
                            <p>Best regards,<br>The Gaming Newsletter Team</p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2024 Gaming Newsletter. All rights reserved.</p>
                            <p>
                                <a href='" . APP_URL . "/privacy-policy.html'>Privacy Policy</a> | 
                                <a href='" . APP_URL . "/terms.html'>Terms & Conditions</a>
                            </p>
                        </div>
                    </div>
                </body>
                </html>
            "
        ],
        'es' => [
            'subject' => 'Confirma tu Dirección de Correo',
            'body' => "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: linear-gradient(135deg, #FFB800 0%, #E52D27 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                        .button { display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #FFB800 0%, #E52D27 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
                        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>¡Bienvenido a Gaming Newsletter!</h1>
                        </div>
                        <div class='content'>
                            <p>Hola {$firstName},</p>
                            <p>¡Gracias por registrarte! Estamos emocionados de traerte ofertas exclusivas de casino, bonos VIP y ofertas privilegiadas.</p>
                            <p>Para comenzar a recibir ofertas, verifica tu dirección de correo haciendo clic en el botón a continuación:</p>
                            <p style='text-align: center;'>
                                <a href='{$verificationLink}' class='button'>Verificar Correo Electrónico</a>
                            </p>
                            <p>Este enlace expirará en 24 horas.</p>
                            <p>Saludos,<br>El Equipo de Gaming Newsletter</p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2024 Gaming Newsletter. Todos los derechos reservados.</p>
                        </div>
                    </div>
                </body>
                </html>
            "
        ]
    ];
    
    $template = $templates[$language] ?? $templates['en'];
    
    // In production, use PHPMailer or a proper email service like SendGrid, Mailgun, etc.
    // This is a simplified example using PHP's mail() function
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">" . "\r\n";
    
    // For production, use PHPMailer:
    /*
    require 'vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email, $firstName);
        
        $mail->isHTML(true);
        $mail->Subject = $template['subject'];
        $mail->Body = $template['body'];
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
    */
    
    // Simplified version (not recommended for production)
    return mail($email, $template['subject'], $template['body'], $headers);
}

/**
 * Send welcome email
 */
function sendWelcomeEmail($email, $firstName, $language = 'en') {
    $templates = [
        'en' => [
            'subject' => 'Welcome to Gaming Newsletter!',
            'body' => "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: linear-gradient(135deg, #FFB800 0%, #E52D27 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                        .offer-box { background: white; border-left: 4px solid #FFB800; padding: 20px; margin: 20px 0; border-radius: 5px; }
                        .button { display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #FFB800 0%, #E52D27 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>🎉 You're In!</h1>
                        </div>
                        <div class='content'>
                            <p>Hi {$firstName},</p>
                            <p>Welcome to the Gaming Newsletter family! You've just unlocked access to exclusive casino bonuses and VIP offers.</p>
                            <div class='offer-box'>
                                <h3>🎁 Your First Exclusive Offer</h3>
                                <p><strong>200% Welcome Bonus + 50 Free Spins</strong></p>
                                <p>Available at our top-rated partner casino!</p>
                            </div>
                            <p>Here's what you can expect from us:</p>
                            <ul>
                                <li>Weekly exclusive bonuses tailored to your preferences</li>
                                <li>VIP-only promotions and birthday rewards</li>
                                <li>Early access to new casino games</li>
                                <li>Expert tips and strategies</li>
                            </ul>
                            <p style='text-align: center;'>
                                <a href='" . APP_URL . "' class='button'>View Latest Offers</a>
                            </p>
                            <p>Stay tuned for your personalized offers!</p>
                            <p>Best regards,<br>The Gaming Newsletter Team</p>
                        </div>
                    </div>
                </body>
                </html>
            "
        ]
    ];
    
    $template = $templates[$language] ?? $templates['en'];
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">" . "\r\n";
    
    return mail($email, $template['subject'], $template['body'], $headers);
}

/**
 * Rate limiting check
 */
function checkRateLimit($identifier, $maxRequests = null, $timeWindow = 60) {
    if (!ENABLE_RATE_LIMITING) {
        return true;
    }
    
    $maxRequests = $maxRequests ?? MAX_REQUESTS_PER_MINUTE;
    
    // Use Redis or database for production
    // This is a simplified file-based example
    $cacheDir = __DIR__ . '/../cache';
    if (!file_exists($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    $cacheFile = $cacheDir . '/rate_limit_' . md5($identifier) . '.txt';
    
    $now = time();
    $requests = [];
    
    if (file_exists($cacheFile)) {
        $requests = json_decode(file_get_contents($cacheFile), true) ?? [];
        // Remove old requests
        $requests = array_filter($requests, function($timestamp) use ($now, $timeWindow) {
            return $timestamp > ($now - $timeWindow);
        });
    }
    
    if (count($requests) >= $maxRequests) {
        return false;
    }
    
    $requests[] = $now;
    file_put_contents($cacheFile, json_encode($requests));
    
    return true;
}

/**
 * Generate a secure random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Generate a unique 8-character alphanumeric short ID
 *
 * This function ensures uniqueness by:
 * 1. Using cryptographically secure random generation
 * 2. Checking database for existing short_id
 * 3. Retrying up to 10 times if collision detected
 * 4. Using uppercase alphanumeric characters (A-Z, 0-9) = 36^8 = 2.8 trillion combinations
 *
 * @param PDO $conn Database connection
 * @return string 8-character unique short ID
 * @throws Exception if unable to generate unique ID after max attempts
 */
function generateUniqueShortId($conn) {
    $maxAttempts = 10;
    $attempts = 0;

    // Character set: A-Z and 0-9 (36 characters)
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $charactersLength = strlen($characters);

    while ($attempts < $maxAttempts) {
        $attempts++;
        $shortId = '';

        // Generate 8 random characters using cryptographically secure random
        for ($i = 0; $i < 8; $i++) {
            // Use random_int for cryptographically secure random number generation
            $randomIndex = random_int(0, $charactersLength - 1);
            $shortId .= $characters[$randomIndex];
        }

        // Check if this short_id already exists in database
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE short_id = ?");
        $stmt->execute([$shortId]);
        $count = $stmt->fetchColumn();

        // If unique, return it
        if ($count == 0) {
            return $shortId;
        }

        // Log collision for monitoring (rare event)
        error_log("Short ID collision detected: {$shortId} (attempt {$attempts})");
    }

    // This should be extremely rare with 36^8 combinations
    throw new Exception('Unable to generate unique short ID after ' . $maxAttempts . ' attempts');
}

/**
 * Get user by short_id
 *
 * @param PDO $conn Database connection
 * @param string $shortId Short ID to lookup
 * @return array|false User data or false if not found
 */
function getUserByShortId($conn, $shortId) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE short_id = ?");
    $stmt->execute([$shortId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Hash password (if needed for future admin panel)
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Log activity
 */
function logActivity($message, $level = 'info') {
    $logDir = __DIR__ . '/../logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[{$timestamp}] [{$level}] {$message}\n";
    
    file_put_contents($logFile, $entry, FILE_APPEND);
}
?>
