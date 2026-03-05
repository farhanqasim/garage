<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function all_category()
    {
        $categories = Category::whereNull('parent_id')->paginate(10);
        return view('admin.category.category', compact('categories'));
    }

    public function post_category(Request $request)
    {
        $category = new Category();
        $category->name = $request->name;
        if ($request->hasFile('image')) {
            $category->image = saveSingleFile($request->file('image'), 'category');
        }
        // return  $category;
        $category->save();
        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function post_category_item(Request $request){
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
                'already_exists' => true,
                'message' => 'This category already exists. It has been selected for you.'
            ]);
        }
        $imagepath = null;
        if ($request->hasFile('image')) {
            $imagepath = saveSingleFile($request->file('image'), 'category');
        }
        $scrapMeasurement = $request->filled('scrap_measurement') && in_array($request->scrap_measurement, ['weight', 'count'])
            ? $request->scrap_measurement
            : null;
        $category = Category::create([
            'name' => $name,
            'image' => $imagepath,
            'type' => $request->type ?? null,
            'scrap_measurement' => $scrapMeasurement,
        ]);

        return response()->json([
        'success' => true,
        'id' => $category->id,
        'name' => $category->name,
        'image' => $category->image,
        'type' => $category->type,
        'scrap_measurement' => $category->scrap_measurement,
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

        $category->update([
            'name'  => $request->name,
            'image' => $imagePath,
            'type'  => $request->type ?? $category->type, // Update type if provided
            'scrap_measurement' => $request->has('scrap_measurement') && in_array($request->scrap_measurement, ['weight', 'count']) ? $request->scrap_measurement : $category->scrap_measurement,
        ]);

        return response()->json([
            'success' => true,
            'id'      => $category->id,
            'name'    => $category->name,
            'image'   => $category->image,
            'type'    => $category->type, // Include type in response
            'scrap_measurement' => $category->scrap_measurement,
            'message' => 'Category updated successfully'
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
            'message' => 'Category deleted successfully'
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
        $category =  Category::find($id);
        $category->name = $request->name;
        if ($request->hasFile('image')) {
            $category->image = saveSingleFile($request->file('image'), 'category');
        }
        $category->update();
        return redirect()->back()->with('success', 'Category created successfully.');
    }

    //Sub Categories
    public function all_sub_category()
    {
        $categories = Category::whereNull('parent_id')->get();
        $subcategories = Category::whereNotNull('parent_id')->paginate(10);
        return view('admin.category.subcategory', compact('categories', 'subcategories'));
    }

    public function post_sub_category(Request $request)
    {      $request->all();
        $category = new Category();
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
        $category =  Category::find($id);
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
