<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'includes/db.php';
require_once 'includes/stripe_config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get subscription and its Stripe payment intent
$subscription = get_user_subscription($user_id);

if ($subscription && $subscription['status'] === 'paid') {
    // 1. Locally cancel in DB
    if (cancel_subscription($user_id)) {
        
        // 2. Stripe Refund (this will show as 'Refunded' or 'Cancelled' in Stripe)
        if (!empty($subscription['stripe_payment_intent'])) {
            try {
                \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
                \Stripe\Refund::create([
                    'payment_intent' => $subscription['stripe_payment_intent'],
                ]);
                
                $_SESSION['toasts'][] = ['type' => 'success', 'title' => 'Stripe Sync Successful', 'message' => 'Status has been updated in Stripe dashboard too.'];
            } catch (Exception $e) {
                // If refund fails (e.g. already refunded), we just log it or notify
                $_SESSION['toasts'][] = ['type' => 'warning', 'title' => 'Stripe Notice', 'message' => 'Local plan cancelled, but Stripe dashboard update failed: ' . $e->getMessage()];
            }
        }

        $c_message = "You’ve cancelled Semicolon Pro. Your skills won’t grow themselves… but we respect the confidence. You've been downgraded to the Free tier, but you know where the 'Upgrade' button is when you're ready to get serious again. 😉";
        create_notification($user_id, "Confidence is Great, Pro is Better", $c_message, "system", "pricing.php");
        $_SESSION['toasts'][] = ['type' => 'info', 'title' => 'Cancelled Pro', 'message' => "Skills won't grow themselves... but okay. 😉"];
    } else {
        $_SESSION['toasts'][] = ['type' => 'error', 'title' => 'Cancellation Failed', 'message' => 'Something went wrong. Please try again.'];
    }
} else {
    $_SESSION['toasts'][] = ['type' => 'info', 'title' => 'No Active Plan', 'message' => 'You do not have an active session to cancel.'];
}

header('Location: dashboard.php');
exit();
?>
