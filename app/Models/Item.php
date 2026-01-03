<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
       use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id' ,'vehical_id', 'total_price',  'price_per_unit', 'sale_price', 'on_hand',  'is_active', 'auto_deactive', 'is_dead','barcode_image',
        'bar_code', 'p_id', 'mileage', 'type', 'plat_id', 'amphors',
        'lineitems', 'company_id', 'category_id', 'subcategory_id',
         'p_brochure', 'image', 'images',
        'car_company', 'volt', 'cca', 'minus_pool_direction',
        'tecnology', 'grade', 'farmula', 'serial_number', 'battery_size',
        'bussiness_location', 'quality_id', 'part_number_id', 'l_stock',
        'm_stock', 'unit', 'packing', 'scale', 'filling',
        'weight_for_delivery', 'packing_purchase_rate','technology',
        'total_sale_price','sale_price_per_base','services','warrenty','gorup','made_in','level',
        'update_date', 'rack', 'supplier', 'pro_dis','short_disc',
        'updated_by', 'last_updated_at'
    ];
        protected $dates = ['deleted_at'];

    protected $casts = [
        'images'                => 'array',
        'is_active'             => 'boolean',
        'auto_deactive'         => 'boolean',
        'is_dead'               => 'boolean',
        'car_manufacture_year'  => 'date',
        'update_date'           => 'date',
        'last_updated_at'       => 'datetime',
        'filling'               => 'decimal:2',
        'weight_for_delivery'   => 'decimal:2',
        'packing_purchase_rate' => 'decimal:2',
        'min_qty'               => 'integer',
        'max_qty'               => 'integer',
    ];

    /* -------------------------------------------------
     *  THUMBNAIL (single image)
     * ------------------------------------------------- */
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value
                ? asset($value)                                 // already full path from helper
                : asset('images/default-item.jpg'),

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
                if (!is_array($decoded)) {
                    \Log::warning('Invalid images JSON for Item ID: ' . $this->id, ['raw' => $value]);
                    return [];
                }

                // Return full URLs
                return array_map(fn($path) => asset($path), $decoded);
            },

            // SET – accept UploadedFile[] or array of paths
            set: function ($value) {
                // If an array of UploadedFile objects
                if (is_array($value) && !empty($value) && $value[0] instanceof \Illuminate\Http\UploadedFile) {
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
            return $this->belongsTo(VehicalType::class,'vehical_id');
        }

        public function subcategory()
        {
            return $this->belongsTo(Category::class, 'subcategory_id');
        }


          public function product_item()
            {
             return $this->belongsTo(Product::class,'p_id');
            }

            public function mileage_item()
            {
                return $this->belongsTo(Mileage::class,'mileage');
            }


                public function plate_item()
                {
                    return $this->belongsTo(Platos::class,'plat_id');
                }


                public function amphors_item()
                {
                    return $this->belongsTo(Amphor::class,'amphors');
                }

                public function lineitems_item()
                {
                    return $this->belongsTo(LineItem::class,'lineitems');
                }


                public function company_item()
                {
                    return $this->belongsTo(Company::class,'company_id');
                }

                public function volt_item()
                {
                    return $this->belongsTo(Volt::class,'volt');
                }

                public function cca_item()
                {
                    return $this->belongsTo(Cca::class,'cca');
                }

                public function minus_pool_item()
                {
                    return $this->belongsTo(Minuspool::class,'minus_pool_direction');
                }

                public function technology_item()
                {
                    return $this->belongsTo(Technology::class,'tecnology');
                }

                public function grade_item()
                {
                    return $this->belongsTo(Grade::class,'grade');
                }


                    public function farmula_item()
                    {
                    return $this->belongsTo(Formula::class,'farmula');
                    }

                    public function quality_item()
                    {
                    return $this->belongsTo(Quality::class,'quality_id');
                    }

                    public function partnumber_item()
                    {
                    return $this->belongsTo(PartNumber::class,'part_number_id');
                    }

                  public function unit_item()
                    {
                    return $this->belongsTo(Unit::class,'unit');
                    }


                    public function services_item()
                    {
                     return $this->belongsTo(Services::class,'services');
                    }

                    public function warrenty_item()
                    {
                     return $this->belongsTo(Warrenty::class,'warrenty');
                    }

                 public function group_item()
                    {
                     return $this->belongsTo(Group::class,'gorup');
                    }

                    public function made_in_item()
                    {
                     return $this->belongsTo(MadeIn::class,'made_in');
                    }


                    public function level_item()
                    {
                     return $this->belongsTo(Level::class,'level');
                    }

                    public function updated_by_user()
                    {
                     return $this->belongsTo(User::class,'updated_by');
                    }

    /**
     * Get item name - use short_disc, pro_dis, or partnumber name
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!empty($this->short_disc)) {
                    return $this->short_disc;
                }
                if (!empty($this->pro_dis)) {
                    return $this->pro_dis;
                }
                if ($this->relationLoaded('partnumber_item') && $this->partnumber_item) {
                    return $this->partnumber_item->name ?? $this->bar_code;
                }
                return $this->bar_code;
            }
        );
    }

}
