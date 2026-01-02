<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Category;

class PosController extends Controller
{
    public function point_of_sale(Request $request){
        $query = Item::where('is_active', true)
            ->where('on_hand', '>', 0)
            ->with(['category', 'partnumber_item'])
            ->select('id', 'short_disc', 'bar_code', 'sale_price', 'price_per_unit', 'on_hand', 'image', 'category_id', 'part_number_id')
            ->orderBy('short_disc', 'asc');
        
        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('short_disc', 'like', '%' . $search . '%')
                  ->orWhere('bar_code', 'like', '%' . $search . '%')
                  ->orWhere('pro_dis', 'like', '%' . $search . '%');
            });
        }
        
        // Category filter
        if ($request->has('category') && !empty($request->category) && $request->category != 'all') {
            $query->where('category_id', $request->category);
        }
        
        $items = $query->paginate(50);
        
        // Get categories for filter
        $categories = Category::where('status', 'active')
            ->whereNull('parent_id')
            ->select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();
        
        return view('admin.pos.home', compact('items', 'categories'));
    }
    
    /**
     * AJAX search for products
     */
    public function search(Request $request)
    {
        $search = $request->get('search', '');
        
        $items = Item::where('is_active', true)
            ->where('on_hand', '>', 0)
            ->with('partnumber_item')
            ->where(function($q) use ($search) {
                $q->where('short_disc', 'like', '%' . $search . '%')
                  ->orWhere('bar_code', 'like', '%' . $search . '%')
                  ->orWhere('pro_dis', 'like', '%' . $search . '%');
            })
            ->select('id', 'short_disc', 'pro_dis', 'bar_code', 'sale_price', 'price_per_unit', 'on_hand', 'image', 'part_number_id')
            ->orderBy('short_disc', 'asc')
            ->limit(50)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name ?? $item->short_disc ?? $item->partnumber_item->name ?? $item->bar_code,
                    'bar_code' => $item->bar_code,
                    'price' => $item->sale_price ?? $item->price_per_unit ?? 0,
                    'stock' => $item->on_hand ?? 0,
                    'image' => $item->image
                ];
            });
        
        return response()->json($items);
    }
}
