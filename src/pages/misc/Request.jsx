import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';
import * as yup from 'yup';
import { useAuth } from '../../contexts/AuthContext';
import { useUserRequests, useRequestMutations } from '../../hooks/useRequests';
import { SYSTEM_CATEGORIES } from '../../lib/utils';
import { CheckCircle2, Sparkles, AlertCircle, ArrowRight, BookOpen, FileText, Video, Send } from 'lucide-react';

const schema = yup.object({
  material_type: yup.string().required('Material type is required'),
  community_category: yup.string().nullable(),
  title: yup.string().required('Title is required'),
  author_publisher: yup.string().nullable(),
  details: yup.string().nullable(),
});

export default function Request() {
  const { user } = useAuth();
  const { createRequest } = useRequestMutations();
  const [selectedType, setSelectedType] = useState('book');
  const [isSubmitted, setIsSubmitted] = useState(false);
  const [submittedTitle, setSubmittedTitle] = useState('');
  const [toast, setToast] = useState('');
  const [errorMessage, setErrorMessage] = useState('');

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm({
    resolver: yupResolver(schema),
    defaultValues: {
      material_type: 'book',
      community_category: '',
      title: '',
      author_publisher: '',
      details: '',
    },
  });

  const onSubmit = async (values) => {
    try {
      setErrorMessage('');
      const targetTitle = values.title;
      await createRequest.mutateAsync({
        user_id: user?.id,
        material_type: selectedType || values.material_type,
        community_category: values.community_category || null,
        title: targetTitle,
        author_publisher: values.author_publisher || null,
        details: values.details || null,
        status: 'pending',
      });

      setSubmittedTitle(targetTitle);
      setIsSubmitted(true);
      setToast('Material request submitted successfully!');
      setTimeout(() => setToast(''), 4000);
      reset();
    } catch (err) {
      console.error(err);
      setErrorMessage(err.message || 'Failed to submit your request. Please try again.');
    }
  };

  return (
    <div className="antialiased bg-zinc-50 min-h-screen">
      {/* Hero Section with Animated Background */}
      <section className="relative overflow-hidden isolate">
        {/* Gradient Background */}
        <div className="absolute inset-0 bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600"></div>
        
        {/* Animated Mesh Pattern */}
        <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff06_1px,transparent_1px),linear-gradient(to_bottom,#ffffff06_1px,transparent_1px)] bg-[size:40px_40px]"></div>
        
        {/* Floating Orbs */}
        <div className="absolute top-20 left-20 w-72 h-72 bg-white/10 rounded-full blur-3xl animate-float"></div>
        <div className="absolute bottom-10 right-20 w-96 h-96 bg-fuchsia-400/20 rounded-full blur-3xl animate-float-delayed"></div>
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-purple-400/10 rounded-full blur-3xl animate-pulse-glow"></div>
        
        {/* Floating Icons */}
        <div className="absolute top-32 right-1/4 text-white/20 animate-float">
          <svg xmlns="http://www.w3.org/2000/svg" className="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1">
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
        <div className="absolute bottom-32 left-1/4 text-white/15 animate-float-delayed">
          <svg xmlns="http://www.w3.org/2000/svg" className="h-20 w-20" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1">
            <path strokeLinecap="round" strokeLinejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        
        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center text-white">
          {/* Breadcrumb Badge */}
          <div className="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md rounded-full px-5 py-2 mb-8 border border-white/20">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
              <path strokeLinecap="round" strokeLinejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span className="text-sm font-medium">Can't find what you need?</span>
          </div>
          
          {/* Main Title */}
          <h1 className="text-5xl md:text-6xl font-bold mb-6 tracking-tight font-heading">
            <span className="text-white/90">Request Study Material</span>
          </h1>
          <p className="text-xl text-white/70 max-w-2xl mx-auto leading-relaxed">
            Let us know what you're looking for and we'll do our best to add it to our library.
          </p>
          
          {/* Steps Indicator */}
          <div className="flex items-center justify-center gap-4 mt-12">
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 rounded-full bg-white text-purple-600 font-bold text-sm flex items-center justify-center">1</div>
              <span className="text-sm text-white/80 hidden sm:inline">Choose Type</span>
            </div>
            <div className="w-8 h-px bg-white/30"></div>
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 rounded-full bg-white/20 text-white font-bold text-sm flex items-center justify-center border border-white/30">2</div>
              <span className="text-sm text-white/60 hidden sm:inline">Add Details</span>
            </div>
            <div className="w-8 h-px bg-white/30"></div>
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 rounded-full bg-white/20 text-white font-bold text-sm flex items-center justify-center border border-white/30">3</div>
              <span className="text-sm text-white/60 hidden sm:inline">Submit Request</span>
            </div>
          </div>
        </div>
        
        {/* Bottom Wave */}
        <div className="absolute bottom-0 left-0 right-0">
          <svg viewBox="0 0 1440 100" fill="none" xmlns="http://www.w3.org/2000/svg" className="w-full">
            <path d="M0 50L48 45.7C96 41.3 192 32.7 288 35.8C384 39 480 54 576 57.2C672 60.3 768 51.7 864 48.5C960 45.3 1056 47.7 1152 51.8C1248 56 1344 62 1392 65L1440 68V100H1392C1344 100 1248 100 1152 100C1056 100 960 100 864 100C768 100 672 100 576 100C480 100 384 100 288 100C192 100 96 100 48 100H0V50Z" fill="#FAFAFA"/>
          </svg>
        </div>
      </section>

      {/* Floating Toast Alert */}
      {toast && (
        <div className="fixed top-20 right-6 z-50 p-4 bg-emerald-600 text-white rounded-2xl shadow-xl flex items-center gap-3 animate-in fade-in slide-in-from-top-4">
          <CheckCircle2 className="h-5 w-5 shrink-0" />
          <span className="text-xs font-semibold">{toast}</span>
          <button onClick={() => setToast('')} className="ml-2 text-white/80 hover:text-white font-bold">
            ✕
          </button>
        </div>
      )}

      {/* Form / Success Section */}
      <section className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 pb-24 relative z-10">
        {/* Error Alert */}
        {errorMessage && (
          <div className="rounded-2xl p-5 mb-8 bg-rose-50 border-2 border-rose-200 flex items-center gap-4 animate-in fade-in">
            <div className="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center text-rose-600 shrink-0">
              <AlertCircle className="h-5 w-5" />
            </div>
            <div>
              <p className="font-semibold text-rose-900 text-xs">Submission Failed</p>
              <p className="text-xs text-rose-700">{errorMessage}</p>
            </div>
          </div>
        )}

        {/* Success Confirmation Card */}
        {isSubmitted ? (
          <div className="bg-white rounded-3xl p-8 sm:p-12 text-center space-y-6 shadow-2xl shadow-purple-500/10 border border-emerald-200 animate-in fade-in zoom-in-95">
            <div className="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto shadow-lg shadow-emerald-500/20">
              <CheckCircle2 className="h-8 w-8" />
            </div>
            <div className="space-y-2">
              <span className="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold">
                <Sparkles className="h-3.5 w-3.5 text-emerald-600" />
                Submitted to Admin Team
              </span>
              <h2 className="text-2xl sm:text-3xl font-bold text-zinc-900 font-heading">
                Request Received!
              </h2>
              <p className="text-sm text-zinc-600 max-w-lg mx-auto leading-relaxed">
                Thank you for your suggestion! We've registered your request for{' '}
                <strong className="text-zinc-900 font-semibold">"{submittedTitle}"</strong>.
                Our team reviews all submissions within 24–48 hours.
              </p>
            </div>

            <div className="flex flex-col sm:flex-row items-center justify-center gap-3 pt-4 max-w-md mx-auto">
              <button
                onClick={() => {
                  setIsSubmitted(false);
                  setSubmittedTitle('');
                  reset();
                }}
                className="w-full sm:w-auto px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-purple-600/20 transition cursor-pointer flex items-center justify-center gap-2"
              >
                <Send className="h-3.5 w-3.5" />
                Request Another Material
              </button>
              <Link
                to="/history"
                className="w-full sm:w-auto px-6 py-3 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 rounded-xl text-xs font-semibold transition text-center"
              >
                View in History
              </Link>
            </div>
          </div>
        ) : (
          /* Main Form Card */
          <form onSubmit={handleSubmit(onSubmit)}>
            <div className="glass-card rounded-3xl shadow-2xl shadow-purple-500/10 overflow-hidden bg-white border border-zinc-200/80">
            
            {/* Material Type Section */}
            <div className="p-8 md:p-10 border-b border-zinc-100">
              <div className="flex items-center gap-3 mb-6">
                <div className="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600">
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                  </svg>
                </div>
                <div>
                  <h2 className="text-xl font-bold text-zinc-900 font-heading">What are you looking for?</h2>
                  <p className="text-sm text-zinc-500">Select the type of material you need</p>
                </div>
              </div>
              
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                {/* Book */}
                <label className="type-card cursor-pointer group" onClick={() => setSelectedType('book')}>
                  <input type="radio" value="book" checked={selectedType === 'book'} onChange={() => setSelectedType('book')} className="peer sr-only" />
                  <div className={`p-6 rounded-2xl border-2 transition-all ${selectedType === 'book' ? 'border-indigo-500 bg-indigo-50 shadow-lg shadow-indigo-500/20' : 'border-zinc-200 bg-zinc-50/50 hover:border-indigo-300 hover:bg-indigo-50/50'}`}>
                    <div className="w-14 h-14 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-indigo-500/30 group-hover:scale-105 transition text-white">
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                      </svg>
                    </div>
                    <h3 className="font-semibold text-zinc-900 mb-1">Book</h3>
                    <p className="text-xs text-zinc-500">Textbooks & References</p>
                  </div>
                </label>
                
                {/* Paper */}
                <label className="type-card cursor-pointer group" onClick={() => setSelectedType('paper')}>
                  <input type="radio" value="paper" checked={selectedType === 'paper'} onChange={() => setSelectedType('paper')} className="peer sr-only" />
                  <div className={`p-6 rounded-2xl border-2 transition-all ${selectedType === 'paper' ? 'border-teal-500 bg-teal-50 shadow-lg shadow-teal-500/20' : 'border-zinc-200 bg-zinc-50/50 hover:border-teal-300 hover:bg-teal-50/50'}`}>
                    <div className="w-14 h-14 bg-gradient-to-br from-teal-500 to-teal-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-teal-500/30 group-hover:scale-105 transition text-white">
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                    <h3 className="font-semibold text-zinc-900 mb-1">Paper</h3>
                    <p className="text-xs text-zinc-500">Exam Papers & Notes</p>
                  </div>
                </label>
                
                {/* Video */}
                <label className="type-card cursor-pointer group" onClick={() => setSelectedType('video')}>
                  <input type="radio" value="video" checked={selectedType === 'video'} onChange={() => setSelectedType('video')} className="peer sr-only" />
                  <div className={`p-6 rounded-2xl border-2 transition-all ${selectedType === 'video' ? 'border-rose-500 bg-rose-50 shadow-lg shadow-rose-500/20' : 'border-zinc-200 bg-zinc-50/50 hover:border-rose-300 hover:bg-rose-50/50'}`}>
                    <div className="w-14 h-14 bg-gradient-to-br from-rose-500 to-rose-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-rose-500/30 group-hover:scale-105 transition text-white">
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <h3 className="font-semibold text-zinc-900 mb-1">Video</h3>
                    <p className="text-xs text-zinc-500">Tutorials & Lectures</p>
                  </div>
                </label>
              </div>
            </div>
            
            {/* Form Fields Section */}
            <div className="p-8 md:p-10 space-y-6">
              <div className="flex items-center gap-3 mb-6">
                <div className="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </div>
                <div>
                  <h2 className="text-xl font-bold text-zinc-900 font-heading">Tell us more</h2>
                  <p className="text-sm text-zinc-500">The more details, the better we can help</p>
                </div>
              </div>
              
              {/* Community Category Field */}
              <div className="space-y-2">
                <label htmlFor="community_category" className="block text-sm font-semibold text-zinc-700">
                  Community Category <span className="text-zinc-400 font-normal">(Optional)</span>
                </label>
                <select 
                  id="community_category" 
                  {...register('community_category')}
                  className="form-input w-full px-5 py-4 bg-zinc-50 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 focus:bg-white transition text-zinc-900 appearance-none cursor-pointer"
                >
                  <option value="">Select a community to target...</option>
                  {SYSTEM_CATEGORIES.map((cat) => (
                    <option key={cat} value={cat}>{cat}</option>
                  ))}
                </select>
              </div>

              {/* Title Field */}
              <div className="space-y-2">
                <label htmlFor="title" className="block text-sm font-semibold text-zinc-700">
                  Title / Topic <span className="text-red-500">*</span>
                </label>
                <input 
                  type="text" 
                  id="title" 
                  {...register('title')}
                  required 
                  placeholder="e.g. Introduction to Algorithms, 3rd Edition"
                  className="form-input w-full px-5 py-4 bg-zinc-50 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 focus:bg-white transition text-zinc-900 placeholder:text-zinc-400"
                />
              </div>
              
              {/* Author/Publisher Field */}
              <div className="space-y-2">
                <label htmlFor="author_publisher" className="block text-sm font-semibold text-zinc-700">
                  Author / Publisher <span className="text-zinc-400 font-normal">(Optional)</span>
                </label>
                <input 
                  type="text" 
                  id="author_publisher" 
                  {...register('author_publisher')}
                  placeholder="e.g. Thomas H. Cormen, MIT Press"
                  className="form-input w-full px-5 py-4 bg-zinc-50 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 focus:bg-white transition text-zinc-900 placeholder:text-zinc-400"
                />
              </div>
              
              {/* Details Field */}
              <div className="space-y-2">
                <label htmlFor="details" className="block text-sm font-semibold text-zinc-700">
                  Additional Details <span className="text-zinc-400 font-normal">(Optional)</span>
                </label>
                <textarea 
                  id="details" 
                  {...register('details')}
                  rows="4" 
                  placeholder="Any specific edition, year, chapter, or context that would help us find the right material..."
                  className="form-input w-full px-5 py-4 bg-zinc-50 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 focus:bg-white transition text-zinc-900 placeholder:text-zinc-400 resize-none"
                ></textarea>
              </div>
              
              {/* Submit Button */}
              <div className="pt-4">
                <button 
                  type="submit"
                  className="w-full relative group bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-600 text-white px-8 py-5 rounded-2xl font-semibold text-lg hover:shadow-xl hover:shadow-purple-500/30 transition-all duration-300 flex justify-center items-center gap-3 overflow-hidden cursor-pointer"
                >
                  <span className="relative z-10 flex items-center gap-3 font-heading">
                    Submit Request
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                  </span>
                  <div className="absolute inset-0 bg-gradient-to-r from-violet-700 via-purple-700 to-fuchsia-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </button>
              </div>
            </div>
          </div>
        </form>
      )}
        
        {/* Info Cards */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10">
          <div className="bg-white rounded-2xl p-6 border border-zinc-200/50 shadow-sm hover:shadow-lg hover:border-purple-200 transition-all duration-300 group">
            <div className="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center text-white mb-4 shadow-lg shadow-amber-500/30 group-hover:scale-105 transition">
              <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                <path strokeLinecap="round" strokeLinejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 className="font-bold text-zinc-900 mb-1">Fast Response</h3>
            <p className="text-sm text-zinc-500 leading-relaxed">We review requests within 24-48 hours</p>
          </div>

          <div className="bg-white rounded-2xl p-6 border border-zinc-200/50 shadow-sm hover:shadow-lg hover:border-purple-200 transition-all duration-300 group">
            <div className="w-12 h-12 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-2xl flex items-center justify-center text-white mb-4 shadow-lg shadow-emerald-500/30 group-hover:scale-105 transition">
              <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                <path strokeLinecap="round" strokeLinejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 className="font-bold text-zinc-900 mb-1">Quality Verified</h3>
            <p className="text-sm text-zinc-500 leading-relaxed">All materials are verified before adding</p>
          </div>

          <div className="bg-white rounded-2xl p-6 border border-zinc-200/50 shadow-sm hover:shadow-lg hover:border-purple-200 transition-all duration-300 group">
            <div className="w-12 h-12 bg-gradient-to-br from-violet-400 to-purple-500 rounded-2xl flex items-center justify-center text-white mb-4 shadow-lg shadow-purple-500/30 group-hover:scale-105 transition">
              <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                <path strokeLinecap="round" strokeLinejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </div>
            <h3 className="font-bold text-zinc-900 mb-1">Get Notified</h3>
            <p className="text-sm text-zinc-500 leading-relaxed">We'll alert you when it's available</p>
          </div>
        </div>
      </section>
    </div>
  );
}
