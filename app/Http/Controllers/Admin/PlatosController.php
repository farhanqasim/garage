<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Platos;
use Illuminate\Http\Request;

class PlatosController extends Controller
{
     public function all_platos()
    {
        $platos = Platos::paginate(10);
        return view('admin.platos.platos', compact('platos'));
    }

    public function post_platos(Request $request)
    {
        $platos =Platos::create(
            ['name' => $request->name]
          );
          return response()->json(
            [
                'success' => true,
                'id' => $platos->id,
                 'name' => $platos->name]);
    }


           public function show_plate($id)
                {
                    return response()->json(Platos::findOrFail($id));
                }


           public function update_plate(Request $request, $id)
            {
                $Platos = Platos::findOrFail($id);
                $Platos->update(['name' => $request->name]);
                return response()->json([
                    'success' => true,
                    'id' => $Platos->id,
                    'name' => $Platos->name,
                    'message' =>"Platos Update Successfully"
                ]);
            }

            public function destory_plate($id)
            {
                Platos::findOrFail($id)->delete();
                return response()->json([
                    'success' => true,
                    'message' =>"Platos deleted Successfully"
                ]);
            }






    public function updateplatosStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);
        $model = Platos::findOrFail($id);
        $model->status = $request->status;
        $model->save();

        return redirect()->back()->with('success', 'status updated successfully');
    }

    public function updateplatos(Request $request, $id)
    {
        $platos =  Platos::find($id);
        $platos->name = $request->name;
        $platos->update();
        return redirect()->back()->with('success', 'Brand created successfully.');
    }


    public function deleteplatos($id)
        {
            $platos = Platos::findOrFail($id);
            $platos->delete();
            return redirect()->back()->with('success', 'Brand deleted successfully.');

        }
}
