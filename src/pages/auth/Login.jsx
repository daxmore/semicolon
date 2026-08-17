import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';
import * as yup from 'yup';
import { useAuth } from '../../contexts/AuthContext';
import Logo from '../../components/common/Logo';
import { BookOpen } from 'lucide-react';

const schema = yup.object({
  email: yup.string().email('Please enter a valid email address').required('Email is required'),
  password: yup.string().required('Password is required'),
});

export default function Login() {
  const { signIn } = useAuth();
  const navigate = useNavigate();
  const [serverError, setServerError] = useState('');
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
      setLoading(true);
      await signIn(values.email, values.password);
      navigate('/dashboard');
    } catch (err) {
      console.error(err);
      setServerError(err.message || 'Invalid email or password.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="antialiased bg-zinc-100 min-h-screen flex items-center justify-center p-4">
      <div className="w-full max-w-5xl bg-white rounded-3xl shadow-2xl overflow-hidden flex min-h-[600px] border border-zinc-200">
        
        {/* Left Side - Form */}
        <div className="w-full lg:w-1/2 p-10 lg:p-16 flex flex-col justify-between">
          <div>
            {/* Logo */}
            <div className="mb-8">
              <Logo className="h-7 w-auto" />
            </div>
            
            {/* Icon + Title side by side */}
            <div className="flex items-center gap-4 mb-6">
              <div className="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center flex-shrink-0 text-white shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <div>
                <h1 className="text-xl font-bold text-zinc-900 font-heading">Welcome Back</h1>
                <p className="text-zinc-500 text-xs mt-0.5">Sign in to your account</p>
              </div>
            </div>

            {/* Error Messages */}
            {serverError && (
              <div className="mb-4 p-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-xs">
                <p>{serverError}</p>
              </div>
            )}

            {/* Separator */}
            <div className="w-10 h-px bg-zinc-200 mb-6"></div>

            {/* Form */}
            <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
              <div>
                <label className="block text-xs font-medium text-zinc-600 mb-1">Username</label>
                <input 
                  type="text" 
                  {...register('email')}
                  required
                  className="input-underline w-full text-zinc-900 text-sm"
                  placeholder="Enter your username"
                />
                {errors.email && (
                  <p className="text-red-500 text-[11px] mt-1">{errors.email.message}</p>
                )}
              </div>
              
              <div>
                <div className="flex items-center justify-between mb-1">
                  <label className="block text-xs font-medium text-zinc-600">Password</label>
                  <Link to="/forgot-password" className="text-xs text-zinc-500 hover:text-indigo-600">Forgot?</Link>
                </div>
                <input 
                  type="password" 
                  {...register('password')}
                  required
                  className="input-underline w-full text-zinc-900 text-sm"
                  placeholder="Enter your password"
                />
                {errors.password && (
                  <p className="text-red-500 text-[11px] mt-1">{errors.password.message}</p>
                )}
              </div>
              
              <button 
                type="submit"
                disabled={loading}
                className="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-full transition-colors mt-6 disabled:opacity-50 text-sm cursor-pointer shadow-md"
              >
                {loading ? 'Signing in...' : 'Sign in'}
              </button>
            </form>
          </div>

          <p className="mt-8 text-center text-zinc-500 text-xs">
            Don't have an account? <Link to="/signup" className="text-zinc-900 font-medium hover:text-indigo-600">Sign up</Link>
          </p>
        </div>
        
        {/* Right Side - Branding */}
        <div className="hidden lg:flex lg:w-1/2 indigo-gradient relative overflow-hidden p-12 flex-col justify-between">
          <div className="absolute top-1/4 right-1/4 w-96 h-96 bg-indigo-400/20 rounded-full blur-3xl"></div>
          <div className="absolute bottom-1/4 left-1/4 w-64 h-64 bg-purple-400/10 rounded-full blur-3xl"></div>
          
          <div></div>
          
          {/* Main headline */}
          <div className="relative z-10 my-auto">
            <h2 className="font-serif text-5xl text-white leading-tight">
              <span className="italic">Enter</span><br />
              <span className="italic">the Future</span><br />
              <span className="font-semibold">of Learning,</span><br />
              <span className="font-semibold">today</span>
            </h2>
          </div>
          
          {/* Floating Card Container */}
          <div className="relative z-10 card-float w-fit">
            <div className="bg-white rounded-2xl p-5 shadow-2xl w-64">
              <div className="flex items-center gap-3 mb-3">
                <div className="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0 text-white">
                  <BookOpen className="h-5 w-5" />
                </div>
                <div>
                  <p className="text-xl font-bold text-zinc-900 font-heading">847</p>
                  <p className="text-[11px] text-zinc-500">Total Resources</p>
                </div>
              </div>
              <div className="flex items-center justify-between pt-3 border-t border-zinc-100 text-xs">
                <div className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full bg-indigo-500"></div>
                  <span className="text-zinc-600">Active Learners</span>
                </div>
                <span className="font-bold text-zinc-900">5,234</span>
              </div>
            </div>
            
            {/* Small floating bottom right icon button */}
            <div className="absolute -bottom-3 -right-3 w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
              <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                <path strokeLinecap="round" strokeLinejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </div>
          </div>
        </div>

      </div>
    </div>
  );
}
