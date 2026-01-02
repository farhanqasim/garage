<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Cca;
use App\Models\EngineCc;
use App\Models\Formula;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Level;
use App\Models\MadeIn;
use App\Models\Minuspool;
use App\Models\PartNumber;
use App\Models\Product;
use App\Models\Quality;
use App\Models\Services;
use App\Models\Technology;
use App\Models\VehicalType;
use App\Models\Volt;
use App\Models\Warrenty;
use App\Models\Year;
use Illuminate\Http\Request;

class AddInputController extends Controller
{

    public function post_volts(Request $request)
    {
        $volts = Volt::create(
            ['name' => $request->name]
        );
        return response()->json([
            'success' => true,
            'id' => $volts->id,
            'name' => $volts->name
        ]);
    }


    public function show_volt($id)
    {
        return response()->json(Volt::findOrFail($id));
    }


    public function update_volt(Request $request, $id)
    {
        $Volt = Volt::findOrFail($id);
        $Volt->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'id' => $Volt->id,
            'name' => $Volt->name,
            'message' => "Volt Update Successfully"
        ]);
    }

    public function destory_volt($id)
    {
        Volt::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Volt deleted Successfully"
        ]);
    }





    public function post_cca(Request $request)
    {

        $cca = Cca::create(
            ['name' => $request->name]
        );
        return response()->json([
            'success' => true,
            'id' => $cca->id,
            'name' => $cca->name
        ]);
    }
    public function show_cca($id)
    {
        return response()->json(Cca::findOrFail($id));
    }
    public function update_cca(Request $request, $id)
    {
        $Cca = Cca::findOrFail($id);
        $Cca->update(['name' => $request->name]);
        return response()->json([
            'success' => true,
            'id' => $Cca->id,
            'name' => $Cca->name,
            'message' => "Cca Update Successfully"
        ]);
    }
    public function destory_cca($id)
    {
        Cca::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Cca deleted Successfully"
        ]);
    }

    // Mins Pool
    public function post_minuspool(Request $request)
    {

        $minuspool = Minuspool::create(
            ['name' => $request->name]
        );
        return response()->json([
            'success' => true,
            'id' => $minuspool->id,
            'name' => $minuspool->name
        ]);
    }

    public function show_minuspool($id)
    {
        return response()->json(Minuspool::findOrFail($id));
    }


    public function update_minuspool(Request $request, $id)
    {
        $minuspool = Minuspool::findOrFail($id);
        $minuspool->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'id' => $minuspool->id,
            'name' => $minuspool->name,
            'message' => "Minuspool Update Successfully"
        ]);
    }

    public function destory_minuspool($id)
    {
        Minuspool::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Minuspool deleted Successfully"
        ]);
    }



    public function post_technology(Request $request)
    {

        $technology = Technology::create(
            ['name' => $request->name]
        );
        return response()->json([
            'success' => true,
            'id' => $technology->id,
            'name' => $technology->name
        ]);
    }

    public function show_technology($id)
    {
        return response()->json(Technology::findOrFail($id));
    }


    public function update_technology(Request $request, $id)
    {
        $technology = Technology::findOrFail($id);
        $technology->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'id' => $technology->id,
            'name' => $technology->name,
            'message' => "Technology Update Successfully"
        ]);
    }

    public function destory_technology($id)
    {
        Technology::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Technology deleted Successfully"
        ]);
    }



    public function post_grade(Request $request)
    {

        $grade = Grade::create(
            ['name' => $request->name]
        );
        return response()->json([
            'success' => true,
            'id' => $grade->id,
            'name' => $grade->name
        ]);
    }

    public function show_grade($id)
    {
        return response()->json(Grade::findOrFail($id));
    }


    public function update_grade(Request $request, $id)
    {
        $grade = Grade::findOrFail($id);
        $grade->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'id' => $grade->id,
            'name' => $grade->name,
            'message' => "Grade Update Successfully"
        ]);
    }

    public function destory_grade($id)
    {
        Grade::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Grade deleted Successfully"
        ]);
    }






    public function post_brand_item(Request $request)
    {
        $imagepath = null;
        if ($request->hasFile('image')) {
            $imagePath = saveSingleFile($request->file('image'), 'brands');
        }
        if ($imagepath) {
            $brand = Brand::create(
                [
                    'name' => $request->name,
                    'image' => $imagePath,
                ]
            );
            return response()->json([
                'success' => true,
                'id' => $brand->id,
                'name' => $brand->name,
                'image' => $brand->image,
            ]);
        } else {
            $brand = Brand::create(
                [
                    'name' => $request->name,
                ]
            );
            return response()->json(['success' => true, 'name' => $brand->name]);
        }
    }


    public function post_formulas(Request $request)
    {
        $formula = Formula::create(
            ['name' => $request->name]
        );
        return response()->json([
            'success' => true,
            'id' => $formula->id,
            'name' => $formula->name
        ]);
    }



    public function post_product(Request $request)
    {
        // Check if product already exists
        $exist = Product::where('name', $request->name)->first();

        if ($exist) {
            return response()->json([
                'success' => false,
                'message' => 'Product is already found, please choose a different name'
            ]);
        }

        // Create new product
        $product = Product::create([
            'name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'id' => $product->id,
            'name' => $product->name,
            'message' => 'Product added successfully'
        ]);
    }


    public function show_product($id)
    {
        return response()->json(Product::findOrFail($id));
    }


    public function update_product(Request $request, $id)
    {
        $Product = Product::findOrFail($id);
        $Product->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'id' => $Product->id,
            'name' => $Product->name,
            'message' => "Product Update Successfully"
        ]);
    }

    public function destory_product($id)
    {
        Product::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Product deleted Successfully"
        ]);
    }



    public function post_qualities(Request $request)
    {
        $quality = Quality::create(
            ['name' => $request->name]
        );
        return response()->json([
            'success' => true,
            'id' => $quality->id,
            'name' => $quality->name
        ]);
    }

    public function show_quality($id)
    {
        return response()->json(Quality::findOrFail($id));
    }


    public function update_quality(Request $request, $id)
    {
        $quality = Quality::findOrFail($id);
        $quality->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'id' => $quality->id,
            'name' => $quality->name,
            'message' => "Quality Update Successfully"
        ]);
    }

    public function destory_quality($id)
    {
        Quality::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Quality deleted Successfully"
        ]);
    }



    public function post_part_number(Request $request)
    {
        $part_number = PartNumber::create(
            ['name' => $request->name]
        );
        return response()->json([
            'success' => true,
            'id' => $part_number->id,
            'name' => $part_number->name
        ]);
    }


        public function show_partnumber($id)
    {
        return response()->json(PartNumber::findOrFail($id));
    }


    public function update_partnumber(Request $request, $id)
    {
        $part_number = PartNumber::findOrFail($id);
        $part_number->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'id' => $part_number->id,
            'name' => $part_number->name,
            'message' => "Part Number Update Successfully"
        ]);
    }

    public function destory_partnumber($id)
    {
        PartNumber::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Part Number deleted Successfully"
        ]);
    }


    public function post_engine_cc(Request $request)
    {
        $enginecc = EngineCc::create(
            ['name' => $request->name]
        );
        return response()->json([
            'success' => true,
            'id' => $enginecc->id,
            'name' => $enginecc->name
        ]);
    }


        public function show_engine_cc($id)
        {
            return response()->json(EngineCc::findOrFail($id));
        }


    public function update_engine_cc(Request $request, $id)
    {
        $EngineCc = EngineCc::findOrFail($id);
        $EngineCc->update(['name' => $request->name]);
        return response()->json([
            'success' => true,
            'id' => $EngineCc->id,
            'name' => $EngineCc->name,
            'message' => "EngineCc Update Successfully"
        ]);
    }

    public function destory_engine_cc($id)
    {
        EngineCc::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "EngineCc deleted Successfully"
        ]);
    }


    public function all_vehicals_types()
    {
        $all_vehicals = VehicalType::paginate(10);
        return view('admin.vehical.index', compact('all_vehicals'));
    }

        // Helper function to check if two year ranges overlap
        private function rangesOverlap($range1, $range2)
        {
            $from1 = (int) $range1['from'];
            $to1 = (int) $range1['to'];
            $from2 = (int) $range2['from'];
            $to2 = (int) $range2['to'];
            
            // Two ranges overlap if: range1 starts before range2 ends AND range1 ends after range2 starts
            return $from1 <= $to2 && $to1 >= $from2;
        }

        // Helper function to check if a year is covered by any range
        private function yearCoveredByRanges($year, $ranges)
        {
            $yearInt = (int) $year;
            foreach ($ranges as $range) {
                $from = (int) $range['from'];
                $to = (int) $range['to'];
                if ($yearInt >= $from && $yearInt <= $to) {
                    return $range;
                }
            }
            return false;
        }

        public function post_product_vehical(Request $request)
        {
            $request->validate([
                'v_part_number_id' => 'required|exists:part_numbers,id',
                'car_manufacturer' => 'nullable|exists:car_manufacturers,id',
                'car_model_name' => 'nullable|exists:car_models,id',
                'engine_cc' => 'nullable|exists:engine_ccs,id',
                'car_manufactured_country' => 'nullable|exists:car_countries,id',
                'year_from' => 'nullable|array',
                'year_from.*' => 'nullable|string',
                'year_to' => 'nullable|array',
                'year_to.*' => 'nullable|string',
            ], [
                'v_part_number_id.required' => 'Please select part number first.',
                'v_part_number_id.exists' => 'The selected part number is invalid.',
            ]);

            $yearFroms = $request->get('year_from', []);
            $yearTos = $request->get('year_to', []);
            
            // Process year ranges
            $validRanges = [];
            for ($i = 0; $i < count($yearFroms); $i++) {
                $from = trim((string) ($yearFroms[$i] ?? ''));
                $to = trim((string) ($yearTos[$i] ?? ''));
                
                if ($from === '' && $to === '') {
                    continue; // Skip empty ranges
                }
                
                $fromInt = $from ? (int) $from : null;
                $toInt = $to ? (int) $to : null;
                
                // Validate year range
                if ($fromInt && ($fromInt < 1900 || $fromInt > 2100)) {
                    continue;
                }
                if ($toInt && ($toInt < 1900 || $toInt > 2100)) {
                    continue;
                }
                
                // If only one year is provided, use it for both
                if ($fromInt && !$toInt) {
                    $toInt = $fromInt;
                } elseif (!$fromInt && $toInt) {
                    $fromInt = $toInt;
                }
                
                // Ensure from <= to
                if ($fromInt && $toInt && $fromInt > $toInt) {
                    $temp = $fromInt;
                    $fromInt = $toInt;
                    $toInt = $temp;
                }
                
                if ($fromInt && $toInt) {
                    $validRanges[] = [
                        'from' => (string) $fromInt,
                        'to' => (string) $toInt
                    ];
                }
            }

            $hasAnyData = !empty($validRanges) ||
                $request->filled('v_part_number_id') ||
                $request->filled('car_manufacturer') ||
                $request->filled('car_model_name') ||
                $request->filled('engine_cc') ||
                $request->filled('car_manufactured_country');

            if (!$hasAnyData) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one field is required to save a vehicle configuration.',
                ], 422);
            }

            // Check for overlaps within the input ranges
            $overlapErrors = [];
            for ($i = 0; $i < count($validRanges); $i++) {
                for ($j = $i + 1; $j < count($validRanges); $j++) {
                    if ($this->rangesOverlap($validRanges[$i], $validRanges[$j])) {
                        $range1Str = $validRanges[$i]['from'] . ($validRanges[$i]['from'] != $validRanges[$i]['to'] ? '-' . $validRanges[$i]['to'] : '');
                        $range2Str = $validRanges[$j]['from'] . ($validRanges[$j]['from'] != $validRanges[$j]['to'] ? '-' . $validRanges[$j]['to'] : '');
                        $overlapErrors[] = "Ranges $range1Str and $range2Str overlap.";
                    }
                }
            }

            if (!empty($overlapErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'YEAR OVERLAP ERROR',
                    'errors' => $overlapErrors,
                ], 422);
            }

            $commonFields = [
                'v_part_number_id' => $request->v_part_number_id,
                'car_manufacturer' => $request->car_manufacturer,
                'car_model_name' => $request->car_model_name,
                'engine_cc' => $request->engine_cc,
                'car_manufactured_country' => $request->car_manufactured_country,
                'status' => 'active',
            ];

            // Get existing ranges for this vehicle config to check overlaps
            $existingRanges = VehicalType::where($commonFields)
                ->select('year_from', 'year_to')
                ->get()
                ->map(function($item) {
                    return ['from' => $item->year_from, 'to' => $item->year_to];
                })
                ->toArray();

            $savedVehicles = [];
            $duplicateRanges = [];
            $overlapWithExisting = [];

            // Save one record per year range
            foreach ($validRanges as $range) {
                // Check if exact duplicate exists
                $exists = VehicalType::where(array_merge($commonFields, [
                    'year_from' => $range['from'],
                    'year_to' => $range['to']
                ]))->exists();

                if ($exists) {
                    $rangeStr = $range['from'] . ($range['from'] != $range['to'] ? '-' . $range['to'] : '');
                    $duplicateRanges[] = $rangeStr;
                    continue;
                }

                // Check overlap with existing ranges
                $hasOverlap = false;
                foreach ($existingRanges as $existing) {
                    if ($this->rangesOverlap($range, $existing)) {
                        $existingStr = $existing['from'] . ($existing['from'] != $existing['to'] ? '-' . $existing['to'] : '');
                        $newRangeStr = $range['from'] . ($range['from'] != $range['to'] ? '-' . $range['to'] : '');
                        
                        // Check if specific years are covered
                        $fromInt = (int) $range['from'];
                        $toInt = (int) $range['to'];
                        $existingFrom = (int) $existing['from'];
                        $existingTo = (int) $existing['to'];
                        
                        if ($fromInt >= $existingFrom && $fromInt <= $existingTo) {
                            $overlapWithExisting[] = "Year {$range['from']} is already covered in range {$existingStr}.";
                        } elseif ($toInt >= $existingFrom && $toInt <= $existingTo) {
                            $overlapWithExisting[] = "Year {$range['to']} is already covered in range {$existingStr}.";
                        } else {
                            $overlapWithExisting[] = "Range $newRangeStr overlaps with existing range $existingStr.";
                        }
                        $hasOverlap = true;
                        break;
                    }
                }

                if ($hasOverlap) {
                    continue;
                }

                $savedVehicles[] = VehicalType::create(array_merge($commonFields, [
                    'year_from' => $range['from'],
                    'year_to' => $range['to']
                ]));
            }

            if (!empty($overlapWithExisting)) {
                return response()->json([
                    'success' => false,
                    'message' => 'YEAR OVERLAP ERROR',
                    'errors' => $overlapWithExisting,
                    'duplicate_years' => $duplicateRanges,
                ], 422);
            }

            if (empty($savedVehicles) && !empty($duplicateRanges)) {
                return response()->json([
                    'success' => false,
                    'message' => 'All year ranges already exist for this vehicle configuration.',
                    'duplicate_years' => $duplicateRanges,
                ], 422);
            }

            $savedIds = collect($savedVehicles)->pluck('id')->toArray();

            // Get all vehicles for this config (including newly saved ones)
            $allVehicleIds = array_merge(
                VehicalType::where($commonFields)->pluck('id')->toArray(),
                $savedIds
            );

            return response()->json([
                'success' => true,
                'message' => 'Vehicle saved successfully!',
                'duplicate_years' => $duplicateRanges,
                'vehicles' => VehicalType::with([
                    'manutacturer_vehical',
                    'model_vehical',
                    'engine_vehical',
                    'country_vehical',
                    'vehical_part_number'
                ])->whereIn('id', array_unique($allVehicleIds))->get()
            ]);
        }


    // In AddInputController.php - Add this method for fetching group data
    public function get_vehical_group($id)
    {
        $vehical = VehicalType::findOrFail($id);

        $groupQuery = VehicalType::where([
            'v_part_number_id' => $vehical->v_part_number_id,
            'car_manufacturer' => $vehical->car_manufacturer,
            'car_model_name' => $vehical->car_model_name,
            'engine_cc' => $vehical->engine_cc,
            'car_manufactured_country' => $vehical->car_manufactured_country,
        ]);

        // Get year ranges and format them
        $yearRanges = $groupQuery->select('year_from', 'year_to')->get();
        $years = [];
        foreach ($yearRanges as $range) {
            if ($range->year_from && $range->year_to) {
                if ($range->year_from == $range->year_to) {
                    $years[] = $range->year_from;
                } else {
                    $years[] = $range->year_from . '-' . $range->year_to;
                }
            }
        }
        
        $group_ids = $groupQuery->pluck('id')->toArray();

        return response()->json([
            'part_id' => $vehical->v_part_number_id,
            'manufacturer_id' => $vehical->car_manufacturer,
            'model_id' => $vehical->car_model_name,
            'engine_id' => $vehical->engine_cc,
            'country_id' => $vehical->car_manufactured_country,
            'years' => $years,
            'group_ids' => $group_ids
        ]);
    }

    // In AddInputController.php - Add this method for updating (separate from post)
    public function update_product_vehical(Request $request)
    {
        $request->validate([
            'v_part_number_id' => 'required',
            'car_manufacturer' => 'required',
            'car_model_name' => 'required',
            'engine_cc' => 'required',
            'car_manufactured_country' => 'required',
            'year_from' => 'nullable|array',
            'year_from.*' => 'nullable|string',
            'year_to' => 'nullable|array',
            'year_to.*' => 'nullable|string',
            'old_v_part_number_id' => 'required',
            'old_car_manufacturer' => 'required',
            'old_car_model_name' => 'required',
            'old_engine_cc' => 'required',
            'old_car_manufactured_country' => 'required',
        ]);

        $old_part = $request->old_v_part_number_id;
        $old_manuf = $request->old_car_manufacturer;
        $old_model = $request->old_car_model_name;
        $old_engine = $request->old_engine_cc;
        $old_country = $request->old_car_manufactured_country;

        // Delete old group records
        $deleted = VehicalType::where([
            'v_part_number_id' => $old_part,
            'car_manufacturer' => $old_manuf,
            'car_model_name' => $old_model,
            'engine_cc' => $old_engine,
            'car_manufactured_country' => $old_country,
            'status' => 'active',
        ])->get();

        $deleted_ids = $deleted->pluck('id')->toArray();
        $deleted->each->delete();

        $yearFroms = $request->get('year_from', []);
        $yearTos = $request->get('year_to', []);
        
        // Process year ranges
        $validRanges = [];
        for ($i = 0; $i < count($yearFroms); $i++) {
            $from = trim((string) ($yearFroms[$i] ?? ''));
            $to = trim((string) ($yearTos[$i] ?? ''));
            
            if ($from === '' && $to === '') {
                continue;
            }

            $fromInt = $from ? (int) $from : null;
            $toInt = $to ? (int) $to : null;
            
            if ($fromInt && ($fromInt < 1900 || $fromInt > 2100)) {
                continue;
            }
            if ($toInt && ($toInt < 1900 || $toInt > 2100)) {
                continue;
            }
            
            if ($fromInt && !$toInt) {
                $toInt = $fromInt;
            } elseif (!$fromInt && $toInt) {
                $fromInt = $toInt;
            }
            
            if ($fromInt && $toInt && $fromInt > $toInt) {
                $temp = $fromInt;
                $fromInt = $toInt;
                $toInt = $temp;
            }
            
            if ($fromInt && $toInt) {
                $validRanges[] = [
                    'from' => (string) $fromInt,
                    'to' => (string) $toInt
                ];
            }
        }

        if (empty($validRanges)) {
            return response()->json([
                'success' => false,
                'message' => 'At least one valid year range is required.',
            ], 422);
        }

        // Check for overlaps within the input ranges
        $overlapErrors = [];
        for ($i = 0; $i < count($validRanges); $i++) {
            for ($j = $i + 1; $j < count($validRanges); $j++) {
                if ($this->rangesOverlap($validRanges[$i], $validRanges[$j])) {
                    $range1Str = $validRanges[$i]['from'] . ($validRanges[$i]['from'] != $validRanges[$i]['to'] ? '-' . $validRanges[$i]['to'] : '');
                    $range2Str = $validRanges[$j]['from'] . ($validRanges[$j]['from'] != $validRanges[$j]['to'] ? '-' . $validRanges[$j]['to'] : '');
                    $overlapErrors[] = "Ranges $range1Str and $range2Str overlap.";
                }
            }
        }

        if (!empty($overlapErrors)) {
            return response()->json([
                'success' => false,
                'message' => 'YEAR OVERLAP ERROR',
                'errors' => $overlapErrors,
            ], 422);
        }

        $commonFields = [
            'v_part_number_id' => $request->v_part_number_id,
            'car_manufacturer' => $request->car_manufacturer,
            'car_model_name' => $request->car_model_name,
            'engine_cc' => $request->engine_cc,
            'car_manufactured_country' => $request->car_manufactured_country,
            'status' => 'active',
        ];

        $savedVehicles = [];
        $duplicateRanges = [];

        // Save one record per year range
        foreach ($validRanges as $range) {
            $exists = VehicalType::where(array_merge($commonFields, [
                'year_from' => $range['from'],
                'year_to' => $range['to']
            ]))->exists();

            if ($exists) {
                $duplicateRanges[] = $range['from'] . ($range['from'] != $range['to'] ? '-' . $range['to'] : '');
                continue;
            }

            $savedVehicles[] = VehicalType::create(array_merge($commonFields, [
                'year_from' => $range['from'],
                'year_to' => $range['to']
            ]));
        }

        if (empty($savedVehicles) && !empty($duplicateRanges)) {
            return response()->json([
                'success' => false,
                'message' => 'All year ranges already exist for this vehicle configuration.',
                'deleted_ids' => $deleted_ids,
            ], 422);
        }

        $savedIds = collect($savedVehicles)->pluck('id')->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle updated successfully!',
            'duplicate_years' => $duplicateRanges,
            'vehicles' => VehicalType::with([
                'manutacturer_vehical',
                'model_vehical',
                'engine_vehical',
                'country_vehical',
                'vehical_part_number'
            ])->whereIn('id', $savedIds)->get(),
            'deleted_ids' => $deleted_ids
        ]);
    }
    public function searchByPartNumber(Request $request)
    {
        $vehicles = VehicalType::with([
            'manutacturer_vehical',
            'model_vehical',
            'engine_vehical',
            'vehical_part_number',
            'country_vehical'
        ])
            ->where('v_part_number_id', $request->vehical_id)
            ->get();
        return $vehicles;
        return response()->json([
            'vehicles' => $vehicles
        ]);
    }


    public function updateVehicalInline(Request $request, $id)
    {
        $vehical = VehicalType::findOrFail($id);
        $vehical->update([$request->field => $request->value]);

        return response()->json(['success' => true]);
    }

    public function delete_vehical($id)
    {
        VehicalType::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }



    public function post_services(Request $request)
    {
        $enginecc = Services::create(
            ['name' => $request->name]
        );
        return response()->json([
            'success' => true,
            'id' => $enginecc->id,
            'name' => $enginecc->name
        ]);
    }

    public function show_service($id)
    {
        return response()->json(Services::findOrFail($id));
    }


    public function update_service(Request $request, $id)
    {
        $service = Services::findOrFail($id);
        $service->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'id' => $service->id,
            'name' => $service->name,
            'message' => "Service Update Successfully"
        ]);
    }

    public function destory_service($id)
    {
        Services::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Service deleted Successfully"
        ]);
    }




    public function post_warrenty(Request $request)
    {
        $warrenty = Warrenty::create(
            ['name' => $request->name]
        );
        return response()->json([
            'success' => true,
            'id' => $warrenty->id,
            'name' => $warrenty->name
        ]);
    }

    public function show_warrenty($id)
    {
        return response()->json(Warrenty::findOrFail($id));
    }


    public function update_warrenty(Request $request, $id)
    {
        $Warrenty = Warrenty::findOrFail($id);
        $Warrenty->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'id' => $Warrenty->id,
            'name' => $Warrenty->name,
            'message' => "Warrenty Update Successfully"
        ]);
    }

    public function destory_warrenty($id)
    {
        Warrenty::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Warrenty deleted Successfully"
        ]);
    }




    public function post_show($id)
    {
        return response()->json(Group::findOrFail($id));
    }

    public function post_groups(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        $existingGroup = Group::where('name', $request->name)->first();
        if ($existingGroup) {
            return response()->json([
                'success' => false,
                'message' => 'Group already exists'
            ], 409);
        }
        $group = Group::create([
            'name' => $request->name
        ]);
        return response()->json([
            'success' => true,
            'id' => $group->id,
            'name' => $group->name,
            'message' => 'Group created successfully'
        ], 201);
    }

    public function post_update(Request $request, $id)
    {
        $group = Group::findOrFail($id);
        $group->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'id' => $group->id,
            'name' => $group->name,
            'message' => "Group Update Successfully"
        ]);
    }

    public function post_destroy($id)
    {
        Group::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Group deleted Successfully"
        ]);
    }


    public function post_made_ins(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $existingGroup = MadeIn::where('name', $request->name)->first();
        if ($existingGroup) {
            return response()->json([
                'success' => false,
                'message' => 'Made In already exists'
            ], 409);
        }
        $group = MadeIn::create([
            'name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'id' => $group->id,
            'name' => $group->name,
            'message' => 'Made In created successfully'
        ], 201);
    }


    public function show_madeins($id)
    {
        return response()->json(MadeIn::findOrFail($id));
    }


    public function update_madeins(Request $request, $id)
    {
        $MadeIn = MadeIn::findOrFail($id);
        $MadeIn->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'id' => $MadeIn->id,
            'name' => $MadeIn->name,
            'message' => "MadeIn Update Successfully"
        ]);
    }

    public function destory_madeins($id)
    {
        MadeIn::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "MadeIn deleted Successfully"
        ]);
    }

    public function post_levels(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $existingGroup = Level::where('name', $request->name)->first();
        if ($existingGroup) {
            return response()->json([
                'success' => false,
                'message' => 'Level already exists'
            ], 409);
        }
        $group = Level::create([
            'name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'id' => $group->id,
            'name' => $group->name,
            'message' => 'Level created successfully'
        ], 201);
    }

    public function show_level($id)
    {
        return response()->json(Level::findOrFail($id));
    }


    public function update_level(Request $request, $id)
    {
        $level = Level::findOrFail($id);
        $level->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'id' => $level->id,
            'name' => $level->name,
            'message' => "level Update Successfully"
        ]);
    }

    public function destory_level($id)
    {
        Level::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "level deleted Successfully"
        ]);
    }
}
