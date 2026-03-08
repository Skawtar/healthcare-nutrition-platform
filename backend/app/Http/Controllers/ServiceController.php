<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch active services, paginated
        $services = Service::latest()->paginate(10);
        return view('admin.services.index', compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $billingPeriods = $this->getBillingPeriods();
        return view('admin.services.create', compact('billingPeriods'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate($this->rules());

        // Generate slug
        $validatedData['slug'] = Str::slug($validatedData['name']);

        Service::create($validatedData);

        return redirect()->route('services.index')
                         ->with('success', 'Service created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        return view('admin.services.show', compact('service'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        $billingPeriods = $this->getBillingPeriods();
        return view('admin.services.edit', compact('service', 'billingPeriods'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        $validatedData = $request->validate($this->rules($service->id));

        // Re-generate slug if name changes (or if it's explicitly allowed to be changed)
        $validatedData['slug'] = Str::slug($validatedData['name']);

        $service->update($validatedData);

        return redirect()->route('services.index')
                         ->with('success', 'Service updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        $service->delete(); // Soft delete

        return redirect()->route('services.index')
                         ->with('success', 'Service deleted successfully.');
    }

    /**
     * Display a listing of soft-deleted resources.
     */
    public function trashed()
    {
        $services = Service::onlyTrashed()->latest()->paginate(10);
        return view('admin.services.trashed', compact('services'));
    }

    /**
     * Restore the specified soft-deleted resource from storage.
     */
    public function restore($id)
    {
        $service = Service::onlyTrashed()->findOrFail($id);
        $service->restore();

        return redirect()->route('services.index')
                         ->with('success', 'Service restored successfully.');
    }

    /**
     * Permanently delete the specified resource from storage.
     */
    public function forceDelete($id)
    {
        $service = Service::onlyTrashed()->findOrFail($id);
        $service->forceDelete();

        return redirect()->route('services.trashed')
                         ->with('success', 'Service permanently deleted.');
    }


    /**
     * Get the validation rules for store/update.
     */
    protected function rules($id = null)
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services')->ignore($id), // Name must be unique, except for itself on update
            ],
            // Slug is generated, so not required from input, but good to know it exists
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_period' => ['required', 'string', Rule::in($this->getBillingPeriods())],
            'features' => 'nullable|string', // Assuming comma-separated or plain text
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get available billing periods.
     */
    protected function getBillingPeriods()
    {
        return [
            'monthly' => 'Monthly',
            'annually' => 'Annually',
            'one-time' => 'One-Time',
            // Add other periods as needed
        ];
    }
}