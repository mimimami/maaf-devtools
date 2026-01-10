# MAAF DevTools Példák

## Alapvető Használat

### DevTools Setup

```php
use MAAF\DevTools\Request\RequestInspector;
use MAAF\DevTools\Log\ModuleLogView;
use MAAF\DevTools\Query\QueryProfiler;
use MAAF\DevTools\Dashboard\DevToolsDashboard;

// Create components
$requestInspector = new RequestInspector('storage/devtools/requests');
$logView = new ModuleLogView('storage/devtools/logs');
$queryProfiler = new QueryProfiler('storage/devtools/queries');

// Create dashboard
$dashboard = new DevToolsDashboard($requestInspector, $logView, $queryProfiler);
```

## Request Inspector

### Request Recording

```php
use MAAF\Core\Http\Request;
use MAAF\Core\Http\Response;
use MAAF\DevTools\Request\RequestInspector;

$inspector = new RequestInspector('storage/devtools/requests');

// Record request
$startTime = microtime(true);
$startMemory = memory_get_usage(true);

// ... handle request ...

$duration = (microtime(true) - $startTime) * 1000;
$memoryUsage = memory_get_usage(true) - $startMemory;

$entry = $inspector->record($request, $response, $duration, $memoryUsage);

// Add additional data
$entry->addQuery([
    'sql' => 'SELECT * FROM users',
    'duration' => 10.5,
    'bindings' => [],
]);

$entry->addLog([
    'level' => 'info',
    'message' => 'User logged in',
    'context' => ['user_id' => 123],
]);

$entry->addEvent([
    'type' => 'cache.hit',
    'key' => 'user:123',
]);
```

### Request Entry Használat

```php
// Get entry
$entry = $inspector->getEntry($entryId);

if ($entry !== null) {
    echo "Method: " . $entry->getRequest()->getMethod() . "\n";
    echo "Path: " . $entry->getRequest()->getPath() . "\n";
    echo "Duration: " . $entry->getDuration() . " ms\n";
    echo "Memory: " . round($entry->getMemoryUsage() / 1024 / 1024, 2) . " MB\n";
    
    // Get queries
    $queries = $entry->getQueries();
    echo "Queries: " . count($queries) . "\n";
    
    // Get logs
    $logs = $entry->getLogs();
    echo "Logs: " . count($logs) . "\n";
}
```

### Recent Entries

```php
// Get recent entries
$recentEntries = $inspector->getRecentEntries(limit: 20);

foreach ($recentEntries as $entry) {
    echo sprintf(
        "%s %s [%d] - %.2f ms\n",
        $entry->getRequest()->getMethod(),
        $entry->getRequest()->getPath(),
        $entry->getResponse()?->getStatusCode() ?? 0,
        $entry->getDuration()
    );
}

// Get entries by path
$userEntries = $inspector->getEntriesByPath('/users');
```

## Modulonkénti Lognézet

### Log Kezelés

```php
use MAAF\DevTools\Log\ModuleLogView;

$logView = new ModuleLogView('storage/devtools/logs');

// Log for module
$logView->log('UserModule', 'info', 'User created', ['user_id' => 123]);
$logView->log('UserModule', 'error', 'Failed to create user', ['error' => 'Validation failed']);
$logView->log('ProductModule', 'warning', 'Low stock', ['product_id' => 456, 'stock' => 5]);
```

### Log Lekérdezés

```php
// Get logs for module
$logs = $logView->getLogs('UserModule', level: null, limit: 100);

foreach ($logs as $log) {
    echo sprintf(
        "[%s] %s: %s\n",
        $log['timestamp'],
        strtoupper($log['level']),
        $log['message']
    );
}

// Get error logs only
$errorLogs = $logView->getLogs('UserModule', level: 'error', limit: 50);

// Get all module logs
$allLogs = $logView->getAllLogs();

foreach ($allLogs as $module => $logs) {
    echo "Module: {$module}\n";
    echo "  Total logs: " . count($logs) . "\n";
}
```

## Query Profiler

### Query Recording

```php
use MAAF\DevTools\Query\QueryProfiler;

$profiler = new QueryProfiler('storage/devtools/queries');

// Record query
$startTime = microtime(true);
$result = $pdo->query('SELECT * FROM users WHERE id = ?');
$duration = (microtime(true) - $startTime) * 1000;

$profiler->record(
    sql: 'SELECT * FROM users WHERE id = ?',
    bindings: [123],
    duration: $duration,
    module: 'UserModule'
);
```

### Query Analysis

```php
// Get queries for module
$queries = $profiler->getQueries(module: 'UserModule', limit: 100);

foreach ($queries as $query) {
    echo "SQL: " . $query->getFormattedSql() . "\n";
    echo "Duration: " . $query->getDuration() . " ms\n";
    echo "Module: " . ($query->getModule() ?? 'N/A') . "\n";
    echo "\n";
}

// Get slow queries
$slowQueries = $profiler->getSlowQueries(threshold: 100.0);

echo "Slow queries (>100ms):\n";
foreach ($slowQueries as $query) {
    echo sprintf(
        "%.2f ms - %s\n",
        $query->getDuration(),
        substr($query->getSql(), 0, 80)
    );
}

// Get statistics
$stats = $profiler->getStatistics();
echo "Total queries: " . $stats['total_queries'] . "\n";
echo "Total duration: " . round($stats['total_duration'], 2) . " ms\n";
echo "Avg duration: " . round($stats['avg_duration'], 2) . " ms\n";
echo "Slow queries: " . $stats['slow_queries'] . "\n";
```

## Middleware Integration

### Request Inspector Middleware

```php
use MAAF\Core\Http\MiddlewareInterface;
use MAAF\Core\Http\Request;
use MAAF\Core\Http\Response;
use MAAF\DevTools\Request\RequestInspector;

class DevToolsMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly RequestInspector $inspector
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        try {
            $response = $next($request);
        } finally {
            $duration = (microtime(true) - $startTime) * 1000;
            $memoryUsage = memory_get_usage(true) - $startMemory;

            $entry = $this->inspector->record($request, $response ?? null, $duration, $memoryUsage);
            
            // Add request data
            $entry->setData('ip', $request->getIp());
            $entry->setData('user_agent', $request->getHeader('User-Agent'));
        }

        return $response;
    }
}
```

### Query Profiler Integration

```php
use MAAF\DevTools\Query\QueryProfiler;

class DatabaseWrapper
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly ?QueryProfiler $profiler = null
    ) {
    }

    public function query(string $sql, array $bindings = [], ?string $module = null): array
    {
        $startTime = microtime(true);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $duration = (microtime(true) - $startTime) * 1000;

        if ($this->profiler !== null) {
            $this->profiler->record($sql, $bindings, $duration, $module);
        }

        return $result;
    }
}
```

## Dashboard

### Dashboard Generálás

```php
use MAAF\DevTools\Dashboard\DevToolsDashboard;

$dashboard = new DevToolsDashboard($requestInspector, $logView, $queryProfiler);

// Get dashboard data
$data = $dashboard->getData();

// Render dashboard HTML
$html = $dashboard->render();
file_put_contents('devtools-dashboard.html', $html);
```

### Dashboard Adatok

```php
$data = $dashboard->getData();

// Request data
$requests = $data['requests'];
$recentRequests = $requests['recent'];

// Log data
$logs = $data['logs'];
$moduleLogs = $logs['modules'];

// Query data
$queries = $data['queries'];
$queryStats = $queries['statistics'];
$slowQueries = $queries['slow_queries'];
```

## Teljes Példa

### Setup és Használat

```php
use MAAF\DevTools\Request\RequestInspector;
use MAAF\DevTools\Log\ModuleLogView;
use MAAF\DevTools\Query\QueryProfiler;
use MAAF\DevTools\Dashboard\DevToolsDashboard;

// 1. Create components
$requestInspector = new RequestInspector('storage/devtools/requests');
$logView = new ModuleLogView('storage/devtools/logs');
$queryProfiler = new QueryProfiler('storage/devtools/queries');

// 2. Record request
$entry = $requestInspector->record($request, $response, duration: 150.5, memoryUsage: 1024 * 1024);

// 3. Record query
$queryProfiler->record('SELECT * FROM users', [], duration: 10.5, module: 'UserModule');

// 4. Log for module
$logView->log('UserModule', 'info', 'User created', ['user_id' => 123]);

// 5. Generate dashboard
$dashboard = new DevToolsDashboard($requestInspector, $logView, $queryProfiler);
$html = $dashboard->render();
file_put_contents('devtools-dashboard.html', $html);
```

## CLI Használat

```bash
# Generate dashboard
php maaf devtools:dashboard

# Generate dashboard to specific file
php maaf devtools:dashboard my-dashboard.html

# Show statistics
php maaf devtools:stats
```

## Best Practices

### Request Inspector

```php
// 1. Only enable in development
if (getenv('APP_ENV') === 'development') {
    $inspector = new RequestInspector('storage/devtools/requests');
    $inspector->enable();
} else {
    $inspector->disable();
}

// 2. Record all relevant data
$entry->addQuery(['sql' => $sql, 'duration' => $duration]);
$entry->addLog(['level' => 'info', 'message' => $message]);
$entry->setData('user_id', $userId);

// 3. Limit storage
$recentEntries = $inspector->getRecentEntries(limit: 100);
```

### Module Log View

```php
// 1. Use appropriate log levels
$logView->log('UserModule', 'info', 'User created');
$logView->log('UserModule', 'warning', 'Low stock');
$logView->log('UserModule', 'error', 'Failed to create user');

// 2. Include context
$logView->log('UserModule', 'error', 'Validation failed', [
    'user_id' => 123,
    'errors' => $validationErrors,
]);

// 3. Filter by level
$errorLogs = $logView->getLogs('UserModule', level: 'error');
```

### Query Profiler

```php
// 1. Record all queries
$profiler->record($sql, $bindings, $duration, $module);

// 2. Identify slow queries
$slowQueries = $profiler->getSlowQueries(threshold: 100.0);

// 3. Analyze by module
$moduleQueries = $profiler->getQueries(module: 'UserModule');
$stats = $profiler->getStatistics();
```

### Performance

```php
// 1. Disable in production
if (getenv('APP_ENV') === 'production') {
    $requestInspector->disable();
    $queryProfiler->disable();
}

// 2. Use storage limits
$recentEntries = $requestInspector->getRecentEntries(limit: 50);
$recentQueries = $queryProfiler->getQueries(limit: 100);

// 3. Clear old data periodically
$requestInspector->clear();
$queryProfiler->clear();
```
