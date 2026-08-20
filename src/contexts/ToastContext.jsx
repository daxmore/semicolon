import React, { createContext, useContext, useState, useCallback } from 'react';
import { CheckCircle2, AlertCircle, Info } from 'lucide-react';

const ToastContext = createContext(null);

export function ToastProvider({ children }) {
  const [toast, setToast] = useState(null);

  const showToast = useCallback((message, type = 'success', duration = 3000) => {
    setToast({ message, type, id: Date.now() });
    setTimeout(() => {
      setToast((current) => (current?.message === message ? null : current));
    }, duration);
  }, []);

  const hideToast = useCallback(() => {
    setToast(null);
  }, []);

  return (
    <ToastContext.Provider value={{ showToast, hideToast }}>
      {children}
      {toast && (
        <div
          role="status"
          aria-live="polite"
          className="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-4 py-3 bg-white/95 backdrop-blur border border-zinc-200/90 rounded-2xl shadow-xl text-xs font-semibold text-zinc-800 transition-all duration-300 animate-in fade-in slide-in-from-bottom-4 hover:shadow-2xl"
        >
          {toast.type === 'error' ? (
            <AlertCircle className="h-4 w-4 text-rose-600 shrink-0" />
          ) : toast.type === 'info' ? (
            <Info className="h-4 w-4 text-indigo-600 shrink-0" />
          ) : (
            <CheckCircle2 className="h-4 w-4 text-emerald-600 shrink-0" />
          )}
          <span>{toast.message}</span>
          <button
            onClick={hideToast}
            className="ml-2 text-zinc-400 hover:text-zinc-700 font-bold px-1 transition"
          >
            ✕
          </button>
        </div>
      )}
    </ToastContext.Provider>
  );
}

export function useToast() {
  const context = useContext(ToastContext);
  if (!context) {
    throw new Error('useToast must be used within a ToastProvider');
  }
  return context;
}
