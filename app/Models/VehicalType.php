<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class VehicalType extends Model
{
    use HasFactory;
    protected $fillable = [
        'v_part_number_id',
        'car_manufacturer',
        'car_model_name',
        'engine_cc',
        'car_manufactured_country',
        'year_from',
        'year_to',
        'status',
    ];
    protected $casts = [
        'year_from' => 'string',
        'year_to' => 'string',
    ];
    public function item_vehical()
    {
        return $this->hasMany(Item::class, 'vehical_id');
    }


    public function manutacturer_vehical(){
     return $this->belongsTo(CarManufacturer::class,'car_manufacturer');
    }

   public function model_vehical(){
     return $this->belongsTo(CarModel::class,'car_model_name');
    }

      public function engine_vehical(){
        return $this->belongsTo(EngineCc::class,'engine_cc');
        }


    public function country_vehical(){
     return $this->belongsTo(CarCountry::class,'car_manufactured_country');
    }


    public function vehical_part_number(){
     return $this->belongsTo(PartNumber::class,'v_part_number_id');
    }
}
