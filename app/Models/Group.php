<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    public const PHONE_CAPACITY = 250;

    protected $fillable = [
        'name',
        'base_name',
        'status',
    ];

    public function item_group()
    {
        return $this->hasOne(Item::class, 'gorup');
    }

    public function phoneNumbers(): HasMany
    {
        return $this->hasMany(GroupPhoneNumber::class);
    }

    /** Non-frozen numbers (for export and messaging).
     * When exporting group data or sending messages to a group, use this relation
     * so frozen numbers are excluded: $group->activePhoneNumbers()->get()
     */
    public function activePhoneNumbers(): HasMany
    {
        return $this->hasMany(GroupPhoneNumber::class)->active();
    }

    /**
     * Get or create the group that has room for more numbers (max 250 per group).
     * If the given group is full, creates the next group in series (e.g. Name-02) and returns it.
     */
    public static function resolveGroupForNewNumbers(int $groupId): self
    {
        $group = self::withCount(['phoneNumbers'])->findOrFail($groupId);
        if ($group->phone_numbers_count < self::PHONE_CAPACITY) {
            return $group;
        }

        return self::createNextInSeries($group->base_name ?? $group->name);
    }

    /**
     * Create the next group in series (e.g. "Company A" -> "Company A-02", then "Company A-03").
     */
    public static function createNextInSeries(string $baseName): self
    {
        $baseName = trim($baseName);
        $sameBase = self::where('base_name', $baseName)->orderBy('id', 'desc')->get();
        $maxNum = 0;
        foreach ($sameBase as $g) {
            if (preg_match('/-(\d+)$/', $g->name, $m)) {
                $maxNum = max($maxNum, (int) $m[1]);
            }
        }
        $nextNum = $maxNum === 0 ? 2 : $maxNum + 1;
        $name = $baseName.'-'.str_pad((string) $nextNum, 2, '0', STR_PAD_LEFT);

        return self::create([
            'name' => $name,
            'base_name' => $baseName,
            'status' => 'active',
        ]);
    }
}
