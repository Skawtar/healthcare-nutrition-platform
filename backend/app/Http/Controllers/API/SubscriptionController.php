<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Subscription;
use App\Http\Controllers\Api\PatientProfileController; // Assuming this is needed for formatPatientData
use Illuminate\Support\Facades\Log; // Added for logging, good practice

class SubscriptionController extends Controller
{
    /**
     * Get available services
     */
    public function getServices()
    {
        $services = Service::active()->get();

        return response()->json([
            'success' => true,
            'services' => $services->map(function ($service) {
                $features = $service->features ?? []; // Directly access as array, default to empty if null

                // If features in DB are stored as associative JSON (e.g., {"feature1": "Value"})
                // and you only want the values as a simple array for Flutter:
                if (is_array($features) && !empty($features) && count(array_keys($features)) > 0 && !is_numeric(array_keys($features)[0])) {
                    $features = array_values($features); // Convert associative array to indexed array of values
                }

                // Ensure all features are strings (in case some values are not strings after casting)
                $features = array_map('strval', $features);

                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'price' => $service->price,
                    'duration' => $service->duration ?? null, // Add null coalescing if it might not exist
                    'is_active' => $service->is_active ?? true, // Assuming default true or a field
                    'billing_period' => $service->billing_period,
                    'features' => $features, // This is now a proper PHP array of strings
                ];
            }),
            'message' => 'Available services retrieved successfully'
        ])->setStatusCode(200, 'OK');
    }

    /**
     * Subscribe patient to a service
     */
    public function subscribe(Request $request)
    {
        $patient = $request->user();
        $service = Service::findOrFail($request->service_id);

        // Check if already subscribed
        if ($patient->subscription && $patient->subscription->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active subscription'
            ], 400);
        }

        // Create new subscription
        $subscription = Subscription::create([
            'patient_id' => $patient->id,
            'service_id' => $service->id,
            'plan_name' => $service->name,
            'start_date' => now(),
            'end_date' => now()->addMonth(), // Example: 1 month subscription
            'payment_method' => 'credit_card', // Default, can be changed
            'status' => 'active',
        ]);

        // Update patient subscription status
        $patient->update([
            'is_subscribed' => true,
            'subscription_plan' => $service->name,
            'subscription_start_date' => $subscription->start_date,
            'subscription_end_date' => $subscription->end_date,
            'payment_method' => $subscription->payment_method,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription successful',
            'subscription' => $subscription,
            'patient' => (new PatientProfileController)->formatPatientData($patient->fresh())
        ]);
    }

    /**
     * Cancel patient subscription
     */
    public function cancel(Request $request)
    {
        $patient = $request->user();

        if (!$patient->subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found'
            ], 400);
        }

        // Mark subscription as cancelled
        $patient->subscription->update([
            'status' => 'cancelled',
            'end_date' => now(), // End immediately
        ]);

        // Update patient status
        $patient->update([
            'is_subscribed' => false,
            'subscription_end_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled',
            'patient' => (new PatientProfileController)->formatPatientData($patient->fresh())
        ]);
    }

    /**
     * Get patient's current subscription
     */
    public function getCurrentSubscription(Request $request)
    {
        $patient = $request->user()->load('subscription.service');

        if (!$patient->subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'subscription' => [
                'id' => $patient->subscription->id,
                'plan_name' => $patient->subscription->plan_name,
                'start_date' => $patient->subscription->start_date,
                'end_date' => $patient->subscription->end_date,
                'status' => $patient->subscription->status,
                'is_active' => $patient->subscription->isActive(),
                'service' => $patient->subscription->service,
                'payment_method' => $patient->subscription->payment_method,
            ]
        ]);
    }
}