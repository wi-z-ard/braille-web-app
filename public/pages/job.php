<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/layout.php';
Security::requireAuth();

$jobId = Security::sanitizeString(substr($uri, 5)); // strip /job/
$user  = Auth::current();
$job   = JobQueue::get($jobId, $user['id']);

if (!$job) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$downloadBaseUrl = url('/api/download');
$uploadUrl = url('/upload');

ob_start();
?>
<section class="max-w-2xl mx-auto px-6 py-16" id="job-page" data-job-id="<?= htmlspecialchars($jobId) ?>">

  <!-- Processing state -->
  <div id="state-processing" class="<?= $job['status'] === 'done' || $job['status'] === 'error' ? 'hidden' : '' ?> text-center py-12">
    <div class="relative w-24 h-24 mx-auto mb-8">
      <div class="absolute inset-0 rounded-full border-2 border-brand-500/20"></div>
      <div class="absolute inset-0 rounded-full border-2 border-transparent border-t-brand-500 animate-spin"></div>
      <div class="absolute inset-3 rounded-full bg-brand-500/10 flex items-center justify-center text-2xl">⠿</div>
    </div>
    <h2 class="text-2xl font-bold text-white mb-2">Converting to Braille</h2>
    <p class="text-gray-400 mb-6">This usually takes under a minute.</p>

    <!-- Progress steps -->
    <div class="text-start max-w-xs mx-auto space-y-3" id="progress-steps">
      <?php foreach ([
        ['extract', 'Extracting text'],
        ['clean',   'Cleaning & detecting language'],
        ['convert', 'Converting to Braille'],
        ['save',    'Saving output files'],
      ] as $i => [$key, $label]): ?>
      <div class="flex items-center gap-3 step-item" data-step="<?= $i ?>">
        <div class="w-5 h-5 rounded-full border border-surface-border flex items-center justify-center flex-shrink-0 step-icon">
          <div class="w-2 h-2 rounded-full bg-gray-600"></div>
        </div>
        <span class="text-sm text-gray-500 step-label"><?= $label ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Done state -->
  <div id="state-done" class="<?= $job['status'] !== 'done' ? 'hidden' : '' ?>">
    <div class="text-center mb-10">
      <div class="w-20 h-20 rounded-2xl bg-green-500/10 border border-green-500/20 flex items-center justify-center text-3xl mx-auto mb-6">✓</div>
      <h2 class="text-2xl font-bold text-white mb-2">Conversion Complete</h2>
      <p class="text-gray-400">Your Braille document is ready.</p>
    </div>

    <!-- File info -->
    <div class="rounded-2xl border border-surface-border bg-surface-card p-6 mb-6">
      <div class="flex items-start justify-between gap-4">
        <div>
          <p class="font-medium text-white"><?= htmlspecialchars($job['orig_name']) ?></p>
          <p class="text-sm text-gray-500 mt-1">
            Language: <span class="text-gray-300"><?= strtoupper(htmlspecialchars($job['language'])) ?></span>
            · Processed: <span class="text-gray-300"><?= htmlspecialchars($job['updated_at']) ?></span>
          </p>
        </div>
        <span class="px-2.5 py-1 rounded-full bg-green-500/10 text-green-400 text-xs font-medium border border-green-500/20">Done</span>
      </div>
    </div>

    <!-- Unicode preview -->
    <div class="rounded-2xl border border-surface-border bg-surface-card p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-medium text-white">Braille Preview</h3>
        <span class="text-xs text-gray-500">Unicode Braille</span>
      </div>
      <div id="braille-preview" class="font-mono text-xl text-brand-500/80 leading-loose tracking-widest min-h-[80px] max-h-48 overflow-y-auto text-wrap break-all">
        Loading preview…
      </div>
    </div>

    <!-- Download buttons -->
    <div class="grid grid-cols-2 gap-4">
      <a href="<?php echo $downloadBaseUrl . '?id=' . urlencode($jobId) . '&format=brf'; ?>"
         class="flex items-center justify-center gap-2 py-4 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-semibold transition-all duration-200 shadow-lg shadow-brand-500/20 hover:-translate-y-0.5">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Download BRF
      </a>
      <a href="<?php echo $downloadBaseUrl . '?id=' . urlencode($jobId) . '&format=unicode'; ?>"
         class="flex items-center justify-center gap-2 py-4 rounded-xl border border-surface-border hover:border-gray-500 text-gray-300 hover:text-white font-medium transition-all duration-200">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Unicode TXT
      </a>
    </div>

    <div class="mt-6 text-center">
      <a href="<?php echo $uploadUrl; ?>" class="text-sm text-gray-500 hover:text-white transition-colors">← Convert another document</a>
    </div>
  </div>

  <!-- Error state -->
  <div id="state-error" class="<?= $job['status'] !== 'error' ? 'hidden' : '' ?> text-center py-12">
    <div class="w-20 h-20 rounded-2xl bg-red-500/10 border border-red-500/20 flex items-center justify-center text-3xl mx-auto mb-6">✕</div>
    <h2 class="text-2xl font-bold text-white mb-2">Conversion Failed</h2>
    <p class="text-gray-400 mb-2">Something went wrong during processing.</p>
    <?php if ($job['error']): ?>
    <p class="text-sm text-red-400 bg-red-500/10 rounded-lg px-4 py-2 inline-block mb-6"><?= htmlspecialchars($job['error']) ?></p>
    <?php endif; ?>
    <div><a href="<?php echo $uploadUrl; ?>" class="px-6 py-3 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-medium transition-colors">Try Again</a></div>
  </div>

</section>

<script>
const jobId = document.getElementById('job-page').dataset.jobId;
let pollInterval;
let stepIndex = 0;
const steps = document.querySelectorAll('.step-item');

function activateStep(i) {
  steps.forEach((s, idx) => {
    const icon  = s.querySelector('.step-icon');
    const label = s.querySelector('.step-label');
    if (idx < i) {
      icon.innerHTML = '<svg class="w-3 h-3 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
      icon.className = 'w-5 h-5 rounded-full border border-green-500/30 bg-green-500/10 flex items-center justify-center flex-shrink-0 step-icon';
      label.className = 'text-sm text-gray-300 step-label';
    } else if (idx === i) {
      icon.innerHTML = '<div class="w-2 h-2 rounded-full bg-brand-500 animate-pulse"></div>';
      icon.className = 'w-5 h-5 rounded-full border border-brand-500/50 bg-brand-500/10 flex items-center justify-center flex-shrink-0 step-icon';
      label.className = 'text-sm text-white font-medium step-label';
    }
  });
}

async function pollStatus() {
  try {
    const res  = await fetch(`<?= url('/api/job-status') ?>?id=${encodeURIComponent(jobId)}`);
    const data = await res.json();

    if (data.status === 'processing' || data.status === 'pending') {
      stepIndex = Math.min(stepIndex + 1, steps.length - 1);
      activateStep(stepIndex);
    }

    if (data.status === 'done') {
      clearInterval(pollInterval);
      activateStep(steps.length);
      document.getElementById('state-processing').classList.add('hidden');
      document.getElementById('state-done').classList.remove('hidden');
      loadPreview();
    }

    if (data.status === 'error') {
      clearInterval(pollInterval);
      document.getElementById('state-processing').classList.add('hidden');
      document.getElementById('state-error').classList.remove('hidden');
    }
  } catch { /* network hiccup, keep polling */ }
}

async function loadPreview() {
  try {
    const res  = await fetch(`<?= url('/api/download') ?>?id=${encodeURIComponent(jobId)}&format=unicode`);
    const text = await res.text();
    const preview = document.getElementById('braille-preview');
    preview.textContent = text.slice(0, 500) + (text.length > 500 ? '…' : '');
  } catch {
    document.getElementById('braille-preview').textContent = 'Preview unavailable.';
  }
}

// Start polling if not already done
const initialStatus = '<?= $job['status'] ?>';
if (initialStatus === 'pending' || initialStatus === 'processing') {
  activateStep(0);
  pollInterval = setInterval(pollStatus, 2500);
} else if (initialStatus === 'done') {
  loadPreview();
}
</script>
<?php
$content = ob_get_clean();
renderLayout('Job Status', $content);
