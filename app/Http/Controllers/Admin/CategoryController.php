<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function all_category()
    {
        $categories = Category::whereNull('parent_id')->paginate(10);

        return view('admin.category.category', compact('categories'));
    }

    public function post_category(Request $request)
    {
        $category = new Category;
        $category->name = $request->name;
        if ($request->hasFile('image')) {
            $category->image = saveSingleFile($request->file('image'), 'category');
        }
        // return  $category;
        $category->save();

        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function post_category_item(Request $request)
    {
        $name = trim((string) ($request->name ?? ''));
        if ($name === '') {
            return response()->json(['success' => false, 'message' => 'Category name is required.'], 422);
        }
        // Check by name only (case-insensitive) so we always catch duplicates regardless of type
        $existing = Category::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'id' => $existing->id,
                'name' => $existing->name,
                'image' => $existing->image,
                'type' => $existing->type,
                'scrap_measurement' => $existing->scrap_measurement,
                'scrap_weight_label' => $existing->scrap_weight_label,
                'scrap_count_label' => $existing->scrap_count_label,
                'already_exists' => true,
                'message' => 'This category already exists. It has been selected for you.',
            ]);
        }
        // Image is mandatory when creating a new category
        if (! $request->hasFile('image') || ! $request->file('image')->isValid()) {
            return response()->json(['success' => false, 'message' => 'Please attach an image.'], 422);
        }
        $imagepath = saveSingleFile($request->file('image'), 'category');
        $type = $request->type ?? null;
        // Scrap measurement only for Services category type; editable unit labels
        $scrapMeasurement = (strtolower((string) $type) === 'services' && $request->filled('scrap_measurement') && in_array($request->scrap_measurement, ['weight', 'count']))
            ? $request->scrap_measurement
            : null;
        $scrapWeightLabel = (strtolower((string) $type) === 'services' && $request->filled('scrap_weight_label'))
            ? trim((string) $request->scrap_weight_label)
            : null;
        $scrapCountLabel = (strtolower((string) $type) === 'services' && $request->filled('scrap_count_label'))
            ? trim((string) $request->scrap_count_label)
            : null;
        $category = Category::create([
            'name' => $name,
            'image' => $imagepath,
            'type' => $type,
            'scrap_measurement' => $scrapMeasurement,
            'scrap_weight_label' => $scrapWeightLabel,
            'scrap_count_label' => $scrapCountLabel,
        ]);

        return response()->json([
            'success' => true,
            'id' => $category->id,
            'name' => $category->name,
            'image' => $category->image,
            'type' => $category->type,
            'scrap_measurement' => $category->scrap_measurement,
            'scrap_weight_label' => $category->scrap_weight_label,
            'scrap_count_label' => $category->scrap_count_label,
        ]);
    }

    public function show_category($id)
    {
        return response()->json(
            Category::findOrFail($id)
        );
    }

    public function update_category(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $imagePath = $category->image;

        if ($request->hasFile('image')) {
            $imagePath = saveSingleFile($request->file('image'), 'category');
        }

        $newType = $request->type ?? $category->type;
        // Scrap measurement only for Services category type; clear for others; editable unit labels
        $scrapMeasurement = (strtolower((string) $newType) === 'services' && $request->has('scrap_measurement') && in_array($request->scrap_measurement, ['weight', 'count']))
            ? $request->scrap_measurement
            : null;
        $scrapWeightLabel = (strtolower((string) $newType) === 'services' && $request->has('scrap_weight_label'))
            ? trim((string) $request->scrap_weight_label)
            : null;
        $scrapCountLabel = (strtolower((string) $newType) === 'services' && $request->has('scrap_count_label'))
            ? trim((string) $request->scrap_count_label)
            : null;
        $category->update([
            'name' => $request->name,
            'image' => $imagePath,
            'type' => $newType,
            'scrap_measurement' => $scrapMeasurement,
            'scrap_weight_label' => $scrapWeightLabel,
            'scrap_count_label' => $scrapCountLabel,
        ]);

        return response()->json([
            'success' => true,
            'id' => $category->id,
            'name' => $category->name,
            'image' => $category->image,
            'type' => $category->type,
            'scrap_measurement' => $category->scrap_measurement,
            'scrap_weight_label' => $category->scrap_weight_label,
            'scrap_count_label' => $category->scrap_count_label,
            'message' => 'Category updated successfully',
        ]);
    }

    public function destory_category($id)
    {
        $category = Category::findOrFail($id);

        // Optional: delete image from storage
        if ($category->image && file_exists(public_path($category->image))) {
            unlink(public_path($category->image));
        }
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);
        $model = Category::findOrFail($id);
        $model->status = $request->status;
        $model->save();

        return redirect()->back()->with('success', 'status updated successfully');
    }

    public function updatecategory(Request $request, $id)
    {
        $category = Category::find($id);
        $category->name = $request->name;
        if ($request->hasFile('image')) {
            $category->image = saveSingleFile($request->file('image'), 'category');
        }
        $category->update();

        return redirect()->back()->with('success', 'Category created successfully.');
    }

    // Sub Categories
    public function all_sub_category()
    {
        $categories = Category::whereNull('parent_id')->get();
        $subcategories = Category::whereNotNull('parent_id')->paginate(10);

        return view('admin.category.subcategory', compact('categories', 'subcategories'));
    }

    public function post_sub_category(Request $request)
    {
        $request->all();
        $category = new Category;
        $category->name = $request->name;
        $category->parent_id = $request->parent_id;
        if ($request->hasFile('image')) {
            $category->image = saveSingleFile($request->file('image'), 'category');
        }
        // return  $category;
        $category->save();

        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function updatesubcategory(Request $request, $id)
    {
        $category = Category::find($id);
        $category->name = $request->name;
        $category->parent_id = $request->parent_id;
        if ($request->hasFile('image')) {
            $category->image = saveSingleFile($request->file('image'), 'category');
        }
        $category->update();

        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function deletecategory($id)
    {
        $category = Category::findOrFail($id);

        // Delete image if exists
        if ($category->image && file_exists(public_path($category->image))) {
            unlink(public_path($category->image));
        }

        // Delete category
        $category->delete();

        // Redirect according to parent_id
        if ($category->parent_id !== null) {
            return redirect()->route('all.sub.category')->with('success', 'Subcategory deleted successfully');
        } else {
            return redirect()->route('all.category')->with('success', 'Category deleted successfully');
        }
    }
}
