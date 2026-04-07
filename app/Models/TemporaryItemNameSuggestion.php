<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class TemporaryItemNameSuggestion extends Model
{
    protected $table = 'temporary_item_name_suggestions';

    protected $fillable = [
        'branch_id',
        'display_name',
        'normalized_name',
        'last_rate',
        'last_quality',
        'use_count',
        'last_used_at',
    ];

    protected $casts = [
        'last_rate' => 'decimal:2',
        'last_used_at' => 'datetime',
    ];

    /**
     * Normalize for duplicate detection: trim, collapse whitespace, lowercase.
     */
    public static function normalizeName(string $name): string
    {
        $s = trim(preg_replace('/\s+/u', ' ', $name));
        if ($s === '') {
            return '';
        }

        return mb_strtolower($s, 'UTF-8');
    }

    /**
     * Escape LIKE wildcards for SQL LIKE patterns.
     */
    public static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);
    }

    /**
     * Record or update a saved name after a successful Temporary Sale line.
     */
    public static function recordUsage(int $branchId, string $displayName, $rate, ?string $quality): void
    {
        $trimmed = trim($displayName);
        if ($trimmed === '') {
            return;
        }
        $norm = self::normalizeName($trimmed);
        if ($norm === '') {
            return;
        }

        $row = self::query()->where('branch_id', $branchId)->where('normalized_name', $norm)->first();
        if ($row) {
            $row->display_name = $trimmed;
            $row->last_rate = $rate;
            $row->last_quality = $quality;
            $row->last_used_at = now();
            $row->use_count = (int) $row->use_count + 1;
            $row->save();
        } else {
            self::create([
                'branch_id' => $branchId,
                'display_name' => $trimmed,
                'normalized_name' => $norm,
                'last_rate' => $rate,
                'last_quality' => $quality,
                'use_count' => 1,
                'last_used_at' => now(),
            ]);
        }
    }

    /**
     * @return Collection<int, self>
     */
    public static function searchForBranch(int $branchId, string $query, int $limit = 40): Collection
    {
        $normQ = self::normalizeName($query);
        $base = self::query()->where('branch_id', $branchId);

        if ($normQ === '') {
            return $base
                ->orderByDesc('last_used_at')
                ->limit(min($limit, 25))
                ->get(['id', 'display_name', 'normalized_name', 'last_rate', 'last_quality']);
        }

        $esc = self::escapeLike($normQ);

        return $base
            ->where('normalized_name', 'like', '%'.$esc.'%')
            ->orderByRaw('CASE WHEN normalized_name LIKE ? THEN 0 ELSE 1 END', [$esc.'%'])
            ->orderByDesc('last_used_at')
            ->limit($limit)
            ->get(['id', 'display_name', 'normalized_name', 'last_rate', 'last_quality']);
    }
}
