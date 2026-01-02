<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LineItem;
use Illuminate\Http\Request;

class LineitemController extends Controller
{
     public function all_lineitems()
        {
            $lineitems = LineItem::paginate(10);
            return view('admin.lineitem.lineitem', compact('lineitems'));
        }

    public function post_lineitems(Request $request)
    {
        $lineitems =LineItem::create(
            ['name' => $request->name]
          );
        return response()->json([
            'success' => true,
            'id' => $lineitems->id,
            'name' => $lineitems->name]);
    }


    public function updatelineitemsStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);
        $model = LineItem::findOrFail($id);
        $model->status = $request->status;
        $model->save();

        return redirect()->back()->with('success', 'status updated successfully');
    }

    public function updatelineitems(Request $request, $id)
    {
        $lineitems =  LineItem::find($id);
        $lineitems->name = $request->name;
        $lineitems->update();
        return redirect()->back()->with('success', 'lineitems created successfully.');
    }


    public function deletelineitems($id)
        {
            $lineitems = LineItem::findOrFail($id);
            $lineitems->delete();
            return redirect()->back()->with('success', 'lineitems deleted successfully.');

        }
}
