<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/layout.php';

if (Auth::current()) { header('Location: ' . BASE_PATH . '/dashboard'); exit; }

ob_start();
$csrf = Security::csrfToken();
?>
<section class="min-h-[80vh] flex items-center justify-center px-6 py-16">
  <div class="w-full max-w-sm">
    <div class="text-center mb-8">
      <div class="w-12 h-12 rounded-xl bg-brand-500 flex items-center justify-center text-white font-bold text-lg mx-auto mb-4">⠃</div>
      <h1 class="text-2xl font-bold text-white">Welcome back</h1>
      <p class="text-gray-400 text-sm mt-1">Sign in to your account</p>
    </div>

    <form id="login-form" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">Email</label>
        <input type="email" name="email" required autocomplete="email"
               class="w-full px-4 py-3 rounded-xl bg-surface-card border border-surface-border text-white placeholder-gray-600 focus:outline-none focus:border-brand-500/60 focus:ring-1 focus:ring-brand-500/30 transition-all"
               placeholder="you@example.com">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
        <input type="password" name="password" required autocomplete="current-password"
               class="w-full px-4 py-3 rounded-xl bg-surface-card border border-surface-border text-white placeholder-gray-600 focus:outline-none focus:border-brand-500/60 focus:ring-1 focus:ring-brand-500/30 transition-all"
               placeholder="••••••••">
      </div>

      <div id="error-msg" class="hidden rounded-xl bg-red-500/10 border border-red-500/20 p-3 text-sm text-red-400"></div>

      <button type="submit" id="submit-btn"
              class="w-full py-3.5 rounded-xl bg-brand-500 hover:bg-brand-600 disabled:opacity-50 text-white font-semibold transition-all duration-200 shadow-lg shadow-brand-500/20 hover:-translate-y-0.5">
        Sign In
      </button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-6">
      Don't have an account? <a href="<?php echo url('/register'); ?>" class="text-brand-500 hover:text-brand-600 font-medium transition-colors">Sign up</a>
    </p>
  </div>
</section>

<script>
document.getElementById('login-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('submit-btn');
  const err = document.getElementById('error-msg');
  btn.disabled = true; btn.textContent = 'Signing in…';
  err.classList.add('hidden');

  const res  = await fetch('/api/auth/login', { method: 'POST', body: new FormData(e.target) });
  const data = await res.json();

  if (data.success) { window.location.href = data.redirect; }
  else {
    err.textContent = data.error; err.classList.remove('hidden');
    btn.disabled = false; btn.textContent = 'Sign In';
  }
});
</script>
<?php
$content = ob_get_clean();
renderLayout('Sign In', $content);
