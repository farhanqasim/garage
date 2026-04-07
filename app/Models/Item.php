<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'vehical_id', 'vehical_ids', 'total_price',  'price_per_unit', 'sale_price', 'on_hand',  'is_active', 'auto_deactive', 'is_dead', 'barcode_image',
        'bar_code', 'p_id', 'mileage', 'type', 'plat_id', 'amphors',
        'lineitems', 'company_id', 'category_id', 'subcategory_id',
        'p_brochure', 'image', 'images',
        'car_company', 'volt', 'cca', 'minus_pool_direction', 'pole_thickness_id', 'pool_direction_id',
        'technology', 'series_id', 'grade', 'farmula', 'serial_number', 'battery_size',
        'bussiness_location', 'quality_id', 'part_number_id', 'l_stock',
        'm_stock', 'unit', 'unit_option', 'packing', 'scale', 'filling',
        'weight_for_delivery', 'weight_unit', 'packing_purchase_rate',
        'total_sale_price', 'sale_price_per_base', 'retail_price', 'tax_percentage', 'r_tax_percentage', 'amount_adjustment_pct', 'price_updated_branch_id', 'services', 'warrenty', 'gorup', 'made_in', 'level',
        'update_date', 'rack', 'supplier', 'pro_dis', 'short_disc',
        'updated_by', 'last_updated_at',
        'is_temporary', 'notes',
        'voice_path', 'voice_transcript', 'notes_voice_path',
        'scrap_dim_width',
        'scrap_dim_height',
        'scrap_dim_length',
        'scrap_dim_depth',
        'scrap_dim_unit',
        'name',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'is_temporary' => 'boolean',
        'images' => 'array',
        'is_active' => 'boolean',
        'auto_deactive' => 'boolean',
        'is_dead' => 'boolean',
        'car_manufacture_year' => 'date',
        'update_date' => 'date',
        'last_updated_at' => 'datetime',
        'vehical_ids' => 'array',
        'filling' => 'decimal:2',
        'weight_for_delivery' => 'decimal:2',
        'packing_purchase_rate' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'r_tax_percentage' => 'decimal:2',
        'amount_adjustment_pct' => 'decimal:2',
        'min_qty' => 'integer',
        'max_qty' => 'integer',
        'scrap_dim_width' => 'decimal:2',
        'scrap_dim_height' => 'decimal:2',
        'scrap_dim_length' => 'decimal:2',
        'scrap_dim_depth' => 'decimal:2',
    ];

    /* -------------------------------------------------
     *  THUMBNAIL (single image)
     * ------------------------------------------------- */
    protected function image(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $fallback = asset('assets/img/icons/image.svg');
                if (! $value) {
                    return $fallback;
                }
                if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
                    return $value;
                }
                $path = str_starts_with($value, '/') ? public_path(ltrim($value, '/')) : public_path($value);
                if (! file_exists($path)) {
                    return $fallback;
                }

                return asset($value);
            },

            // When you assign $item->image = $request->file('image')
            set: fn ($value) => $value instanceof \Illuminate\Http\UploadedFile
                ? saveSingleFile($value, 'items')               // <-- your helper
                : $value
        );
    }

    /* -------------------------------------------------
     *  GALLERY (multiple images) – JSON column
     * ------------------------------------------------- */
    protected function images(): Attribute
    {
        return Attribute::make(
            // GET – safely decode JSON + prepend asset()
            get: function ($value) {
                if (empty($value)) {
                    return [];
                }

                $decoded = json_decode($value, true);

                // Guard against malformed JSON
                if (! is_array($decoded)) {
                    \Log::warning('Invalid images JSON for Item ID: '.$this->id, ['raw' => $value]);

                    return [];
                }

                // Return full URLs
                return array_map(fn ($path) => asset($path), $decoded);
            },

            // SET – accept UploadedFile[] or array of paths
            set: function ($value) {
                // If an array of UploadedFile objects
                if (is_array($value) && ! empty($value) && $value[0] instanceof \Illuminate\Http\UploadedFile) {
                    return json_encode(saveMultipleFiles($value, 'items/gallery'));
                }

                // If already an array of paths (e.g. from old data)
                if (is_array($value)) {
                    return json_encode($value);
                }

                // Fallback
                return $value;
            }
        );
    }

    // -----------------------------------------------------------------
    // Relationships (unchanged)
    // -----------------------------------------------------------------

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function item_user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vehical_item()
    {
        return $this->belongsTo(VehicalType::class, 'vehical_id');
    }

    /**
     * Get vehicle models from vehical_ids array (with relationships for display)
     */
    public function vehical_items()
    {
        $ids = $this->vehical_ids ?? [];
        if (empty($ids)) {
            return collect([]);
        }

        return VehicalType::with(['manutacturer_vehical', 'model_vehical'])
            ->whereIn('id', $ids)
            ->get();
    }

    public function subcategory()
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function product_item()
    {
        return $this->belongsTo(Product::class, 'p_id');
    }

    public function mileage_item()
    {
        return $this->belongsTo(Mileage::class, 'mileage');
    }

    public function plate_item()
    {
        return $this->belongsTo(Platos::class, 'plat_id');
    }

    public function amphors_item()
    {
        return $this->belongsTo(Amphor::class, 'amphors');
    }

    public function lineitems_item()
    {
        return $this->belongsTo(LineItem::class, 'lineitems');
    }

    public function company_item()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function volt_item()
    {
        return $this->belongsTo(Volt::class, 'volt');
    }

    public function cca_item()
    {
        return $this->belongsTo(Cca::class, 'cca');
    }

    public function minus_pool_item()
    {
        return $this->belongsTo(Minuspool::class, 'minus_pool_direction');
    }

    public function technology_item()
    {
        return $this->belongsTo(Technology::class, 'technology');
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function grade_item()
    {
        return $this->belongsTo(Grade::class, 'grade');
    }

    public function farmula_item()
    {
        return $this->belongsTo(Formula::class, 'farmula');
    }

    public function quality_item()
    {
        return $this->belongsTo(Quality::class, 'quality_id');
    }

    public function partnumber_item()
    {
        return $this->belongsTo(PartNumber::class, 'part_number_id');
    }

    public function unit_item()
    {
        return $this->belongsTo(Unit::class, 'unit');
    }

    public function services_item()
    {
        return $this->belongsTo(Services::class, 'services');
    }

    public function warrenty_item()
    {
        return $this->belongsTo(Warrenty::class, 'warrenty');
    }

    public function group_item()
    {
        return $this->belongsTo(Group::class, 'gorup');
    }

    public function made_in_item()
    {
        return $this->belongsTo(MadeIn::class, 'made_in');
    }

    public function level_item()
    {
        return $this->belongsTo(Level::class, 'level');
    }

    public function updated_by_user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function priceUpdatedBranch()
    {
        return $this->belongsTo(Branch::class, 'price_updated_branch_id');
    }

    /**
     * Get item name - use short_disc, pro_dis, or partnumber name.
     * Setter allows storing name when table has name column (e.g. temporary products).
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! empty($this->short_disc)) {
                    return $this->short_disc;
                }
                if (! empty($this->pro_dis)) {
                    return $this->pro_dis;
                }
                if ($this->relationLoaded('partnumber_item') && $this->partnumber_item) {
                    return $this->partnumber_item->name ?? $this->bar_code;
                }

                return $this->bar_code ?? '';
            },
            set: fn ($value) => $value,
        );
    }
}
