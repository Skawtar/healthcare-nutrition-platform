<?php

namespace App\Http\Controllers;

use App\Models\DocumentMedical;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    /**
     * Download a medical document
     */
    public function downloadDocument(DocumentMedical $document): StreamedResponse
    {
        // Check if user is authorized to download this document
        
        if (!Storage::exists($document->file_path)) {
            abort(404, 'File not found');
        }

        // Get the correct storage path
        $storagePath = Storage::disk('local')->path($document->file_path);

        return response()->download($storagePath, $document->type.'.pdf');
    }


    /**
     * Download a medical record
     */
    public function downloadMedicalRecord(MedicalRecord $record): StreamedResponse
    {
        // Check if user is authorized to download this record
        
        // Assuming you have a 'file_path' attribute that stores the file location
        if (!storage_path('app/' . $record->file_path)) {
            abort(404);
        }

        return response()->download(storage_path('app/' . $record->file_path));
    }
}