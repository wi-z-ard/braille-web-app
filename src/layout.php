<?php
declare(strict_types=1);

function url(string $path = ''): string {
    return BASE_PATH . $path;
}

function renderLayout(string $title, string $content, array $options = []): void {
    $user      = Auth::current();
    $lang      = $options['lang'] ?? 'en';
    $dir       = $lang === 'ar' ? 'rtl' : 'ltr';
    $bodyClass = $options['bodyClass'] ?? '';
    $csrf      = Security::csrfToken();
    
    $cssUrl = url('/css/app.css');
    $jsUrl = url('/js/app.js');
    $homeUrl = url('/');
    $lineiconsUrl = url('/assets/lineicons/lineicons.css');
    $tailwindUrl = url('/assets/tailwind.min.js');
    $basePath = BASE_PATH;
    
    echo <<<HTML
<!DOCTYPE html>
<html lang="{$lang}" dir="{$dir}" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{$title} — Braille Bridge</title>
  <meta name="description" content="Convert textbooks to Braille instantly. Supporting blind students worldwide.">
  <link rel="stylesheet" href="{$lineiconsUrl}">
  <script src="{$tailwindUrl}"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            brand: { 50:'#f0f4ff', 100:'#e0e9ff', 500:'#4f6ef7', 600:'#3b5bf5', 700:'#2a47e8', 900:'#1a2d9e' },
            surface: { DEFAULT:'#0f1117', card:'#161b27', border:'#1e2535' }
          },
          fontFamily: { sans: ['Inter','system-ui','sans-serif'] },
          backdropBlur: { xs: '2px' },
          animation: {
            'pulse-slow': 'pulse 3s cubic-bezier(0.4,0,0.6,1) infinite',
            'float': 'float 6s ease-in-out infinite',
            'shimmer': 'shimmer 2s linear infinite',
          },
          keyframes: {
            float: { '0%,100%': {transform:'translateY(0)'}, '50%': {transform:'translateY(-12px)'} },
            shimmer: { '0%': {backgroundPosition:'-200% 0'}, '100%': {backgroundPosition:'200% 0'} },
          }
        }
      }
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{$cssUrl}">
  <meta name="csrf-token" content="{$csrf}">
  <script>window.BASE_PATH = '{$basePath}';</script>
</head>
<body class="bg-surface text-white font-sans antialiased {$bodyClass}">

  <!-- Nav -->
  <nav class="fixed top-0 inset-x-0 z-50 border-b border-surface-border/50 backdrop-blur-md bg-surface/80">
    <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
      <a href="{$homeUrl}" class="flex items-center gap-2.5 group">
        <div class="w-8 h-8 rounded-lg bg-brand-500 flex items-center justify-center text-white font-bold text-sm group-hover:bg-brand-600 transition-colors relative overflow-hidden">
          <span class="relative z-10">⠃⠃</span>
          <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent"></div>
        </div>
        <span class="font-semibold text-white">Braille Bridge</span>
      </a>
      
      <!-- Desktop Menu -->
      <div class="hidden md:flex items-center gap-4">
HTML;
    if ($user) {
        $dashUrl = url('/dashboard');
        $convertUrl = url('/convert');
        $uploadUrl = url('/upload');
        $logoutUrl = url('/api/auth/logout');
        echo <<<HTML
        <span class="text-sm text-gray-400">{$user['name']}</span>
        <a href="{$dashUrl}" class="flex items-center gap-1.5 text-sm text-gray-300 hover:text-white transition-colors">
          <i class="lni lni-dashboard"></i>
          <span>Dashboard</span>
        </a>
        <a href="{$convertUrl}" class="flex items-center gap-1.5 text-sm text-gray-300 hover:text-white transition-colors">
          <i class="lni lni-text-format"></i>
          <span>Convert</span>
        </a>
        <a href="{$uploadUrl}" class="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-sm font-medium transition-colors">
          <i class="lni lni-cloud-upload"></i>
          <span>Upload</span>
        </a>
        <a href="{$logoutUrl}" class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-white transition-colors">
          <i class="lni lni-exit"></i>
          <span>Logout</span>
        </a>
HTML;
    } else {
        $loginUrl = url('/login');
        $registerUrl = url('/register');
        echo <<<HTML
        <a href="{$loginUrl}" class="flex items-center gap-1.5 text-sm text-gray-300 hover:text-white transition-colors">
          <i class="lni lni-enter"></i>
          <span>Sign in</span>
        </a>
        <a href="{$registerUrl}" class="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-sm font-medium transition-colors">
          <i class="lni lni-user"></i>
          <span>Get Started</span>
        </a>
HTML;
    }
    echo <<<HTML
      </div>
      
      <!-- Mobile Menu Button -->
      <button id="mobile-menu-btn" class="md:hidden text-gray-300 hover:text-white">
        <i class="lni lni-menu text-2xl"></i>
      </button>
    </div>
    
    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden border-t border-surface-border bg-surface">
      <div class="px-6 py-4 space-y-3">
HTML;
    if ($user) {
        echo <<<HTML
        <div class="text-sm text-gray-400 pb-2 border-b border-surface-border">{$user['name']}</div>
        <a href="{$dashUrl}" class="flex items-center gap-2 text-sm text-gray-300 hover:text-white py-2">
          <i class="lni lni-dashboard"></i>
          <span>Dashboard</span>
        </a>
        <a href="{$convertUrl}" class="flex items-center gap-2 text-sm text-gray-300 hover:text-white py-2">
          <i class="lni lni-text-format"></i>
          <span>Convert</span>
        </a>
        <a href="{$uploadUrl}" class="flex items-center gap-2 text-sm text-gray-300 hover:text-white py-2">
          <i class="lni lni-cloud-upload"></i>
          <span>Upload</span>
        </a>
        <a href="{$logoutUrl}" class="flex items-center gap-2 text-sm text-gray-400 hover:text-white py-2">
          <i class="lni lni-exit"></i>
          <span>Logout</span>
        </a>
HTML;
    } else {
        echo <<<HTML
        <a href="{$loginUrl}" class="flex items-center gap-2 text-sm text-gray-300 hover:text-white py-2">
          <i class="lni lni-enter"></i>
          <span>Sign in</span>
        </a>
        <a href="{$registerUrl}" class="flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-sm font-medium text-center">
          <i class="lni lni-user"></i>
          <span>Get Started</span>
        </a>
HTML;
    }
    echo <<<HTML
      </div>
    </div>
  </nav>
  
  <script>
    document.getElementById('mobile-menu-btn')?.addEventListener('click', () => {
      document.getElementById('mobile-menu').classList.toggle('hidden');
    });
  </script>

  <main class="pt-16 min-h-screen">
    {$content}
  </main>

  <footer class="border-t border-surface-border/50 py-8 text-center text-sm text-gray-500">
    <p>Braille Bridge — Making knowledge accessible for everyone.</p>
  </footer>

  <script src="{$jsUrl}"></script>
</body>
</html>
HTML;
}
