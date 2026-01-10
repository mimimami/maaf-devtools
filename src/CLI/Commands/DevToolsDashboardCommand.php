<?php

declare(strict_types=1);

namespace MAAF\DevTools\CLI\Commands;

use MAAF\Core\Cli\CommandInterface;
use MAAF\DevTools\Dashboard\DevToolsDashboard;

/**
 * DevTools Dashboard Command
 * 
 * DevTools dashboard generálása.
 * 
 * @version 1.0.0
 */
final class DevToolsDashboardCommand implements CommandInterface
{
    public function __construct(
        private readonly ?DevToolsDashboard $dashboard = null
    ) {
    }

    public function getName(): string
    {
        return 'devtools:dashboard';
    }

    public function getDescription(): string
    {
        return 'Generate DevTools dashboard';
    }

    public function execute(array $args): int
    {
        if ($this->dashboard === null) {
            echo "❌ Dashboard not available\n";
            return 1;
        }

        $outputFile = $args[0] ?? 'devtools-dashboard.html';

        echo "Generating DevTools dashboard...\n";
        $html = $this->dashboard->render();
        file_put_contents($outputFile, $html);

        echo "✅ Dashboard generated: {$outputFile}\n";
        return 0;
    }
}
