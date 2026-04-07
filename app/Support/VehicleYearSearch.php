<?php

namespace App\Support;

/**
 * Vehicle fitment year matching for item search (purchase/sales ajax).
 *
 * Supports year_from / year_to as separate columns and combined "2012-2020" text in year_from
 * when year_to is empty. Uses MySQL/MariaDB numeric coercion on strings (e.g. '2012-2020'+0 → 2012).
 */
class VehicleYearSearch
{
    public const MIN_YEAR = 1900;

    public const MAX_YEAR = 2100;

    /**
     * True for a whole-number search token that should be treated as a model year (not a vehicle id, etc.).
     */
    public static function isPlausibleYearTerm(string $term): bool
    {
        if ($term === '' || ! ctype_digit($term)) {
            return false;
        }
        $y = (int) $term;

        return $y >= self::MIN_YEAR && $y <= self::MAX_YEAR;
    }

    /**
     * Match rows where the given calendar year lies within the vehicle's stored range (inclusive).
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    public static function whereVehicleRowContainsYear($query, int $year, string $table = 'vehical_types'): void
    {
        $query->whereRaw(self::rangePredicateSql($table), [$year, $year]);
    }

    /**
     * Match rows where the vehicle's year range overlaps [rangeStart, rangeEnd] (inclusive).
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    public static function whereVehicleRowOverlapsYearRange($query, int $rangeStart, int $rangeEnd, string $table = 'vehical_types'): void
    {
        $high = max($rangeStart, $rangeEnd);
        $low = min($rangeStart, $rangeEnd);

        $query->whereRaw(self::rangePredicateSql($table), [$high, $low]);
    }

    private static function rangePredicateSql(string $table): string
    {
        $yf = "`{$table}`.`year_from`";
        $yt = "`{$table}`.`year_to`";
        $endExpr = self::vehicleEndYearExpression($yf, $yt);

        return "(({$yf} + 0) <= ? AND ({$endExpr}) >= ?)";
    }

    /**
     * Effective end year: year_to if numeric, else last segment of year_from when it contains '-', else start.
     */
    private static function vehicleEndYearExpression(string $yearFrom, string $yearTo): string
    {
        return 'COALESCE(NULLIF(TRIM('.$yearTo.') + 0, 0), IF(TRIM(IFNULL('.$yearFrom.', \'\')) LIKE \'%-%\', SUBSTRING_INDEX('.$yearFrom.', \'-\', -1) + 0, NULL), ('.$yearFrom.' + 0))';
    }
}
