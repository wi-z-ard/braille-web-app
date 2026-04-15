<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/layout.php';
ob_start();
?>
<section class="min-h-[70vh] flex items-center justify-center px-6 text-center">
  <div>
    <div class="text-7xl font-bold text-surface-border mb-4">404</div>
    <h1 class="text-2xl font-bold text-white mb-2">Page not found</h1>
    <p class="text-gray-400 mb-8">The page you're looking for doesn't exist.</p>
    <a href="<?php echo url('/'); ?>" class="px-6 py-3 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-medium transition-colors">Go Home</a>
  </div>
</section>
<?php
$content = ob_get_clean();
renderLayout('Not Found', $content);
