<?php
ob_start();
session_start();
require_once 'vendor/autoload.php';
require_once 'includes/stripe_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$YOUR_DOMAIN = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
// Detect if we are in a subdirectory like /semicolon
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/semicolon'; // Default base path for this project
$YOUR_DOMAIN .= $base_path;

// Fetch user email if not in session
$user_email = $_SESSION['user_email'] ?? '';
if (!$user_email && isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $user_email = $row['email'];
        $_SESSION['user_email'] = $user_email;
    }
}

try {
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'inr',
                'product_data' => [
                    'name' => 'Pro Plan - Semicolon',
                    'description' => 'Unlimited access to all books, papers, and premium features.',
                ],
                'unit_amount' => 49900, // Amount in paise (499 INR)
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => $YOUR_DOMAIN . '/success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $YOUR_DOMAIN . '/pricing.php',
        'customer_email' => $user_email,
    ]);

    header("Location: " . $checkout_session->url);
    exit();
} catch (Exception $e) {
    echo "Stripe Error: " . $e->getMessage();
    exit();
}
?>
