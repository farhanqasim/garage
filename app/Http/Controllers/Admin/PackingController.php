<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Packing;
use Illuminate\Http\Request;

class PackingController extends Controller
{
     public function all_packings()
        {
            $packings = Packing::paginate(10);
            return view('admin.packing.packing', compact('packings'));
        }

    public function post_packings(Request $request)
    {
        $packings =Packing::create(
                ['name' => $request->name]
            );
            return response()->json([
                'success' => true,
                'id' => $packings->id,
                'name' => $packings->name]);
    }


    public function updatepackingsStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);
        $model = Packing::findOrFail($id);
        $model->status = $request->status;
        $model->save();

        return redirect()->back()->with('success', 'status updated successfully');
    }

    public function updatepackings(Request $request, $id)
    {
        $packings =  Packing::find($id);
        $packings->name = $request->name;
        $packings->update();
        return redirect()->back()->with('success', 'packings created successfully.');
    }


    public function deletepackings($id)
        {
            $packings = Packing::findOrFail($id);
            $packings->delete();
            return redirect()->back()->with('success', 'packings deleted successfully.');

        }
}
