// Global CSRF helper
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const BASE_PATH = window.BASE_PATH || '';

// Enhance all fetch calls with CSRF header and base path
const _fetch = window.fetch;
window.fetch = (url, opts = {}) => {
  if (typeof url === 'string' && url.startsWith('/api/')) {
    url = BASE_PATH + url;
    opts.headers = { ...(opts.headers || {}), 'X-CSRF-Token': CSRF };
  }
  return _fetch(url, opts);
};

// Drag-over visual feedback (global)
document.addEventListener('dragover', e => e.preventDefault());

// Auto-dismiss flash messages
document.querySelectorAll('[data-autohide]').forEach(el => {
  setTimeout(() => el.remove(), parseInt(el.dataset.autohide) || 4000);
});
