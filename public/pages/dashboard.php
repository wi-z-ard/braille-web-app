<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/layout.php';
Security::requireAuth();

$user = Auth::current();
$jobs = JobQueue::listForUser($user['id']);

$uploadUrl = url('/upload');
$jobBaseUrl = url('/job/');
$downloadBaseUrl = url('/api/download');

ob_start();
?>
<section class="max-w-5xl mx-auto px-6 py-12">
  <div class="flex items-center justify-between mb-10">
    <div>
      <h1 class="text-2xl font-bold text-white">Dashboard</h1>
      <p class="text-gray-400 text-sm mt-1">Welcome back, <?= htmlspecialchars($user['name']) ?></p>
    </div>
    <a href="<?php echo $uploadUrl; ?>" class="px-5 py-2.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-medium text-sm transition-all duration-200 shadow-lg shadow-brand-500/20 hover:-translate-y-0.5">
      + New Conversion
    </a>
  </div>

  <?php if (empty($jobs)): ?>
  <div class="rounded-2xl border border-dashed border-surface-border bg-surface-card/30 p-16 text-center">
    <div class="text-4xl mb-4">⠿</div>
    <p class="text-white font-medium mb-2">No conversions yet</p>
    <p class="text-gray-500 text-sm mb-6">Upload your first document to get started.</p>
    <a href="<?php echo $uploadUrl; ?>" class="px-6 py-3 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-medium text-sm transition-colors">Upload Document</a>
  </div>
  <?php else: ?>
  <div class="rounded-2xl border border-surface-border overflow-hidden">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-surface-border bg-surface-card/50">
          <th class="text-start px-6 py-4 text-gray-400 font-medium">Document</th>
          <th class="text-start px-4 py-4 text-gray-400 font-medium hidden sm:table-cell">Language</th>
          <th class="text-start px-4 py-4 text-gray-400 font-medium hidden md:table-cell">Date</th>
          <th class="text-start px-4 py-4 text-gray-400 font-medium">Status</th>
          <th class="text-end px-6 py-4 text-gray-400 font-medium">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-surface-border">
        <?php foreach ($jobs as $job):
          $statusColors = [
            'pending'    => 'text-yellow-400 bg-yellow-500/10 border-yellow-500/20',
            'processing' => 'text-blue-400 bg-blue-500/10 border-blue-500/20',
            'done'       => 'text-green-400 bg-green-500/10 border-green-500/20',
            'error'      => 'text-red-400 bg-red-500/10 border-red-500/20',
          ];
          $sc = $statusColors[$job['status']] ?? 'text-gray-400 bg-gray-500/10 border-gray-500/20';
        ?>
        <tr class="hover:bg-surface-card/30 transition-colors">
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-surface-card border border-surface-border flex items-center justify-center text-xs text-gray-400 flex-shrink-0">
                <?= strtoupper(pathinfo($job['orig_name'], PATHINFO_EXTENSION)) ?>
              </div>
              <span class="text-white font-medium truncate max-w-[180px]"><?= htmlspecialchars($job['orig_name']) ?></span>
            </div>
          </td>
          <td class="px-4 py-4 text-gray-400 hidden sm:table-cell"><?= strtoupper(htmlspecialchars($job['language'])) ?></td>
          <td class="px-4 py-4 text-gray-500 hidden md:table-cell"><?= date('M j, Y', strtotime($job['created_at'])) ?></td>
          <td class="px-4 py-4">
            <span class="px-2.5 py-1 rounded-full border text-xs font-medium <?= $sc ?>"><?= ucfirst($job['status']) ?></span>
          </td>
          <td class="px-6 py-4 text-end">
            <div class="flex items-center justify-end gap-3">
              <a href="<?php echo $jobBaseUrl . urlencode($job['id']); ?>" class="text-gray-400 hover:text-white transition-colors text-xs">View</a>
              <?php if ($job['status'] === 'done'): ?>
              <a href="<?php echo $downloadBaseUrl . '?id=' . urlencode($job['id']) . '&format=brf'; ?>" class="text-brand-500 hover:text-brand-600 transition-colors text-xs font-medium">BRF ↓</a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</section>
<?php
$content = ob_get_clean();
renderLayout('Dashboard', $content);
