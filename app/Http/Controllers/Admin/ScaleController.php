<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scale;
use Illuminate\Http\Request;

class ScaleController extends Controller
{
     public function all_scales()
        {
            $scales = Scale::paginate(10);
            return view('admin.scale.scale', compact('scales'));
        }

    public function post_scales(Request $request)
    {
         $scales =Scale::create(
                ['name' => $request->name]
            );
         return response()->json([
            'success' => true,
            'id' => $scales->id,
             'name' => $scales->name]);
    }


    public function updatescalesStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);
        $model = Scale::findOrFail($id);
        $model->status = $request->status;
        $model->save();

        return redirect()->back()->with('success', 'status updated successfully');
    }

    public function updatescales(Request $request, $id)
    {
        $scales =  Scale::find($id);
        $scales->name = $request->name;
        $scales->update();
        return redirect()->back()->with('success', 'scales created successfully.');
    }


    public function deletescales($id)
        {
            $scales = Scale::findOrFail($id);
            $scales->delete();
            return redirect()->back()->with('success', 'scales deleted successfully.');

        }
}
