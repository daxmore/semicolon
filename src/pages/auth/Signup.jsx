import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';
import * as yup from 'yup';
import { useAuth } from '../../contexts/AuthContext';
import { supabase } from '../../lib/supabaseClient';
import Logo from '../../components/common/Logo';
import { BookOpen, Users, ShieldCheck, ArrowLeft, ArrowRight, Check } from 'lucide-react';

const SECURITY_QUESTIONS = [
  "What was your first pet's name?",
  "What city were you born in?",
  "What is your mother's maiden name?",
  "What was the name of your primary school?",
  "What was your favorite teacher's name?",
  "What was the make of your first car?",
  "What is your favorite book or movie?"
];

const step1Schema = yup.object({
  username: yup
    .string()
    .required('Username is required')
    .min(3, 'Username must be at least 3 characters')
    .max(20, 'Username must be at most 20 characters')
    .matches(/^[a-zA-Z0-9_]+$/, 'Only letters, numbers, and underscores are allowed'),
  email: yup.string().email('Please enter a valid email address').required('Email is required'),
  password: yup
    .string()
    .required('Password is required')
    .min(6, 'Password must be at least 6 characters'),
});

const step2Schema = yup.object({
  security_question: yup.string().required('Please select a security question'),
  security_answer: yup.string().required('Please provide an answer to your security question'),
});

export default function Signup() {
  const { signUp } = useAuth();
  const navigate = useNavigate();
  
  const [step, setStep] = useState(1);
  const [step1Data, setStep1Data] = useState(null);
  const [serverError, setServerError] = useState('');
  const [loading, setLoading] = useState(false);

  // Step 1 Form
  const {
    register: registerStep1,
    handleSubmit: handleSubmitStep1,
    formState: { errors: errorsStep1 },
  } = useForm({
    resolver: yupResolver(step1Schema),
    defaultValues: step1Data || {},
  });

  // Step 2 Form
  const {
    register: registerStep2,
    handleSubmit: handleSubmitStep2,
    formState: { errors: errorsStep2 },
  } = useForm({
    resolver: yupResolver(step2Schema),
  });

  // Step 1 Submit -> Proceed to Step 2
  const onStep1Submit = (values) => {
    setServerError('');
    setStep1Data(values);
    setStep(2);
  };

  // Step 2 Submit -> Complete signup & persist user profile
  const onStep2Submit = async (values) => {
    try {
      setServerError('');
      setLoading(true);
      
      const payload = {
        email: step1Data.email,
        password: step1Data.password,
        username: step1Data.username,
        security_question: values.security_question,
        security_answer: values.security_answer,
      };

      const { data, error } = await supabase.auth.signUp({
        email: payload.email,
        password: payload.password,
        options: {
          data: {
            username: payload.username,
            security_question: payload.security_question,
            security_answer: payload.security_answer,
          },
        },
      });

      if (error) throw error;

      // If user session is immediate or requires profile update
      if (data?.user) {
        await supabase
          .from('profiles')
          .update({
            security_question: values.security_question,
            security_answer: values.security_answer.trim().toLowerCase(),
          })
          .eq('id', data.user.id);
      }

      navigate('/dashboard', { replace: true });
    } catch (err) {
      console.error(err);
      setServerError(err.message || 'Error creating account. Please try again.');
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
              <div className="w-12 h-12 bg-teal-600 rounded-2xl flex items-center justify-center flex-shrink-0 text-white shadow-md">
                {step === 1 ? (
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                  </svg>
                ) : (
                  <ShieldCheck className="h-6 w-6" />
                )}
              </div>
              <div>
                <h1 className="text-xl font-bold text-zinc-900 font-heading">
                  {step === 1 ? 'Get Started' : 'Security Question'}
                </h1>
                <p className="text-zinc-500 text-xs mt-0.5">
                  {step === 1 ? 'Step 1 of 2' : 'Step 2 of 2 — Account Recovery'}
                </p>
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

            {/* Step 1: Account Credentials */}
            {step === 1 && (
              <form onSubmit={handleSubmitStep1(onStep1Submit)} className="space-y-4">
                <div>
                  <label className="block text-xs font-medium text-zinc-600 mb-1">Username</label>
                  <input 
                    type="text" 
                    {...registerStep1('username')}
                    required
                    className="input-underline w-full text-zinc-900 text-sm"
                    placeholder="Choose a username"
                  />
                  {errorsStep1.username && (
                    <p className="text-red-500 text-[11px] mt-1">{errorsStep1.username.message}</p>
                  )}
                </div>

                <div>
                  <label className="block text-xs font-medium text-zinc-600 mb-1">Email ID</label>
                  <input 
                    type="email" 
                    {...registerStep1('email')}
                    required
                    className="input-underline w-full text-zinc-900 text-sm"
                    placeholder="Enter your email"
                  />
                  {errorsStep1.email && (
                    <p className="text-red-500 text-[11px] mt-1">{errorsStep1.email.message}</p>
                  )}
                </div>
                
                <div>
                  <label className="block text-xs font-medium text-zinc-600 mb-1">Password</label>
                  <input 
                    type="password" 
                    {...registerStep1('password')}
                    required
                    className="input-underline w-full text-zinc-900 text-sm"
                    placeholder="Create a password"
                  />
                  {errorsStep1.password && (
                    <p className="text-red-500 text-[11px] mt-1">{errorsStep1.password.message}</p>
                  )}
                </div>
                
                <button 
                  type="submit"
                  className="w-full py-3.5 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-full transition-colors mt-6 text-sm cursor-pointer shadow-md flex items-center justify-center gap-2"
                >
                  Next Step ›
                </button>
              </form>
            )}

            {/* Step 2: Security Question for Account Recovery */}
            {step === 2 && (
              <form onSubmit={handleSubmitStep2(onStep2Submit)} className="space-y-4 animate-in fade-in duration-200">
                <div>
                  <label className="block text-xs font-medium text-zinc-600 mb-1">Security Question</label>
                  <select 
                    {...registerStep2('security_question')}
                    required
                    className="input-underline w-full text-zinc-900 text-sm bg-transparent cursor-pointer"
                  >
                    <option value="" disabled selected>Select a security question</option>
                    {SECURITY_QUESTIONS.map((q, idx) => (
                      <option key={idx} value={q}>{q}</option>
                    ))}
                  </select>
                  {errorsStep2.security_question && (
                    <p className="text-red-500 text-[11px] mt-1">{errorsStep2.security_question.message}</p>
                  )}
                </div>

                <div>
                  <label className="block text-xs font-medium text-zinc-600 mb-1">Your Answer</label>
                  <input 
                    type="text" 
                    {...registerStep2('security_answer')}
                    required
                    className="input-underline w-full text-zinc-900 text-sm"
                    placeholder="Enter your answer"
                  />
                  {errorsStep2.security_answer && (
                    <p className="text-red-500 text-[11px] mt-1">{errorsStep2.security_answer.message}</p>
                  )}
                </div>

                <div className="flex gap-3 pt-4">
                  <button 
                    type="button"
                    onClick={() => setStep(1)}
                    className="py-3 px-5 border border-zinc-200 hover:bg-zinc-50 text-zinc-700 font-medium rounded-full transition text-sm cursor-pointer flex items-center gap-1.5"
                  >
                    <ArrowLeft className="h-4 w-4" /> Back
                  </button>
                  <button 
                    type="submit"
                    disabled={loading}
                    className="flex-1 py-3.5 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-full transition-colors disabled:opacity-50 text-sm cursor-pointer shadow-md flex items-center justify-center gap-2"
                  >
                    {loading ? 'Creating Account...' : 'Complete Signup'}
                  </button>
                </div>
              </form>
            )}
          </div>

          <p className="mt-8 text-center text-zinc-500 text-xs">
            Already have an account? <Link to="/login" className="text-zinc-900 font-medium hover:text-teal-600">Log in</Link>
          </p>
        </div>
        
        {/* Right Side - Branding */}
        <div className="hidden lg:flex lg:w-1/2 teal-gradient relative overflow-hidden p-12 flex-col justify-between">
          <div className="absolute top-1/4 right-1/4 w-96 h-96 bg-teal-400/20 rounded-full blur-3xl"></div>
          <div className="absolute bottom-1/4 left-1/4 w-64 h-64 bg-emerald-400/10 rounded-full blur-3xl"></div>
          
          {/* Top floating icon */}
          <div className="w-9 h-9 rounded-xl bg-white/10 backdrop-blur border border-white/20 flex items-center justify-center text-white">
            <BookOpen className="h-4 w-4" />
          </div>
          
          {/* Main headline */}
          <div className="relative z-10 my-auto">
            <h2 className="font-serif text-5xl text-white leading-tight">
              <span className="italic">Start</span><br />
              <span className="italic">Your Journey</span><br />
              <span className="font-semibold">to Mastery,</span><br />
              <span className="font-semibold">today</span>
            </h2>
          </div>
          
          {/* Floating Card Container */}
          <div className="relative z-10 card-float w-fit">
            <div className="bg-white rounded-2xl p-5 shadow-2xl w-64 space-y-3">
              <div className="flex items-center gap-3 mb-2">
                <div className="w-8 h-8 bg-teal-600 rounded-xl flex items-center justify-center flex-shrink-0 text-white">
                  <Users className="h-4 w-4" />
                </div>
                <div>
                  <p className="text-lg font-bold text-zinc-900 font-heading">5,234</p>
                  <p className="text-[10px] text-zinc-500">Happy Learners</p>
                </div>
              </div>
              
              <div className="space-y-1 text-xs pt-1 border-t border-zinc-100">
                <div className="flex justify-between text-zinc-600 text-[11px]">
                  <span>Books</span>
                  <span className="font-bold text-zinc-900">500+</span>
                </div>
                <div className="flex justify-between text-zinc-600 text-[11px]">
                  <span>Papers</span>
                  <span className="font-bold text-zinc-900">247</span>
                </div>
                <div className="flex justify-between text-zinc-600 text-[11px]">
                  <span>Videos</span>
                  <span className="font-bold text-zinc-900">100+</span>
                </div>
              </div>

              <Link to="/books" className="block text-center w-full py-1.5 bg-teal-50 hover:bg-teal-100 text-teal-700 font-semibold text-[11px] rounded-lg transition">
                View All Resources
              </Link>
            </div>
            
            {/* Bottom icon badge */}
            <div className="absolute -bottom-3 -left-3 w-9 h-9 bg-teal-600 rounded-xl flex items-center justify-center text-white shadow-lg">
              <BookOpen className="h-4 w-4" />
            </div>
          </div>
        </div>

      </div>
    </div>
  );
}
