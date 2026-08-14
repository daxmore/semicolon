import { serve } from 'https://deno.land/std@0.177.0/http/server.ts';
import Stripe from 'https://esm.sh/stripe@12.18.0?target=deno';
import { createClient } from 'https://esm.sh/@supabase/supabase-js@2';

const stripe = new Stripe(Deno.env.get('STRIPE_SECRET_KEY') || '', {
  httpClient: Stripe.createFetchHttpClient(),
});

const cryptoProvider = Stripe.createSubtleCryptoProvider();

const supabaseAdmin = createClient(
  Deno.env.get('SUPABASE_URL') || '',
  Deno.env.get('SUPABASE_SERVICE_ROLE_KEY') || ''
);

serve(async (req) => {
  const signature = req.headers.get('Stripe-Signature');
  const body = await req.text();

  let event;

  try {
    const webhookSecret = Deno.env.get('STRIPE_WEBHOOK_SECRET');
    if (webhookSecret && signature) {
      event = await stripe.webhooks.constructEventAsync(
        body,
        signature,
        webhookSecret,
        undefined,
        cryptoProvider
      );
    } else {
      event = JSON.parse(body);
    }
  } catch (err) {
    console.error('Webhook signature verification failed:', err.message);
    return new Response(`Webhook Error: ${err.message}`, { status: 400 });
  }

  if (event.type === 'checkout.session.completed') {
    const session = event.data.object;
    const userId = session.metadata?.userId;

    if (userId) {
      // 1. Upgrade user in profiles table
      await supabaseAdmin
        .from('profiles')
        .update({ is_pro: true })
        .eq('id', userId);

      // 2. Record subscription in subscriptions table
      await supabaseAdmin.from('subscriptions').insert({
        user_id: userId,
        stripe_checkout_id: session.id,
        stripe_payment_intent: session.payment_intent,
        amount: (session.amount_total || 49900) / 100,
        currency: session.currency || 'inr',
        status: 'paid',
      });

      // 3. Create celebratory notification
      await supabaseAdmin.from('notifications').insert({
        user_id: userId,
        type: 'system',
        title: "You've Officially Leveled Up!",
        message: "Welcome to Pro! You paid ₹499, so you’re definitely finishing that course now. Ads are removed—there's nothing left to blame but your own procrastination. 😌",
        link: '/dashboard',
      });
    }
  }

  return new Response(JSON.stringify({ received: true }), {
    headers: { 'Content-Type': 'application/json' },
  });
});
