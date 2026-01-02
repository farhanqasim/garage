<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function all_companies()
    {
        $companies = Company::paginate(10);
        return view('admin.company.company', compact('companies'));
    }

    public function post_companies(Request $request)
    {
        $companies = Company::create(
            ['name' => $request->name]
        );
        return response()->json([
            'success' => true,
            'id' => $companies->id,
            'name' => $companies->name
        ]);
    }


    public function updatecompaniesStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);
        $model = Company::findOrFail($id);
        $model->status = $request->status;
        $model->save();

        return redirect()->back()->with('success', 'status updated successfully');
    }

    public function updatecompanies(Request $request, $id)
    {
        $companies =  Company::find($id);
        $companies->name = $request->name;
        $companies->update();
        return redirect()->back()->with('success', 'companies created successfully.');
    }


    public function deletecompanies($id)
    {
        $companies = Company::findOrFail($id);
        $companies->delete();
        return redirect()->back()->with('success', 'companies deleted successfully.');
    }

    public function show_company($id)
    {
        return response()->json(Company::findOrFail($id));
    }


    public function update_company(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        $company->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'id' => $company->id,
            'name' => $company->name,
            'message' => "Company Update Successfully"
        ]);
    }

    public function destory_company($id)
    {
        Company::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Company deleted Successfully"
        ]);
    }
}
