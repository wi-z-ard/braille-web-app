<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/layout.php';
Security::requireAuth();

$maxChars = Settings::getInt('text_conversion_limit', 5000);
$convertUrl = url('/api/convert-text');

ob_start();
?>
<section class="max-w-6xl mx-auto px-6 py-16">
  <div class="text-center mb-12">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-brand-500/10 border border-brand-500/20 mb-6">
      <span class="text-3xl">⠿</span>
    </div>
    <h1 class="text-4xl font-bold text-white mb-3">Live Text Converter</h1>
    <p class="text-gray-400">Instant bidirectional text ↔ Braille conversion</p>
  </div>

  <!-- Controls -->
  <div class="rounded-2xl border border-surface-border bg-surface-card p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-2">Direction</label>
        <select id="direction" class="w-full px-4 py-3 rounded-xl bg-[#0f1117] border border-surface-border text-white focus:border-brand-500 focus:outline-none transition-colors">
          <option value="to_braille">Text → Braille</option>
          <option value="from_braille">Braille → Text</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-2">Language</label>
        <select id="language" class="w-full px-4 py-3 rounded-xl bg-[#0f1117] border border-surface-border text-white focus:border-brand-500 focus:outline-none transition-colors">
          <option value="auto">Auto-detect</option>
          <option value="en">English (UEB Grade 2)</option>
          <option value="ar">Arabic (Grade 1)</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-2">Output Format</label>
        <select id="format" class="w-full px-4 py-3 rounded-xl bg-[#0f1117] border border-surface-border text-white focus:border-brand-500 focus:outline-none transition-colors">
          <option value="unicode">Unicode Braille</option>
          <option value="brf">BRF (ASCII)</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Converter -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Input -->
    <div class="rounded-2xl border border-surface-border bg-surface-card p-6">
      <div class="flex items-center justify-between mb-4">
        <label class="text-sm font-medium text-white">Input</label>
        <span class="text-xs text-gray-500"><span id="char-count">0</span> / <?= $maxChars ?></span>
      </div>
      <textarea id="input-text" 
                class="w-full h-80 px-4 py-3 rounded-xl bg-[#0f1117] border border-surface-border text-white placeholder-gray-600 focus:border-brand-500 focus:outline-none resize-none transition-colors"
                placeholder="Type or paste your text here..."
                maxlength="<?= $maxChars ?>"></textarea>
    </div>

    <!-- Output -->
    <div class="rounded-2xl border border-surface-border bg-surface-card p-6">
      <div class="flex items-center justify-between mb-4">
        <label class="text-sm font-medium text-white">Output</label>
        <div class="flex gap-2">
          <button id="print-btn" class="px-3 py-1.5 rounded-lg bg-[#0f1117] hover:bg-surface-border text-xs text-gray-300 hover:text-white transition-all duration-200 border border-surface-border">
            <span class="flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
              </svg>
              <span>Print</span>
            </span>
          </button>
          <button id="copy-btn" class="px-3 py-1.5 rounded-lg bg-[#0f1117] hover:bg-surface-border text-xs text-gray-300 hover:text-white transition-all duration-200 border border-surface-border">
          <span class="flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            <span id="copy-text">Copy</span>
          </span>
        </button>
        </div>
      </div>
      <div class="relative">
        <textarea id="output-text" 
                  class="w-full h-80 px-4 py-3 rounded-xl bg-[#0f1117] border border-surface-border text-brand-500/90 resize-none font-mono text-xl leading-loose tracking-widest"
                  placeholder="Converted output will appear here..."
                  readonly></textarea>
        <div id="loading" class="hidden absolute inset-0 flex items-center justify-center bg-surface-card/90 rounded-xl backdrop-blur-sm">
          <div class="w-8 h-8 border-2 border-brand-500/20 border-t-brand-500 rounded-full animate-spin"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Error Message -->
  <div id="error-msg" class="hidden mt-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm"></div>
</section>

<script>
const input = document.getElementById('input-text');
const output = document.getElementById('output-text');
const direction = document.getElementById('direction');
const language = document.getElementById('language');
const format = document.getElementById('format');
const charCount = document.getElementById('char-count');
const errorMsg = document.getElementById('error-msg');
const copyBtn = document.getElementById('copy-btn');

let debounceTimer;

input.addEventListener('input', () => {
  charCount.textContent = input.value.length;
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(convert, 500);
});

[direction, language, format].forEach(el => {
  el.addEventListener('change', convert);
});

copyBtn.addEventListener('click', () => {
  output.select();
  navigator.clipboard.writeText(output.value);
  const copyText = document.getElementById('copy-text');
  copyText.textContent = 'Copied!';
  setTimeout(() => copyText.textContent = 'Copy', 2000);
});

document.getElementById('print-btn').addEventListener('click', () => {
  if (!output.value) return;
  
  const printWindow = window.open('', '', 'width=800,height=600');
  printWindow.document.write(`
    <html>
      <head>
        <title>Braille Output</title>
        <style>
          body { 
            font-family: monospace; 
            font-size: 20px; 
            line-height: 2; 
            letter-spacing: 0.2em; 
            padding: 20px;
            white-space: pre-wrap;
          }
          @media print {
            body { font-size: 18px; }
          }
        </style>
      </head>
      <body>${output.value}</body>
    </html>
  `);
  printWindow.document.close();
  printWindow.focus();
  setTimeout(() => printWindow.print(), 250);
});

async function convert() {
  const text = input.value.trim();
  if (!text) {
    output.value = '';
    return;
  }

  errorMsg.classList.add('hidden');
  document.getElementById('loading').classList.remove('hidden');

  try {
    const res = await fetch('<?= $convertUrl ?>', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        text,
        direction: direction.value,
        language: language.value,
        format: format.value
      })
    });

    const data = await res.json();
    
    if (!res.ok) {
      errorMsg.textContent = data.error || 'Conversion failed';
      errorMsg.classList.remove('hidden');
      output.value = '';
      return;
    }

    output.value = data.result;
  } catch (err) {
    errorMsg.textContent = 'Network error. Please try again.';
    errorMsg.classList.remove('hidden');
    output.value = '';
  } finally {
    document.getElementById('loading').classList.add('hidden');
  }
}
</script>
<?php
$content = ob_get_clean();
renderLayout('Text Converter', $content);
