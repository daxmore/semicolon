import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';
import * as yup from 'yup';
import { supabase } from '../../lib/supabaseClient';
import { axiosClient } from '../../lib/axiosClient';
import { Mail, Shield, KeyRound, AlertCircle, CheckCircle2, ArrowRight, ArrowLeft } from 'lucide-react';
import Logo from '../../components/common/Logo';

const emailSchema = yup.object({
  email: yup.string().email('Please enter a valid email address').required('Email is required'),
});

const securitySchema = yup.object({
  securityAnswer: yup.string().required('Security answer is required'),
  newPassword: yup
    .string()
    .min(6, 'Password must be at least 6 characters')
    .matches(/[A-Z]/, 'Password must include at least one uppercase letter')
    .matches(/[0-9]/, 'Password must include at least one number')
    .matches(/[\W_]/, 'Password must include at least one special character')
    .required('New password is required'),
});

export default function ForgotPassword() {
  const [step, setStep] = useState(1); // 1: Email, 2: Security Question, 3: Email sent link
  const [profileData, setProfileData] = useState(null);
  const [serverError, setServerError] = useState('');
  const [successMessage, setSuccessMessage] = useState('');
  const [loading, setLoading] = useState(false);

  const {
    register: registerEmail,
    handleSubmit: handleSubmitEmail,
    formState: { errors: emailErrors },
  } = useForm({
    resolver: yupResolver(emailSchema),
  });

  const {
    register: registerSecurity,
    handleSubmit: handleSubmitSecurity,
    formState: { errors: securityErrors },
  } = useForm({
    resolver: yupResolver(securitySchema),
  });

  // Step 1: Find user and check for security question or send email link
  const onEmailSubmit = async (values) => {
    try {
      setServerError('');
      setLoading(true);

      // Check if user profile exists
      const { data: profiles } = await axiosClient.get(
        `/rest/v1/profiles?email=eq.${encodeURIComponent(values.email)}&select=*`
      );

      if (!profiles || profiles.length === 0) {
        setServerError('No account found with this email address.');
        setLoading(false);
        return;
      }

      const userProfile = profiles[0];
      setProfileData(userProfile);

      if (userProfile.security_question && userProfile.security_answer) {
        setStep(2); // Can answer security question
      } else {
        // Fallback to Supabase reset email
        const { error } = await supabase.auth.resetPasswordForEmail(values.email, {
          redirectTo: `${window.location.origin}/forgot-password?type=recovery`,
        });
        if (error) throw error;
        setStep(3);
      }
    } catch (err) {
      console.error(err);
      setServerError(err.message || 'Failed to process password reset request.');
    } finally {
      setLoading(false);
    }
  };

  // Step 2: Verify security answer
  const onSecuritySubmit = async (values) => {
    try {
      setServerError('');
      setLoading(true);

      if (
        profileData.security_answer.trim().toLowerCase() !==
        values.securityAnswer.trim().toLowerCase()
      ) {
        setServerError('Incorrect answer to security question.');
        setLoading(false);
        return;
      }

      // Send Supabase magic recovery link or update
      const { error } = await supabase.auth.resetPasswordForEmail(profileData.email);
      if (error) throw error;

      setSuccessMessage('Answer verified! Password reset instructions have been sent to your email.');
      setTimeout(() => setStep(3), 1500);
    } catch (err) {
      console.error(err);
      setServerError(err.message || 'Error verifying security answer.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-bg-primary">
      <div className="max-w-md w-full space-y-8 bg-white p-8 sm:p-10 rounded-2xl border border-zinc-200/80 shadow-xl shadow-zinc-200/40">
        <div className="text-center space-y-2">
          <div className="flex justify-center mb-4">
            <Logo />
          </div>
          <h2 className="text-2xl font-bold font-heading text-zinc-900 tracking-tight">
            Reset password
          </h2>
          <p className="text-xs text-zinc-500">
            {step === 1 && "Enter your email to recover your account access."}
            {step === 2 && "Answer your security question to confirm identity."}
            {step === 3 && "Check your inbox for the recovery link."}
          </p>
        </div>

        {serverError && (
          <div className="p-3.5 rounded-xl bg-rose-50 border border-rose-200/80 flex items-start gap-2.5 text-xs text-rose-700 animate-in fade-in">
            <AlertCircle className="h-4 w-4 shrink-0 mt-0.5 text-rose-500" />
            <span>{serverError}</span>
          </div>
        )}

        {successMessage && (
          <div className="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200/80 flex items-start gap-2.5 text-xs text-emerald-700 animate-in fade-in">
            <CheckCircle2 className="h-4 w-4 shrink-0 mt-0.5 text-emerald-500" />
            <span>{successMessage}</span>
          </div>
        )}

        {step === 1 && (
          <form className="mt-8 space-y-5" onSubmit={handleSubmitEmail(onEmailSubmit)}>
            <div>
              <label className="block text-xs font-semibold text-zinc-700 mb-1.5">
                Email address
              </label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                  <Mail className="h-4 w-4" />
                </div>
                <input
                  type="email"
                  {...registerEmail('email')}
                  placeholder="developer@example.com"
                  className={`w-full pl-9 pr-4 py-2.5 bg-zinc-50/50 border rounded-xl text-xs text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition ${
                    emailErrors.email ? 'border-rose-300 bg-rose-50/30' : 'border-zinc-200'
                  }`}
                />
              </div>
              {emailErrors.email && (
                <p className="mt-1 text-[11px] text-rose-600 font-medium">{emailErrors.email.message}</p>
              )}
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full flex items-center justify-center gap-2 py-2.5 px-4 border border-transparent rounded-xl shadow-md shadow-indigo-500/20 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition disabled:opacity-50"
            >
              {loading ? (
                <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
              ) : (
                <>
                  <span>Continue</span>
                  <ArrowRight className="h-3.5 w-3.5" />
                </>
              )}
            </button>
          </form>
        )}

        {step === 2 && profileData && (
          <form className="mt-8 space-y-5" onSubmit={handleSubmitSecurity(onSecuritySubmit)}>
            <div className="p-4 rounded-xl bg-indigo-50/50 border border-indigo-100 space-y-1">
              <span className="text-[11px] font-bold text-indigo-700 uppercase tracking-wider flex items-center gap-1.5">
                <Shield className="h-3.5 w-3.5" /> Security Challenge
              </span>
              <p className="text-xs font-semibold text-zinc-800">{profileData.security_question}</p>
            </div>

            <div>
              <label className="block text-xs font-semibold text-zinc-700 mb-1.5">
                Your Answer
              </label>
              <input
                type="text"
                {...registerSecurity('securityAnswer')}
                placeholder="Enter exact answer"
                className={`w-full px-4 py-2.5 bg-zinc-50/50 border rounded-xl text-xs text-zinc-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition ${
                  securityErrors.securityAnswer ? 'border-rose-300 bg-rose-50/30' : 'border-zinc-200'
                }`}
              />
              {securityErrors.securityAnswer && (
                <p className="mt-1 text-[11px] text-rose-600 font-medium">
                  {securityErrors.securityAnswer.message}
                </p>
              )}
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full flex items-center justify-center gap-2 py-2.5 px-4 border border-transparent rounded-xl shadow-md shadow-indigo-500/20 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition disabled:opacity-50"
            >
              {loading ? (
                <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
              ) : (
                <>
                  <span>Verify and Reset</span>
                  <ArrowRight className="h-3.5 w-3.5" />
                </>
              )}
            </button>
          </form>
        )}

        {step === 3 && (
          <div className="text-center py-6 space-y-4">
            <div className="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto">
              <Mail className="h-6 w-6" />
            </div>
            <p className="text-xs text-zinc-600 leading-relaxed">
              We've dispatched recovery instructions. Please open the link in your email to choose a new password.
            </p>
            <Link
              to="/login"
              className="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-indigo-600 hover:bg-indigo-50 transition"
            >
              <ArrowLeft className="h-3.5 w-3.5" />
              Back to login
            </Link>
          </div>
        )}

        <div className="pt-4 border-t border-zinc-100 text-center">
          <Link
            to="/login"
            className="inline-flex items-center gap-1 text-xs font-medium text-zinc-500 hover:text-zinc-700 transition"
          >
            <ArrowLeft className="h-3.5 w-3.5" />
            Back to sign in
          </Link>
        </div>
      </div>
    </div>
  );
}
