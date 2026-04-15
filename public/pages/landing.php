<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/layout.php';

ob_start();
?>
<!-- Hero -->
<section class="relative overflow-hidden min-h-[92vh] flex items-center">
  <!-- Background glow -->
  <div class="absolute inset-0 pointer-events-none">
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-brand-500/10 rounded-full blur-[120px]"></div>
    <div class="absolute top-1/3 left-1/4 w-[300px] h-[300px] bg-purple-500/8 rounded-full blur-[80px]"></div>
  </div>

  <!-- Braille dot grid decoration -->
  <div class="absolute inset-0 pointer-events-none opacity-[0.03]" id="braille-grid"></div>

  <div class="relative max-w-6xl mx-auto px-6 py-24 text-center">
    <!-- Badge -->
    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-brand-500/30 bg-brand-500/10 text-brand-500 text-xs font-medium mb-8">
      <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span>
      Arabic & English Braille Conversion
    </div>

    <!-- Headline -->
    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold tracking-tight leading-[1.1] mb-6">
      <span class="text-white">Knowledge in</span><br>
      <span class="bg-gradient-to-r from-sky-500 to-sky-400 bg-clip-text text-transparent">Every Fingertip</span>
    </h1>

    <p class="text-lg sm:text-xl text-gray-400 max-w-2xl mx-auto mb-10 leading-relaxed">
      Upload any textbook — PDF, Word, or text — and receive a perfectly formatted Braille document in seconds. Built for blind students, educators, and accessibility advocates.
    </p>

    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="<?php echo url('/register'); ?>" class="group px-8 py-4 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-semibold text-base transition-all duration-200 hover:-translate-y-0.5">
        Start Converting Free
        <span class="inline-block ms-2 group-hover:translate-x-1 transition-transform">→</span>
      </a>
      <a href="#how-it-works" class="px-8 py-4 rounded-xl border border-surface-border hover:border-gray-500 text-gray-300 hover:text-white font-medium text-base transition-all duration-200">
        See How It Works
      </a>
    </div>

    <!-- Floating Braille preview -->
    <div class="mt-20 relative max-w-3xl mx-auto">
      <div class="absolute inset-0 bg-gradient-to-t from-surface via-transparent to-transparent z-10 pointer-events-none"></div>
      <div class="rounded-2xl border border-surface-border bg-surface-card p-6 shadow-2xl text-left animate-float">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-3 h-3 rounded-full bg-red-500/70"></div>
          <div class="w-3 h-3 rounded-full bg-yellow-500/70"></div>
          <div class="w-3 h-3 rounded-full bg-green-500/70"></div>
          <span class="ms-2 text-xs text-gray-500 font-mono">output.brf</span>
        </div>
        <div class="font-mono text-2xl text-brand-500/80 leading-loose tracking-widest select-none">
          ⠓⠑⠇⠇⠕ ⠺⠕⠗⠇⠙<br>
          ⠞⠓⠊⠎ ⠊⠎ ⠃⠗⠁⠊⠇⠇⠑<br>
          ⠁⠉⠉⠑⠎⠎⠊⠃⠊⠇⠊⠞⠽
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Stats -->
<section class="border-y border-surface-border/50 bg-surface-card/30">
  <div class="max-w-6xl mx-auto px-6 py-12 grid grid-cols-2 sm:grid-cols-4 gap-8 text-center">
    <?php foreach ([
      ['50MB', 'Max file size'],
      ['2', 'Languages supported'],
      ['BRF + Unicode', 'Output formats'],
      ['< 60s', 'Average processing'],
    ] as [$val, $label]): ?>
    <div>
      <div class="text-3xl font-bold text-white mb-1"><?= $val ?></div>
      <div class="text-sm text-gray-500"><?= $label ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- How it works -->
<section id="how-it-works" class="max-w-6xl mx-auto px-6 py-24">
  <div class="text-center mb-16">
    <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">How It Works</h2>
    <p class="text-gray-400 max-w-xl mx-auto">Three steps from document to Braille-ready output.</p>
  </div>

  <div class="grid sm:grid-cols-3 gap-6">
    <?php
    $steps = [
      ['01', 'Upload', 'Drag & drop your PDF, Word, or text file. We handle scanned documents with OCR automatically.', '⬆'],
      ['02', 'Process', 'Our engine extracts text, detects language, cleans OCR artifacts, and converts via Liblouis.', '⚙'],
      ['03', 'Download', 'Preview Unicode Braille in your browser, then download the BRF file for any embosser.', '⬇'],
    ];
    foreach ($steps as [$num, $title, $desc, $icon]):
    ?>
    <div class="group relative rounded-2xl border border-surface-border bg-surface-card p-8 hover:border-brand-500/40 transition-all duration-300">
      <div class="absolute top-6 end-6 text-3xl opacity-20 group-hover:opacity-40 transition-opacity"><?= $icon ?></div>
      <div class="text-xs font-mono text-brand-500 mb-3"><?= $num ?></div>
      <h3 class="text-lg font-semibold text-white mb-2"><?= $title ?></h3>
      <p class="text-sm text-gray-400 leading-relaxed"><?= $desc ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- CTA -->
<section class="max-w-6xl mx-auto px-6 pb-24">
  <div class="rounded-3xl bg-gradient-to-br from-brand-500/20 to-purple-500/10 border border-brand-500/20 p-12 text-center">
    <h2 class="text-3xl font-bold text-white mb-4">Ready to make knowledge accessible?</h2>
    <p class="text-gray-400 mb-8">Join educators and accessibility advocates using Braille Bridge.</p>
    <a href="<?php echo url('/register'); ?>" class="inline-block px-8 py-4 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-semibold transition-all duration-200 hover:-translate-y-0.5">
      Create Free Account
    </a>
  </div>
</section>

<script>
// Generate subtle Braille dot grid
const grid = document.getElementById('braille-grid');
if (grid) {
  const dots = Array.from({length: 400}, () =>
    `<circle cx="${Math.random()*100}%" cy="${Math.random()*100}%" r="1.5" fill="white"/>`
  ).join('');
  grid.innerHTML = `<svg width="100%" height="100%">${dots}</svg>`;
}
</script>
<?php
$content = ob_get_clean();
renderLayout('Convert Textbooks to Braille', $content);
