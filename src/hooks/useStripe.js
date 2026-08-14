import { useMutation } from '@tanstack/react-query';
import { axiosClient } from '../lib/axiosClient';

/**
 * Hook to initiate Stripe checkout via Supabase Edge Function
 */
export function useStripeCheckout() {
  return useMutation({
    mutationFn: async ({ userId, email }) => {
      const returnUrl = window.location.origin;

      const { data } = await axiosClient.post(
        '/functions/v1/create-checkout-session',
        {
          userId,
          email,
          returnUrl,
        }
      );

      if (data?.url) {
        window.location.href = data.url;
      }
      return data;
    },
  });
}
