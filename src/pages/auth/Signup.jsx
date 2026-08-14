import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';
import * as yup from 'yup';
import { useAuth } from '../../contexts/AuthContext';
import { axiosClient } from '../../lib/axiosClient';
import { Eye, EyeOff, Lock, Mail, User, Shield, AlertCircle, CheckCircle2, ArrowRight } from 'lucide-react';
import Logo from '../../components/common/Logo';

const schema = yup.object({
  username: yup
    .string()
    .min(3, 'Username must be at least 3 characters')
    .max(30, 'Username cannot exceed 30 characters')
    .matches(/^[a-zA-Z0-9_]+$/, 'Username can only contain letters, numbers, and underscores')
    .required('Username is required'),
  email: yup.string().email('Please enter a valid email address').required('Email is required'),
  password: yup
    .string()
    .min(6, 'Password must be at least 6 characters')
    .matches(/[A-Z]/, 'Password must include at least one uppercase letter')
    .matches(/[0-9]/, 'Password must include at least one number')
    .matches(/[\W_]/, 'Password must include at least one special character')
    .required('Password is required'),
  confirmPassword: yup
    .string()
    .oneOf([yup.ref('password'), null], 'Passwords must match')
    .required('Confirm password is required'),
  securityQuestion: yup.string().required('Security question is required'),
  securityAnswer: yup.string().min(2, 'Security answer is required').required('Security answer is required'),
});

const SECURITY_QUESTIONS = [
  "What is the name of your first developer mentor?",
  "What was your first programming language?",
  "What was the model of your first computer?",
  "In what city did you write your first line of code?",
  "What is your favorite code editor?",
];

export default function Signup() {
  const { signUp } = useAuth();
  const navigate = useNavigate();
  const [showPassword, setShowPassword] = useState(false);
  const [serverError, setServerError] = useState('');
  const [successMessage, setSuccessMessage] = useState('');
  const [loading, setLoading] = useState(false);

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm({
    resolver: yupResolver(schema),
  });

  const onSubmit = async (values) => {
    try {
      setServerError('');
      setSuccessMessage('');
      setLoading(true);

      // Check if username is already taken via public profiles
      const { data: existingUsers } = await axiosClient.get(
        `/rest/v1/profiles?username=eq.${encodeURIComponent(values.username)}&select=id`
      );

      if (existingUsers && existingUsers.length > 0) {
        setServerError('This username is already taken. Please choose another one.');
        setLoading(false);
        return;
      }

      // Supabase Signup with user metadata (picked up by Postgres trigger to create public.profiles)
      const res = await signUp(values.email, values.password, values.username);

      // If user is returned immediately (no email confirm) or we have session, update security questions
      if (res?.user?.id) {
        try {
          await axiosClient.patch(`/rest/v1/profiles?id=eq.${res.user.id}`, {
            security_question: values.securityQuestion,
            security_answer: values.securityAnswer,
          });
        } catch (err) {
          console.warn('Could not immediately update security answer:', err);
        }
      }

      setSuccessMessage('Account created successfully! Redirecting...');
      setTimeout(() => {
        navigate('/dashboard');
      }, 1200);
    } catch (err) {
      console.error(err);
      setServerError(err.message || 'Failed to create an account. Please try again.');
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
            Create an account
          </h2>
          <p className="text-xs text-zinc-500">
            Join the semicolon community to unlock resources and start earning XP.
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

        <form className="mt-8 space-y-4" onSubmit={handleSubmit(onSubmit)}>
          {/* Username */}
          <div>
            <label className="block text-xs font-semibold text-zinc-700 mb-1.5">
              Username
            </label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                <User className="h-4 w-4" />
              </div>
              <input
                type="text"
                {...register('username')}
                placeholder="syntax_samurai"
                className={`w-full pl-9 pr-4 py-2.5 bg-zinc-50/50 border rounded-xl text-xs text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition ${
                  errors.username ? 'border-rose-300 bg-rose-50/30' : 'border-zinc-200'
                }`}
              />
            </div>
            {errors.username && (
              <p className="mt-1 text-[11px] text-rose-600 font-medium">{errors.username.message}</p>
            )}
          </div>

          {/* Email */}
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
                {...register('email')}
                placeholder="you@domain.com"
                className={`w-full pl-9 pr-4 py-2.5 bg-zinc-50/50 border rounded-xl text-xs text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition ${
                  errors.email ? 'border-rose-300 bg-rose-50/30' : 'border-zinc-200'
                }`}
              />
            </div>
            {errors.email && (
              <p className="mt-1 text-[11px] text-rose-600 font-medium">{errors.email.message}</p>
            )}
          </div>

          {/* Password */}
          <div>
            <label className="block text-xs font-semibold text-zinc-700 mb-1.5">
              Password
            </label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                <Lock className="h-4 w-4" />
              </div>
              <input
                type={showPassword ? 'text' : 'password'}
                {...register('password')}
                placeholder="••••••••••••"
                className={`w-full pl-9 pr-10 py-2.5 bg-zinc-50/50 border rounded-xl text-xs text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition ${
                  errors.password ? 'border-rose-300 bg-rose-50/30' : 'border-zinc-200'
                }`}
              />
              <button
                type="button"
                onClick={() => setShowPassword(!showPassword)}
                className="absolute inset-y-0 right-0 pr-3 flex items-center text-zinc-400 hover:text-zinc-600"
              >
                {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
              </button>
            </div>
            {errors.password && (
              <p className="mt-1 text-[11px] text-rose-600 font-medium">{errors.password.message}</p>
            )}
          </div>

          {/* Confirm Password */}
          <div>
            <label className="block text-xs font-semibold text-zinc-700 mb-1.5">
              Confirm Password
            </label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                <Lock className="h-4 w-4" />
              </div>
              <input
                type={showPassword ? 'text' : 'password'}
                {...register('confirmPassword')}
                placeholder="••••••••••••"
                className={`w-full pl-9 pr-4 py-2.5 bg-zinc-50/50 border rounded-xl text-xs text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition ${
                  errors.confirmPassword ? 'border-rose-300 bg-rose-50/30' : 'border-zinc-200'
                }`}
              />
            </div>
            {errors.confirmPassword && (
              <p className="mt-1 text-[11px] text-rose-600 font-medium">{errors.confirmPassword.message}</p>
            )}
          </div>

          {/* Security Question (from legacy signup step 2) */}
          <div className="pt-2 border-t border-zinc-100">
            <label className="block text-xs font-semibold text-zinc-700 mb-1.5 flex items-center gap-1.5">
              <Shield className="h-3.5 w-3.5 text-indigo-500" />
              Security Question (for recovery)
            </label>
            <select
              {...register('securityQuestion')}
              className={`w-full px-3 py-2.5 bg-zinc-50/50 border rounded-xl text-xs text-zinc-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition ${
                errors.securityQuestion ? 'border-rose-300 bg-rose-50/30' : 'border-zinc-200'
              }`}
            >
              <option value="">Select a security question</option>
              {SECURITY_QUESTIONS.map((q, idx) => (
                <option key={idx} value={q}>
                  {q}
                </option>
              ))}
            </select>
            {errors.securityQuestion && (
              <p className="mt-1 text-[11px] text-rose-600 font-medium">{errors.securityQuestion.message}</p>
            )}
          </div>

          {/* Security Answer */}
          <div>
            <label className="block text-xs font-semibold text-zinc-700 mb-1.5">
              Security Answer
            </label>
            <input
              type="text"
              {...register('securityAnswer')}
              placeholder="Your answer"
              className={`w-full px-4 py-2.5 bg-zinc-50/50 border rounded-xl text-xs text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition ${
                errors.securityAnswer ? 'border-rose-300 bg-rose-50/30' : 'border-zinc-200'
              }`}
            />
            {errors.securityAnswer && (
              <p className="mt-1 text-[11px] text-rose-600 font-medium">{errors.securityAnswer.message}</p>
            )}
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full flex items-center justify-center gap-2 py-2.5 px-4 mt-6 border border-transparent rounded-xl shadow-md shadow-indigo-500/20 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {loading ? (
              <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
            ) : (
              <>
                <span>Create account</span>
                <ArrowRight className="h-3.5 w-3.5" />
              </>
            )}
          </button>
        </form>

        <div className="pt-4 border-t border-zinc-100 text-center">
          <p className="text-xs text-zinc-500">
            Already have an account?{' '}
            <Link to="/login" className="font-semibold text-indigo-600 hover:text-indigo-700 transition">
              Sign in
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}
