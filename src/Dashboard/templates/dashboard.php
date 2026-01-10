<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAAF DevTools Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        .header { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h3 { font-size: 16px; color: #666; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .stat { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
        .stat:last-child { border-bottom: none; }
        .stat-label { color: #666; }
        .stat-value { font-weight: 600; color: #333; }
        .request-item { padding: 10px; background: #f9f9f9; border-radius: 4px; margin-bottom: 8px; }
        .request-method { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 12px; font-weight: 600; }
        .method-get { background: #d4edda; color: #155724; }
        .method-post { background: #d1ecf1; color: #0c5460; }
        .method-put { background: #fff3cd; color: #856404; }
        .method-delete { background: #f8d7da; color: #721c24; }
        .status-code { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 12px; font-weight: 600; }
        .status-2xx { background: #d4edda; color: #155724; }
        .status-4xx { background: #fff3cd; color: #856404; }
        .status-5xx { background: #f8d7da; color: #721c24; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9f9f9; font-weight: 600; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>MAAF DevTools Dashboard</h1>
            <p>Request inspector, logs, and query profiler</p>
        </div>

        <div class="grid">
            <div class="card">
                <h3>Recent Requests</h3>
                <?php foreach (array_slice($data['requests']['recent'] ?? [], 0, 10) as $request): ?>
                <div class="request-item">
                    <div>
                        <span class="request-method method-<?= strtolower($request['method'] ?? 'get') ?>">
                            <?= htmlspecialchars($request['method'] ?? 'GET') ?>
                        </span>
                        <span class="status-code status-<?= substr((string)($request['status_code'] ?? 200), 0, 1) ?>xx">
                            <?= $request['status_code'] ?? 200 ?>
                        </span>
                        <strong><?= htmlspecialchars($request['path'] ?? '') ?></strong>
                    </div>
                    <div style="font-size: 12px; color: #666; margin-top: 4px;">
                        <?= round($request['duration'] ?? 0, 2) ?> ms | 
                        <?= round(($request['memory_usage'] ?? 0) / 1024 / 1024, 2) ?> MB
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <h3>Module Logs</h3>
                <?php foreach ($data['logs']['modules'] ?? [] as $module => $stats): ?>
                <div class="stat">
                    <span class="stat-label"><?= htmlspecialchars($module) ?></span>
                    <span class="stat-value"><?= $stats['total'] ?? 0 ?></span>
                </div>
                <?php if (!empty($stats['by_level'])): ?>
                <div style="padding-left: 20px; font-size: 12px; color: #666;">
                    <?php foreach ($stats['by_level'] as $level => $count): ?>
                    <span class="badge badge-<?= $level === 'error' ? 'danger' : ($level === 'warning' ? 'warning' : 'info') ?>">
                        <?= strtoupper($level) ?>: <?= $count ?>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <h3>Query Statistics</h3>
                <div class="stat">
                    <span class="stat-label">Total Queries</span>
                    <span class="stat-value"><?= $data['queries']['statistics']['total_queries'] ?? 0 ?></span>
                </div>
                <div class="stat">
                    <span class="stat-label">Total Duration</span>
                    <span class="stat-value"><?= round($data['queries']['statistics']['total_duration'] ?? 0, 2) ?> ms</span>
                </div>
                <div class="stat">
                    <span class="stat-label">Avg Duration</span>
                    <span class="stat-value"><?= round($data['queries']['statistics']['avg_duration'] ?? 0, 2) ?> ms</span>
                </div>
                <div class="stat">
                    <span class="stat-label">Slow Queries</span>
                    <span class="stat-value"><?= $data['queries']['statistics']['slow_queries'] ?? 0 ?></span>
                </div>
            </div>
        </div>

        <?php if (!empty($data['queries']['slow_queries'])): ?>
        <div class="card">
            <h3>Slow Queries (>100ms)</h3>
            <table>
                <thead>
                    <tr>
                        <th>SQL</th>
                        <th>Duration</th>
                        <th>Module</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['queries']['slow_queries'] as $query): ?>
                    <tr>
                        <td style="font-family: monospace; font-size: 12px;">
                            <?= htmlspecialchars(substr($query['sql'] ?? '', 0, 100)) ?>...
                        </td>
                        <td><?= round($query['duration'] ?? 0, 2) ?> ms</td>
                        <td><?= htmlspecialchars($query['module'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($query['timestamp'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
