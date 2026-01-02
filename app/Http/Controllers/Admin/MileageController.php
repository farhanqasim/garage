<?php

namespace App\Http\Controllers\Admin;

use App\Models\Mileage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MileageController extends Controller
{

    public function all_mileage()
    {
        $mileages = Mileage::paginate(10);
        return view('admin.Mileage.Mileage', compact('mileages'));
    }

    public function post_mileage(Request $request)
    {
        $Mileages = new Mileage();
        $Mileages->name = $request->name;
        $Mileages->save();
        return redirect()->back()->with('success', 'Mileages created successfully.');
    }


      public function post_item_mileage(Request $request)
        {
            $Mileages = Mileage::create(
            ['name' => $request->name]
          );
          return response()->json([
            'success' => true,
            'id' => $Mileages->id,
            'name' => $Mileages->name]);
        }

         public function show_mileage($id)
                {
                    return response()->json(Mileage::findOrFail($id));
                }


    public function update_mileage(Request $request, $id)
            {
                $Mileage = Mileage::findOrFail($id);
                $Mileage->update(['name' => $request->name]);

                return response()->json([
                    'success' => true,
                    'id' => $Mileage->id,
                    'name' => $Mileage->name,
                    'message' =>"Mileage Update Successfully"
                ]);
            }

            public function destory_mileage($id)
            {
                Mileage::findOrFail($id)->delete();
                return response()->json([
                    'success' => true,
                    'message' =>"Mileage deleted Successfully"
                ]);
            }




    public function updatemileageStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);
        $model = Mileage::findOrFail($id);
        $model->status = $request->status;
        $model->save();
        return redirect()->back()->with('success', 'status updated successfully');
    }

    public function updatemileage(Request $request, $id)
    {
        $Mileages =  Mileage::find($id);
        $Mileages->name = $request->name;
        $Mileages->update();
        return redirect()->back()->with('success', 'Mileages created successfully.');
    }


    public function deletemileage($id)
    {
        $Mileages = Mileage::findOrFail($id);
        $Mileages->delete();
        return redirect()->back()->with('success', 'Mileages deleted successfully.');
    }
}
