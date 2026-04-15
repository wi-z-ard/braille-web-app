<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/layout.php';
require_once __DIR__ . '/../../src/Admin/Admin.php';

Admin::requireAdmin();

$stats = Admin::getStats();
$recentJobs = Admin::getAllJobs(10);
$recentUsers = Admin::getAllUsers(10);

ob_start();
?>
<section class="max-w-7xl mx-auto px-6 py-12">
  <div class="mb-10">
    <h1 class="text-3xl font-bold text-white mb-2">Admin Dashboard</h1>
    <p class="text-gray-400">System overview and management</p>
  </div>

  <!-- Stats Grid -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
    <?php
    $statCards = [
      ['Total Users', $stats['total_users'], 'text-blue-400', 'lni lni-users'],
      ['Total Jobs', $stats['total_jobs'], 'text-purple-400', 'lni lni-files'],
      ['Success Rate', $stats['success_rate'] . '%', 'text-green-400', 'lni lni-checkmark-circle'],
      ['Active Jobs', $stats['active_jobs'], 'text-yellow-400', 'lni lni-pulse'],
    ];
    foreach ($statCards as [$label, $value, $color, $icon]):
    ?>
    <div class="rounded-xl border border-surface-border bg-surface-card p-6">
      <div class="flex items-center justify-between mb-2">
        <i class="<?= $icon ?> text-2xl <?= $color ?>"></i>
        <span class="text-xs text-gray-500"><?= $label ?></span>
      </div>
      <div class="text-2xl font-bold <?= $color ?>"><?= $value ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Tabs -->
  <div class="border-b border-surface-border mb-6">
    <nav class="flex gap-6">
      <button onclick="showTab('settings')" id="tab-settings" class="tab-btn active pb-3 px-1 text-sm font-medium border-b-2 border-brand-500 text-white">Settings</button>
      <button onclick="showTab('users')" id="tab-users" class="tab-btn pb-3 px-1 text-sm font-medium border-b-2 border-transparent text-gray-400 hover:text-white">Users</button>
      <button onclick="showTab('jobs')" id="tab-jobs" class="tab-btn pb-3 px-1 text-sm font-medium border-b-2 border-transparent text-gray-400 hover:text-white">Jobs</button>
      <button onclick="showTab('activity')" id="tab-activity" class="tab-btn pb-3 px-1 text-sm font-medium border-b-2 border-transparent text-gray-400 hover:text-white">Activity Logs</button>
      <button onclick="showTab('system')" id="tab-system" class="tab-btn pb-3 px-1 text-sm font-medium border-b-2 border-transparent text-gray-400 hover:text-white">System Health</button>
    </nav>
  </div>

  <!-- Settings Tab -->
  <div id="content-settings" class="tab-content">
    <div class="rounded-2xl border border-surface-border bg-surface-card p-6">
      <h2 class="text-xl font-bold text-white mb-4">System Settings</h2>
      <form id="settings-form" class="space-y-4">
        <?php
        $settings = Settings::getAll();
        foreach ($settings as $setting):
        ?>
        <div class="flex items-center justify-between py-3 border-b border-surface-border last:border-0">
          <div class="flex-1">
            <label class="text-sm font-medium text-white"><?= htmlspecialchars($setting['setting_key']) ?></label>
            <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($setting['description']) ?></p>
          </div>
          <input type="number" 
                 name="<?= htmlspecialchars($setting['setting_key']) ?>" 
                 value="<?= htmlspecialchars($setting['setting_value']) ?>"
                 class="w-24 px-3 py-2 rounded-lg bg-[#0f1117] border border-surface-border text-white text-sm focus:border-brand-500 focus:outline-none">
        </div>
        <?php endforeach; ?>
        <button type="submit" class="px-6 py-2.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white font-medium transition-colors">
          Save Settings
        </button>
      </form>
    </div>
  </div>

  <!-- Users Tab -->
  <div id="content-users" class="tab-content hidden">
    <div class="rounded-xl border border-surface-border overflow-hidden">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-surface-border bg-surface-card/50">
            <th class="text-start px-6 py-4 text-gray-400 font-medium">User</th>
            <th class="text-start px-4 py-4 text-gray-400 font-medium">Jobs</th>
            <th class="text-start px-4 py-4 text-gray-400 font-medium">Joined</th>
            <th class="text-start px-4 py-4 text-gray-400 font-medium">Status</th>
            <th class="text-end px-6 py-4 text-gray-400 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-surface-border">
          <?php foreach ($recentUsers as $user): ?>
          <tr class="hover:bg-surface-card/30">
            <td class="px-6 py-4">
              <div class="font-medium text-white"><?= htmlspecialchars($user['name']) ?></div>
              <div class="text-xs text-gray-500"><?= htmlspecialchars($user['email']) ?></div>
            </td>
            <td class="px-4 py-4 text-gray-400"><?= $user['completed_jobs'] ?> / <?= $user['total_jobs'] ?></td>
            <td class="px-4 py-4 text-gray-500"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
            <td class="px-4 py-4">
              <?php if ($user['is_admin']): ?>
                <span class="px-2 py-1 rounded-full bg-purple-500/10 text-purple-400 text-xs border border-purple-500/20">Admin</span>
              <?php elseif ($user['is_active']): ?>
                <span class="px-2 py-1 rounded-full bg-green-500/10 text-green-400 text-xs border border-green-500/20">Active</span>
              <?php else: ?>
                <span class="px-2 py-1 rounded-full bg-red-500/10 text-red-400 text-xs border border-red-500/20">Suspended</span>
              <?php endif; ?>
            </td>
            <td class="px-6 py-4 text-end">
              <?php if (!$user['is_admin']): ?>
              <button onclick="toggleUser(<?= $user['id'] ?>)" class="text-xs text-gray-400 hover:text-white mr-3">
                <?= $user['is_active'] ? 'Suspend' : 'Activate' ?>
              </button>
              <button onclick="deleteUser(<?= $user['id'] ?>)" class="text-xs text-red-400 hover:text-red-300">Delete</button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Jobs Tab -->
  <div id="content-jobs" class="tab-content hidden">
    <div class="rounded-xl border border-surface-border overflow-hidden">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-surface-border bg-surface-card/50">
            <th class="text-start px-6 py-4 text-gray-400 font-medium">Job ID</th>
            <th class="text-start px-4 py-4 text-gray-400 font-medium">User</th>
            <th class="text-start px-4 py-4 text-gray-400 font-medium">File</th>
            <th class="text-start px-4 py-4 text-gray-400 font-medium">Status</th>
            <th class="text-start px-4 py-4 text-gray-400 font-medium">Created</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-surface-border">
          <?php foreach ($recentJobs as $job): 
            $statusColors = [
              'pending' => 'text-yellow-400 bg-yellow-500/10 border-yellow-500/20',
              'processing' => 'text-blue-400 bg-blue-500/10 border-blue-500/20',
              'done' => 'text-green-400 bg-green-500/10 border-green-500/20',
              'error' => 'text-red-400 bg-red-500/10 border-red-500/20',
            ];
          ?>
          <tr class="hover:bg-surface-card/30">
            <td class="px-6 py-4 font-mono text-xs text-gray-400"><?= htmlspecialchars($job['id']) ?></td>
            <td class="px-4 py-4">
              <div class="text-white text-xs"><?= htmlspecialchars($job['user_name'] ?? 'Unknown') ?></div>
              <div class="text-gray-500 text-xs"><?= htmlspecialchars($job['user_email'] ?? '') ?></div>
            </td>
            <td class="px-4 py-4 text-gray-400 truncate max-w-[200px]"><?= htmlspecialchars($job['orig_name']) ?></td>
            <td class="px-4 py-4">
              <span class="px-2 py-1 rounded-full text-xs border <?= $statusColors[$job['status']] ?>">
                <?= ucfirst($job['status']) ?>
              </span>
            </td>
            <td class="px-4 py-4 text-gray-500 text-xs"><?= date('M j, H:i', strtotime($job['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Activity Tab -->
  <div id="content-activity" class="tab-content hidden">
    <div class="rounded-xl border border-surface-border overflow-hidden">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-surface-border bg-surface-card/50">
            <th class="text-start px-6 py-4 text-gray-400 font-medium">User</th>
            <th class="text-start px-4 py-4 text-gray-400 font-medium">Action</th>
            <th class="text-start px-4 py-4 text-gray-400 font-medium">Details</th>
            <th class="text-start px-4 py-4 text-gray-400 font-medium">IP</th>
            <th class="text-start px-4 py-4 text-gray-400 font-medium">Time</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $activities = ActivityLog::getRecent(50);
          if (empty($activities)):
          ?>
          <tr>
            <td colspan="5" class="px-6 py-8 text-center text-gray-500">No activity logs yet</td>
          </tr>
          <?php else: foreach ($activities as $log): ?>
          <tr class="border-b border-surface-border hover:bg-surface-card/30">
            <td class="px-6 py-4">
              <div class="text-white font-medium"><?= htmlspecialchars($log['user_name']) ?></div>
              <div class="text-xs text-gray-500"><?= htmlspecialchars($log['user_email']) ?></div>
            </td>
            <td class="px-4 py-4">
              <span class="px-2 py-1 rounded-full bg-brand-500/10 text-brand-400 text-xs font-medium">
                <?= htmlspecialchars($log['action']) ?>
              </span>
            </td>
            <td class="px-4 py-4 text-gray-300"><?= htmlspecialchars($log['details'] ?? '-') ?></td>
            <td class="px-4 py-4 text-gray-400 font-mono text-xs"><?= htmlspecialchars($log['ip_address'] ?? '-') ?></td>
            <td class="px-4 py-4 text-gray-400 text-xs"><?= htmlspecialchars($log['created_at']) ?></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- System Health Tab -->
  <div id="content-system" class="tab-content hidden">
    <div class="grid gap-4">
      <div class="rounded-xl border border-surface-border bg-surface-card p-6">
        <h3 class="font-semibold text-white mb-4">System Status</h3>
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-gray-400">Python Service</span>
            <span class="px-2 py-1 rounded-full bg-green-500/10 text-green-400 text-xs" id="python-status">Checking...</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-gray-400">Database</span>
            <span class="px-2 py-1 rounded-full bg-green-500/10 text-green-400 text-xs">Connected</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-gray-400">Storage</span>
            <span class="px-2 py-1 rounded-full bg-green-500/10 text-green-400 text-xs">Writable</span>
          </div>
        </div>
      </div>
      
      <div class="rounded-xl border border-surface-border bg-surface-card p-6">
        <h3 class="font-semibold text-white mb-4">Statistics (Last 24h)</h3>
        <div class="text-3xl font-bold text-brand-500 mb-2"><?= $stats['jobs_24h'] ?></div>
        <p class="text-sm text-gray-400">Jobs processed</p>
      </div>
    </div>
  </div>
</section>

<script>
function showTab(tab) {
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.classList.remove('active', 'border-brand-500', 'text-white');
    btn.classList.add('border-transparent', 'text-gray-400');
  });
  document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
  
  document.getElementById('tab-' + tab).classList.add('active', 'border-brand-500', 'text-white');
  document.getElementById('content-' + tab).classList.remove('hidden');
}

async function toggleUser(userId) {
  if (!confirm('Toggle user status?')) return;
  const res = await fetch(window.BASE_PATH + '/api/admin/toggle-user', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({user_id: userId})
  });
  if (res.ok) location.reload();
}

async function deleteUser(userId) {
  if (!confirm('Permanently delete this user and all their jobs?')) return;
  const res = await fetch(window.BASE_PATH + '/api/admin/delete-user', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({user_id: userId})
  });
  if (res.ok) location.reload();
}

// Settings form
document.getElementById('settings-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const formData = new FormData(e.target);
  const settings = Object.fromEntries(formData);
  
  const res = await fetch(window.BASE_PATH + '/api/admin/update-settings', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(settings)
  });
  
  if (res.ok) {
    alert('Settings saved successfully');
  } else {
    alert('Failed to save settings');
  }
});

// Check Python service
fetch('http://127.0.0.1:8001/health').then(r => {
  document.getElementById('python-status').textContent = r.ok ? 'Running' : 'Offline';
  document.getElementById('python-status').className = r.ok 
    ? 'px-2 py-1 rounded-full bg-green-500/10 text-green-400 text-xs'
    : 'px-2 py-1 rounded-full bg-red-500/10 text-red-400 text-xs';
}).catch(() => {
  document.getElementById('python-status').textContent = 'Offline';
  document.getElementById('python-status').className = 'px-2 py-1 rounded-full bg-red-500/10 text-red-400 text-xs';
});
</script>
<?php
$content = ob_get_clean();
renderLayout('Admin Dashboard', $content);
