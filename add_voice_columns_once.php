<?php
/**
 * One-time script: Add voice_path and voice_transcript to items table.
 * Run: php add_voice_columns_once.php
 */
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

$table = 'items';
if (Schema::hasColumn($table, 'voice_path') && Schema::hasColumn($table, 'voice_transcript')) {
    echo "Columns already exist. Done.\n";
    exit(0);
}

Schema::table($table, function (Blueprint $t) {
    if (!Schema::hasColumn('items', 'voice_path')) {
        $t->string('voice_path')->nullable()->after('notes');
    }
    if (!Schema::hasColumn('items', 'voice_transcript')) {
        $t->text('voice_transcript')->nullable()->after('voice_path');
    }
});

echo "Added voice_path and voice_transcript to items table. Done.\n";
