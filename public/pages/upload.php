<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/layout.php';
Security::requireAuth();

ob_start();
$csrf = Security::csrfToken();
?>
<section class="max-w-2xl mx-auto px-6 py-16">
  <div class="mb-10">
    <h1 class="text-3xl font-bold text-white mb-2">Upload Document</h1>
    <p class="text-gray-400">PDF, DOCX, or TXT — up to 50MB. Scanned files are handled via OCR.</p>
  </div>

  <!-- Upload form -->
  <form id="upload-form" class="space-y-6">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

    <!-- Drop zone -->
    <div id="drop-zone"
         class="relative rounded-2xl border-2 border-dashed border-surface-border hover:border-brand-500/50 bg-surface-card transition-all duration-300 cursor-pointer group"
         onclick="document.getElementById('file-input').click()">
      <input type="file" id="file-input" name="file" accept=".pdf,.docx,.txt" class="sr-only" required>

      <div id="drop-content" class="flex flex-col items-center justify-center py-16 px-8 text-center">
        <div class="w-16 h-16 rounded-2xl bg-brand-500/10 border border-brand-500/20 flex items-center justify-center mb-4 group-hover:bg-brand-500/20 transition-colors">
          <i class="lni lni-cloud-upload text-3xl text-brand-500"></i>
        </div>
        <p class="text-white font-medium mb-1">Drop your file here</p>
        <p class="text-sm text-gray-500">or click to browse</p>
        <p class="text-xs text-gray-600 mt-3">PDF · DOCX · TXT · Max 50MB</p>
      </div>

      <!-- File selected state -->
      <div id="file-selected" class="hidden flex items-center gap-4 p-6">
        <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center flex-shrink-0">
          <i class="lni lni-files text-2xl text-brand-500"></i>
        </div>
        <div class="flex-1 min-w-0">
          <p id="file-name" class="text-white font-medium truncate"></p>
          <p id="file-size" class="text-sm text-gray-500"></p>
        </div>
        <button type="button" onclick="clearFile(event)" class="text-gray-500 hover:text-white transition-colors p-1">
          <i class="lni lni-close text-xl"></i>
        </button>
      </div>
    </div>

    <!-- Language selector -->
    <div>
      <label class="block text-sm font-medium text-gray-300 mb-2">Language</label>
      <div class="grid grid-cols-3 gap-3">
        <?php foreach ([['auto','Auto-detect','lni lni-search'],['en','English','lni lni-flag'],['ar','Arabic','lni lni-flag-alt']] as [$val,$label,$icon]): ?>
        <label class="lang-option cursor-pointer">
          <input type="radio" name="language" value="<?= $val ?>" <?= $val === 'auto' ? 'checked' : '' ?> class="sr-only">
          <div class="lang-card rounded-xl border border-surface-border bg-surface-card p-3 text-center hover:border-brand-500/40 transition-all duration-200 <?= $val === 'auto' ? 'selected' : '' ?>">
            <i class="<?= $icon ?> text-2xl text-brand-500 mb-1"></i>
            <div class="text-sm font-medium text-gray-300"><?= $label ?></div>
          </div>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Error message -->
    <div id="error-msg" class="hidden rounded-xl bg-red-500/10 border border-red-500/20 p-4 text-sm text-red-400"></div>

    <!-- Submit -->
    <button type="submit" id="submit-btn"
            class="w-full py-4 rounded-xl bg-brand-500 hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-base transition-all duration-200 shadow-lg shadow-brand-500/20 hover:shadow-brand-500/30 hover:-translate-y-0.5">
      Convert to Braille
    </button>
  </form>
</section>

<script>
const dropZone   = document.getElementById('drop-zone');
const fileInput  = document.getElementById('file-input');
const dropContent= document.getElementById('drop-content');
const fileSelected=document.getElementById('file-selected');
const fileName   = document.getElementById('file-name');
const fileSize   = document.getElementById('file-size');
const errorMsg   = document.getElementById('error-msg');
const submitBtn  = document.getElementById('submit-btn');

// Drag & drop
['dragenter','dragover'].forEach(e => dropZone.addEventListener(e, ev => {
  ev.preventDefault(); dropZone.classList.add('border-brand-500','bg-brand-500/5');
}));
['dragleave','drop'].forEach(e => dropZone.addEventListener(e, ev => {
  ev.preventDefault(); dropZone.classList.remove('border-brand-500','bg-brand-500/5');
}));
dropZone.addEventListener('drop', ev => {
  const file = ev.dataTransfer.files[0];
  if (file) setFile(file);
});
fileInput.addEventListener('change', () => fileInput.files[0] && setFile(fileInput.files[0]));

function setFile(file) {
  const allowed = ['application/pdf','application/vnd.openxmlformats-officedocument.wordprocessingml.document','text/plain'];
  if (!allowed.includes(file.type) && !file.name.match(/\.(pdf|docx|txt)$/i)) {
    showError('Only PDF, DOCX, and TXT files are allowed.'); return;
  }
  if (file.size > 50 * 1024 * 1024) { showError('File exceeds 50MB limit.'); return; }
  hideError();
  fileName.textContent = file.name;
  fileSize.textContent = formatBytes(file.size);
  dropContent.classList.add('hidden');
  fileSelected.classList.remove('hidden');
  fileSelected.classList.add('flex');
  // Transfer to input
  const dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files;
}

function clearFile(e) {
  e.stopPropagation();
  fileInput.value = '';
  dropContent.classList.remove('hidden');
  fileSelected.classList.add('hidden');
  fileSelected.classList.remove('flex');
}

function formatBytes(b) {
  if (b < 1024) return b + ' B';
  if (b < 1048576) return (b/1024).toFixed(1) + ' KB';
  return (b/1048576).toFixed(1) + ' MB';
}

function showError(msg) { errorMsg.textContent = msg; errorMsg.classList.remove('hidden'); }
function hideError() { errorMsg.classList.add('hidden'); }

// Language card selection
document.querySelectorAll('.lang-option input').forEach(radio => {
  radio.addEventListener('change', () => {
    document.querySelectorAll('.lang-card').forEach(c => c.classList.remove('selected','border-brand-500','bg-brand-500/10'));
    radio.closest('.lang-option').querySelector('.lang-card').classList.add('selected','border-brand-500','bg-brand-500/10');
  });
});
// Init selected state
document.querySelector('.lang-card.selected')?.classList.add('border-brand-500','bg-brand-500/10');

// Form submit
document.getElementById('upload-form').addEventListener('submit', async e => {
  e.preventDefault();
  if (!fileInput.files[0]) { showError('Please select a file.'); return; }

  submitBtn.disabled = true;
  submitBtn.textContent = 'Uploading…';

  const fd = new FormData(e.target);
  try {
    const res  = await fetch('/api/upload', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      window.location.href = data.redirect;
    } else {
      showError(data.error || 'Upload failed.');
      submitBtn.disabled = false;
      submitBtn.textContent = 'Convert to Braille';
    }
  } catch {
    showError('Network error. Please try again.');
    submitBtn.disabled = false;
    submitBtn.textContent = 'Convert to Braille';
  }
});
</script>
<?php
$content = ob_get_clean();
renderLayout('Upload Document', $content);
