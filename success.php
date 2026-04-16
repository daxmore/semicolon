<?php
require_once 'vendor/autoload.php';
require_once 'includes/stripe_config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$session_id = $_GET['session_id'] ?? '';

if (!$session_id) {
    header('Location: pricing.php');
    exit();
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

try {
    $session = \Stripe\Checkout\Session::retrieve($session_id);
    
    if ($session->payment_status === 'paid') {
        // Payment successful
        
        // 1. Update user table
        $stmt = $conn->prepare("UPDATE users SET is_pro = 1 WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        
        // 2. Insert into subscriptions table
        $amount = $session->amount_total / 100;
        $currency = $session->currency;
        $status = $session->payment_status;
        $payment_intent = $session->payment_intent;
        
        $stmt_sub = $conn->prepare("INSERT INTO subscriptions (user_id, stripe_checkout_id, stripe_payment_intent, amount, currency, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_sub->bind_param('issdss', $user_id, $session_id, $payment_intent, $amount, $currency, $status);
        $stmt_sub->execute();

        // 3. Notify user
        require_once 'includes/functions.php';
        $p_message = "Welcome to Pro! You paid ₹499, so you’re definitely finishing that course now. We’ve removed the ads—there's nothing left to blame but your own procrastination. Congratulations, you’ve officially paid to suffer less. 😌";
        create_notification($user_id, "You've Officially Leveled Up!", $p_message, "system", "dashboard.php");

        // Redirect with success message
        $_SESSION['toasts'][] = ['type' => 'success', 'title' => 'Welcome to Pro!', 'message' => 'Congratulations... you paid to suffer less. 😌'];
        header('Location: dashboard.php');
        exit();
    } else {
        $_SESSION['toasts'][] = ['type' => 'error', 'title' => 'Payment Failed', 'message' => 'Your payment was not successful. Please try again.'];
        header('Location: pricing.php');
        exit();
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
