<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandCotroller extends Controller
{
     public function all_brands()
    {
        $brands = Brand::paginate(10);
        return view('admin.brand.brand', compact('brands'));
    }

    public function post_brand(Request $request)
    {
        $brands = new Brand();
        $brands->name = $request->name;
        if ($request->hasFile('image')) {
            $brands->image = saveSingleFile($request->file('image'), 'brands');
        }
        // return  $category;
        $brands->save();
        return redirect()->back()->with('success', 'Brand created successfully.');
    }


    public function updatebrandStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);
        $model = Brand::findOrFail($id);
        $model->status = $request->status;
        $model->save();

        return redirect()->back()->with('success', 'status updated successfully');
    }

    public function updatebrand(Request $request, $id)
    {
        $brands =  Brand::find($id);
        $brands->name = $request->name;
        if ($request->hasFile('image')) {
            $brands->image = saveSingleFile($request->file('image'), 'category');
        }
        $brands->update();
        return redirect()->back()->with('success', 'Brand created successfully.');
    }


    public function deletebrand($id)
        {
            $brands = Brand::findOrFail($id);

            // Delete image if exists
            if ($brands->image && file_exists(public_path($brands->image))) {
                unlink(public_path($brands->image));
            }
            //Delete brands
            $brands->delete();
            return redirect()->back()->with('success', 'Brand deleted successfully.');

        }
}
