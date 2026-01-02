<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amphor;
use Illuminate\Http\Request;

class AmphorController extends Controller
{
    public function all_amphors()
    {
        $amphors = Amphor::paginate(10);
        return view('admin.amphor.amphor', compact('amphors'));
    }

    public function post_amphors(Request $request)
    {
        $amphor = Amphor::create(
            ['name' => $request->name]
        );
        return response()->json([
            'success' => true,
            'id' => $amphor->id,
            'name' => $amphor->name
        ]);
    }

    public function show_ampere($id)
    {
        return response()->json(Amphor::findOrFail($id));
    }


    public function update_ampere(Request $request, $id)
    {
        $amphor = Amphor::findOrFail($id);
        $amphor->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'id' => $amphor->id,
            'name' => $amphor->name,
            'message' => "Amphor Update Successfully"
        ]);
    }

    public function destory_ampere($id)
    {
        Amphor::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Amphor deleted Successfully"
        ]);
    }




    public function updateamphorsStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);
        $model = Amphor::findOrFail($id);
        $model->status = $request->status;
        $model->save();

        return redirect()->back()->with('success', 'status updated successfully');
    }

    public function updateamphors(Request $request, $id)
    {
        $amphors =  Amphor::find($id);
        $amphors->name = $request->name;
        $amphors->update();
        return redirect()->back()->with('success', 'Amphor created successfully.');
    }


    public function deleteamphors($id)
    {
        $amphors = Amphor::findOrFail($id);
        $amphors->delete();
        return redirect()->back()->with('success', 'Amphor deleted successfully.');
    }
}
