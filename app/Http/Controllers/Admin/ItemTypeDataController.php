<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BatterySize;
use App\Models\Category;
use App\Models\Cca;
use App\Models\Company;
use App\Models\Grade;
use App\Models\Mileage;
use App\Models\Minuspool;
use App\Models\PartNumber;
use App\Models\Platos;
use App\Models\PoleThickness;
use App\Models\PoolDirection;
use App\Models\Product;
use App\Models\Quality;
use App\Models\Services;
use App\Models\Technology;
use App\Models\Unit;
use App\Models\Volt;
use Illuminate\Http\Request;

class ItemTypeDataController extends Controller
{
    protected $validTypes = ['parts', 'battery', 'scrap', 'filters', 'breakpad', 'oil', 'services'];

    /**
     * Type-to-entities mapping: which dropdowns/data each item type uses
     */
    protected function getTypeEntities(): array
    {
        return [
            'parts' => [
                ['key' => 'part_numbers', 'label' => 'Part Numbers', 'model' => PartNumber::class, 'nameCol' => 'name'],
                ['key' => 'categories', 'label' => 'Categories', 'model' => Category::class, 'nameCol' => 'name', 'filter' => ['parent_id' => null]],
                ['key' => 'qualities', 'label' => 'Qualities', 'model' => Quality::class, 'nameCol' => 'name'],
                ['key' => 'companies', 'label' => 'Companies', 'model' => Company::class, 'nameCol' => 'name'],
                ['key' => 'products', 'label' => 'Products', 'model' => Product::class, 'nameCol' => 'name'],
            ],
            'battery' => [
                ['key' => 'volts', 'label' => 'Volts', 'model' => Volt::class, 'nameCol' => 'name'],
                ['key' => 'ccas', 'label' => 'CCAs', 'model' => Cca::class, 'nameCol' => 'name'],
                ['key' => 'minuspool', 'label' => 'Minus Pool', 'model' => Minuspool::class, 'nameCol' => 'name'],
                ['key' => 'pole_thickness', 'label' => 'Pole Thickness', 'model' => PoleThickness::class, 'nameCol' => 'name'],
                ['key' => 'pool_direction', 'label' => 'Pool Direction', 'model' => PoolDirection::class, 'nameCol' => 'name'],
                ['key' => 'technologies', 'label' => 'Technologies', 'model' => Technology::class, 'nameCol' => 'name'],
                ['key' => 'battery_sizes', 'label' => 'Battery Sizes', 'model' => BatterySize::class, 'nameCol' => 'name'],
                ['key' => 'grades', 'label' => 'Grades', 'model' => Grade::class, 'nameCol' => 'name'],
                ['key' => 'plates', 'label' => 'Plates', 'model' => Platos::class, 'nameCol' => 'name'],
            ],
            'scrap' => [
                ['key' => 'plates', 'label' => 'Plates', 'model' => Platos::class, 'nameCol' => 'name'],
            ],
            'filters' => [
                ['key' => 'qualities', 'label' => 'Qualities', 'model' => Quality::class, 'nameCol' => 'name'],
                ['key' => 'part_numbers', 'label' => 'Part Numbers', 'model' => PartNumber::class, 'nameCol' => 'name'],
                ['key' => 'categories', 'label' => 'Categories', 'model' => Category::class, 'nameCol' => 'name', 'filter' => ['parent_id' => null]],
                ['key' => 'companies', 'label' => 'Companies', 'model' => Company::class, 'nameCol' => 'name'],
            ],
            'breakpad' => [
                ['key' => 'qualities', 'label' => 'Qualities', 'model' => Quality::class, 'nameCol' => 'name'],
                ['key' => 'part_numbers', 'label' => 'Part Numbers', 'model' => PartNumber::class, 'nameCol' => 'name'],
                ['key' => 'categories', 'label' => 'Categories', 'model' => Category::class, 'nameCol' => 'name', 'filter' => ['parent_id' => null]],
                ['key' => 'companies', 'label' => 'Companies', 'model' => Company::class, 'nameCol' => 'name'],
            ],
            'oil' => [
                ['key' => 'mileages', 'label' => 'Mileage', 'model' => Mileage::class, 'nameCol' => 'name'],
                ['key' => 'units', 'label' => 'Units', 'model' => Unit::class, 'nameCol' => 'name'],
                ['key' => 'companies', 'label' => 'Companies', 'model' => Company::class, 'nameCol' => 'name'],
                ['key' => 'categories', 'label' => 'Categories', 'model' => Category::class, 'nameCol' => 'name', 'filter' => ['parent_id' => null]],
            ],
            'services' => [
                ['key' => 'services', 'label' => 'Services', 'model' => Services::class, 'nameCol' => 'name'],
            ],
        ];
    }

    protected function getEntityRoutes(string $key): array
    {
        $routes = [
            'part_numbers' => ['post' => 'post.partnumber', 'show' => 'show.partnumber', 'update' => 'update.partnumber', 'delete' => 'destory.partnumber'],
            'categories' => ['post' => 'post.item.category', 'show' => 'show.category', 'update' => 'update.category', 'delete' => 'destory.category'],
            'qualities' => ['post' => 'post.qualities', 'show' => 'show.quality', 'update' => 'update.quality', 'delete' => 'destory.quality'],
            'companies' => ['post' => 'post.companies', 'show' => 'show.company', 'update' => 'update.company', 'delete' => 'destory.company'],
            'products' => ['post' => 'post.product', 'show' => 'show.product', 'update' => 'update.product', 'delete' => 'destory.product'],
            'volts' => ['post' => 'post.volts', 'show' => 'show.volt', 'update' => 'update.volt', 'delete' => 'destory.volt'],
            'ccas' => ['post' => 'post.cca', 'show' => 'show.cca', 'update' => 'update.cca', 'delete' => 'destory.cca'],
            'minuspool' => ['post' => 'post.minuspool', 'show' => 'show.minuspool', 'update' => 'update.minuspool', 'delete' => 'destory.minuspool'],
            'pole_thickness' => ['post' => 'post.polethickness', 'show' => 'show.polethickness', 'update' => 'update.polethickness', 'delete' => 'destory.polethickness'],
            'pool_direction' => ['post' => 'post.pooldirection', 'show' => 'show.pooldirection', 'update' => 'update.pooldirection', 'delete' => 'destory.pooldirection'],
            'technologies' => ['post' => 'post.technology', 'show' => 'show.technology', 'update' => 'update.technology', 'delete' => 'destory.technology'],
            'battery_sizes' => ['post' => 'post.battery.size', 'show' => 'show.battery.size', 'update' => 'update.battery.size', 'delete' => 'destory.battery.size'],
            'grades' => ['post' => 'post.grade', 'show' => 'show.grade', 'update' => 'update.grade', 'delete' => 'destory.grade'],
            'plates' => ['post' => 'post.platos', 'show' => 'show.plate', 'update' => 'update.plate', 'delete' => 'destory.plate'],
            'mileages' => ['post' => 'post.mileage', 'show' => 'show.mileage', 'update' => 'update.mileage', 'delete' => 'destory.mileage'],
            'units' => ['post' => 'post.units', 'show' => 'show.unit', 'update' => 'update.unit', 'delete' => 'destory.unit'],
            'services' => ['post' => 'post.services', 'show' => 'show.service', 'update' => 'update.service', 'delete' => 'destory.service'],
        ];
        return $routes[$key] ?? [];
    }

    public function index(Request $request, string $type)
    {
        $type = strtolower($type);
        if (!in_array($type, $this->validTypes)) {
            abort(404, 'Invalid item type.');
        }

        $typeEntities = $this->getTypeEntities();
        $entities = $typeEntities[$type] ?? [];

        $tables = [];
        foreach ($entities as $e) {
            $query = $e['model']::query();
            if (!empty($e['filter'])) {
                foreach ($e['filter'] as $col => $val) {
                    $query->where($col, $val);
                }
            }
            $data = $query->orderBy($e['nameCol'])->get();
            $routes = $this->getEntityRoutes($e['key']);
            $tables[] = [
                'key' => $e['key'],
                'label' => $e['label'],
                'data' => $data,
                'nameCol' => $e['nameCol'],
                'routes' => $routes,
            ];
        }

        $typeLabel = ucfirst($type);
        $allTypes = [
            'parts' => 'Parts',
            'battery' => 'Battery',
            'scrap' => 'Scrap',
            'filters' => 'Filters',
            'breakpad' => 'Break Pad',
            'oil' => 'Oil',
            'services' => 'Services',
        ];

        return view('admin.item-type-data.index', compact('type', 'typeLabel', 'tables', 'allTypes'));
    }
}
