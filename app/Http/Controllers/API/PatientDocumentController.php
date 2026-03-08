<?php

namespace App\Http\Controllers\API; 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\Validator; 
use Symfony\Component\HttpFoundation\StreamedResponse; 
use App\Http\Controllers\Controller;
use App\Models\DocumentMedical;


class PatientDocumentController extends Controller
{
    // Constants for document types
    const DOCUMENT_TYPES = [
        'ORDONNANCE', 'BILAN_SANGUIN', 'RADIOLOGIE', 'COMPTE_RENDU', 'AUTRE'
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Get documents only for the authenticated patient
            $documents = DocumentMedical::where('patient_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'data' => $documents
            ]);

        } catch (\Exception $e) {
            // It's good practice to log the error for debugging
            \Log::error("Failed to retrieve patient documents: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'message' => 'Failed to retrieve documents',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'file' => 'required|file|max:10240', // 10MB max
        'document_type' => 'required|string|in:' . implode(',', self::DOCUMENT_TYPES), // Expects 'document_type'
        'description' => 'nullable|string|max:1000', // Expects 'description'
    ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $path = $file->store('documents', 'private'); // Store in storage/app/private/documents

            $document = DocumentMedical::create([
                'patient_id' => auth()->id(), // Associate with authenticated patient
                'nom_fichier' => $file->getClientOriginalName(),
                'document_type' => $request->document_type,
                'fichier' => $path, // The stored path
                'description' => $request->description,
                'date_creation' => now(),
            ]);

            return response()->json([
                'message' => 'Document uploaded successfully',
                'data' => $document
            ], 201);

        } catch (\Exception $e) {
            \Log::error("Document upload failed: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Document upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DocumentMedical $patient_document)
    {
        try {
            // Authorization check: Ensure only the patient who owns the document can delete it
            if ($patient_document->patient_id != auth()->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Delete file from storage
            Storage::disk('private')->delete($patient_document->fichier);

            // Delete record from database
            $patient_document->delete();

            return response()->json(['message' => 'Document deleted successfully']);

        } catch (\Exception $e) {
            \Log::error("Document deletion failed: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Document deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download the specified resource from storage.
     */
    public function download(DocumentMedical $patient_document): StreamedResponse
    {
        // Authorization check
        if ($patient_document->patient_id != auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Return the file for download
        return Storage::disk('private')->download(
            $patient_document->fichier,
            $patient_document->nom_fichier
        );
    }
}