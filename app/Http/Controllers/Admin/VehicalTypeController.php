<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\VehicalType;
use Illuminate\Http\Request;

class VehicalTypeController extends Controller
{
     public function all_vehical()
        {
            $vehicals = VehicalType::paginate(10);
            return view('admin.vehical.index', compact('vehicals'));
        }

    public function post_vehical(Request $request)
    {
    $vehical = VehicalType::create(
        ['name' => $request->name]
    );
    return response()->json([
        'success' => true,
        'id' => $vehical->id,
         'name' => $vehical->name]);
    }


    public function updatevehicalStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);
        $model = VehicalType::findOrFail($id);
        $model->status = $request->status;
        $model->save();

        return redirect()->back()->with('success', 'status updated successfully');
    }

    public function updatevehical(Request $request, $id)
    {
        $vehicals =  VehicalType::find($id);
        $vehicals->name = $request->name;
        $vehicals->update();
        return redirect()->back()->with('success', 'vehical created successfully.');
    }


    public function deletevehical($id)
        {
            $vehicals = VehicalType::findOrFail($id);
            $vehicals->delete();
            return redirect()->back()->with('success', 'vehical deleted successfully.');

        }
}
