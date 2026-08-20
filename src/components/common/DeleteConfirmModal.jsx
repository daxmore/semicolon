import React, { useEffect, useRef } from 'react';
import { AlertTriangle, Trash2, X } from 'lucide-react';

/**
 * Modern confirmation modal popup utilizing HTML dialog/popover standard.
 *
 * @param {boolean} isOpen - Whether the modal is visible
 * @param {function} onClose - Callback when modal is closed/cancelled
 * @param {function} onConfirm - Callback when delete is confirmed
 * @param {string} title - Title of the modal (e.g. "Delete User")
 * @param {string} itemName - Name or identifier of the item being deleted
 * @param {string} message - Explanatory message
 * @param {boolean} isLoading - Loading state during async deletion
 */
export default function DeleteConfirmModal({
  isOpen,
  onClose,
  onConfirm,
  title = 'Confirm Deletion',
  itemName = '',
  message = 'Are you sure you want to permanently delete this item? This action cannot be undone.',
  isLoading = false,
}) {
  const dialogRef = useRef(null);

  useEffect(() => {
    const dialog = dialogRef.current;
    if (!dialog) return;

    if (isOpen) {
      if (typeof dialog.showModal === 'function') {
        if (!dialog.open) {
          dialog.showModal();
        }
      }
    } else {
      if (typeof dialog.close === 'function' && dialog.open) {
        dialog.close();
      }
    }
  }, [isOpen]);

  if (!isOpen) return null;

  return (
    <dialog
      ref={dialogRef}
      popover="auto"
      onCancel={(e) => {
        e.preventDefault();
        if (!isLoading) onClose();
      }}
      className="fixed inset-0 m-auto z-50 p-0 bg-transparent backdrop:bg-black/20 backdrop:backdrop-blur-xs open:flex items-center justify-center animate-in fade-in zoom-in-95 duration-200"
    >
      <div 
        className="fixed inset-0 bg-black/25 backdrop-blur-[2px] flex items-center justify-center p-4 z-50"
        onClick={(e) => {
          if (e.target === e.currentTarget && !isLoading) onClose();
        }}
      >
        <div className="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-zinc-200/80 space-y-5 text-zinc-900 relative">
          
          {/* Close button */}
          <button
            type="button"
            disabled={isLoading}
            onClick={onClose}
            className="absolute top-4 right-4 p-1.5 text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 rounded-xl transition cursor-pointer disabled:opacity-50"
          >
            <X className="h-4 w-4" />
          </button>

          {/* Header Icon + Titles */}
          <div className="flex items-start gap-4">
            <div className="w-11 h-11 rounded-2xl bg-red-50 border border-red-100 flex items-center justify-center text-red-600 shrink-0 shadow-xs">
              <AlertTriangle className="h-5 w-5" />
            </div>
            <div className="space-y-1 pr-6">
              <h3 className="font-bold text-base text-zinc-900">{title}</h3>
              <p className="text-xs text-zinc-500 leading-relaxed">{message}</p>
            </div>
          </div>

          {/* Target details (without ITEM label) */}
          {itemName && (
            <div className="px-3.5 py-2 bg-zinc-50 border border-zinc-200/60 rounded-xl text-xs font-semibold text-zinc-700 break-all">
              {itemName}
            </div>
          )}

          {/* Actions */}
          <div className="flex items-center justify-end gap-2.5 pt-2 border-t border-zinc-100">
            <button
              type="button"
              disabled={isLoading}
              onClick={onClose}
              className="px-4 py-2 text-xs font-semibold text-zinc-600 hover:text-zinc-800 bg-zinc-100 hover:bg-zinc-200/80 rounded-xl transition cursor-pointer disabled:opacity-50"
            >
              Cancel
            </button>
            <button
              type="button"
              disabled={isLoading}
              onClick={onConfirm}
              className="inline-flex items-center justify-center min-w-[90px] px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 active:bg-red-800 rounded-xl shadow-xs transition cursor-pointer disabled:opacity-50"
            >
              {isLoading ? (
                <div className="flex items-center gap-1.5">
                  <div className="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin" />
                  <span>Deleting...</span>
                </div>
              ) : (
                <span>Delete Permanently</span>
              )}
            </button>
          </div>
        </div>
      </div>
    </dialog>
  );
}
