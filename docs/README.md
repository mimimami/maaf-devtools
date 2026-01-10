# MAAF DevTools Dokumentáció

## Áttekintés

MAAF DevTools egy fejlesztői eszköz rendszer Telescope szerű request inspectorral, modulonkénti lognézettel és query profilerrel.

## Funkciók

- ✅ **Request Inspector** - Telescope szerű request inspector
- ✅ **Modulonkénti Lognézet** - Modul szintű log nézet
- ✅ **Query Profiler** - Query profiler adatbázis lekérdezésekhez
- ✅ **Dashboard** - DevTools dashboard
- ✅ **CLI Támogatás** - DevTools kezelés CLI parancsokkal

## Telepítés

```bash
composer require maaf/devtools
```

## Használat

### Alapvető Használat

```php
use MAAF\DevTools\Request\RequestInspector;
use MAAF\DevTools\Log\ModuleLogView;
use MAAF\DevTools\Query\QueryProfiler;

// Create components
$requestInspector = new RequestInspector('storage/devtools/requests');
$logView = new ModuleLogView('storage/devtools/logs');
$queryProfiler = new QueryProfiler('storage/devtools/queries');
```

### Request Inspector

```php
// Record request
$entry = $requestInspector->record($request, $response, duration: 150.5, memoryUsage: 1024 * 1024);

// Add query to entry
$entry->addQuery(['sql' => 'SELECT * FROM users', 'duration' => 10.5]);

// Add log to entry
$entry->addLog(['level' => 'info', 'message' => 'User logged in']);

// Get recent entries
$recent = $requestInspector->getRecentEntries(20);
```

### Module Log View

```php
// Log for module
$logView->log('UserModule', 'info', 'User created', ['user_id' => 123]);

// Get logs for module
$logs = $logView->getLogs('UserModule', level: 'error', limit: 50);
```

### Query Profiler

```php
// Record query
$queryProfiler->record('SELECT * FROM users WHERE id = ?', [123], duration: 15.5, module: 'UserModule');

// Get slow queries
$slowQueries = $queryProfiler->getSlowQueries(threshold: 100.0);

// Get statistics
$stats = $queryProfiler->getStatistics();
```

## CLI Parancsok

```bash
# Generate dashboard
php maaf devtools:dashboard

# Show statistics
php maaf devtools:stats
```

## További információk

- [API Dokumentáció](api.md)
- [Példák](examples.md)
- [Best Practices](best-practices.md)
