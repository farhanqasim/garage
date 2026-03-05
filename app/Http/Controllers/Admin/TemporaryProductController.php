<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;

class TemporaryProductController extends Controller
{
    /**
     * List all temporary products (is_temporary = true).
     */
    public function index()
    {
        $items = Item::where('is_temporary', true)
            ->orderBy('updated_at', 'desc')
            ->paginate(20);
        return view('admin.purchases.temporary-products-index', compact('items'));
    }

    /**
     * Show edit form for a temporary product.
     */
    public function edit($id)
    {
        $item = Item::where('is_temporary', true)->findOrFail($id);
        $units = \App\Models\Unit::where('status', 'active')->orderBy('name')->get();
        return view('admin.purchases.temporary-products-edit', compact('item', 'units'));
    }

    /**
     * Update a temporary product. Image required only when creating; on update image is optional.
     */
    public function update(Request $request, $id)
    {
        $item = Item::where('is_temporary', true)->findOrFail($id);

        $rules = [
            'product_name' => 'required|string|max:255',
            'cost_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ];
        if ($request->hasFile('image')) {
            $rules['image'] = 'image|mimes:jpeg,png,jpg,gif,webp|max:5120';
        }
        $request->validate($rules);

        $name = trim($request->product_name);
        $item->pro_dis = $name;
        $item->short_disc = $name;
        $item->packing_purchase_rate = (float) $request->cost_price;
        $item->notes = $request->filled('notes') ? trim($request->notes) : null;
        if ($request->has('unit') && $request->unit !== '') {
            $item->unit = $request->unit;
        }

        if ($request->hasFile('image')) {
            if (function_exists('saveSingleFile')) {
                $item->image = saveSingleFile($request->file('image'), 'items');
            } else {
                $file = $request->file('image');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $file->move(public_path('items'), $filename);
                $item->image = 'items/' . $filename;
            }
        }
        $item->save();

        return redirect()->route('purchases.temporary.index')
            ->with('success', 'Temporary product updated successfully.');
    }

    /**
     * Convert temporary product to real product (set is_temporary = false).
     * Optionally redirect to full item edit for completing details.
     */
    public function convert(Request $request, $id)
    {
        $item = Item::where('is_temporary', true)->findOrFail($id);
        $item->is_temporary = false;
        $item->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Product converted to real product.', 'item_id' => $item->id]);
        }
        return redirect()->route('purchases.temporary.index')
            ->with('success', 'Temporary product converted to real product. You can now edit it from the main Items list.');
    }
}
