<?php

namespace App\Http\Controllers;

use App\Models\FileUpload;
use App\Jobs\ProcessCsvJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileUploadController extends Controller
{
    /**
     * Display the upload form and recent uploads
     */
    public function index()
    {
        $uploads = FileUpload::orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
            
        return view('upload', compact('uploads'));
    }

    /**
     * Handle file upload
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|max:102400', // 100MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('file');
        
        // Store the file
        $path = $file->store('uploads', 'local');
        
        // Create file upload record
        $fileUpload = FileUpload::create([
            'filename' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'status' => 'pending',
            'total_records' => 0,
            'processed_records' => 0,
            'failed_records' => 0,
        ]);

        // Dispatch the processing job
        ProcessCsvJob::dispatch($fileUpload);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => [
                'id' => $fileUpload->id,
                'filename' => $fileUpload->original_name,
            ]
        ]);
    }

    /**
     * Get upload status
     */
    public function status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:file_uploads,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid upload ID'
            ], 422);
        }

        $upload = FileUpload::findOrFail($request->id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $upload->id,
                'filename' => $upload->original_name,
                'status' => $upload->status,
                'total_records' => $upload->total_records,
                'processed_records' => $upload->processed_records,
                'failed_records' => $upload->failed_records,
                'progress_percentage' => $upload->progress_percentage,
                'error_message' => $upload->error_message,
                'created_at' => $upload->created_at,
                'completed_at' => $upload->completed_at,
            ]
        ]);
    }

    /**
     * Get all uploads for dashboard
     */
    public function uploads()
    {
        $uploads = FileUpload::orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($upload) {
                return [
                    'id' => $upload->id,
                    'filename' => $upload->original_name,
                    'status' => $upload->status,
                    'total_records' => $upload->total_records,
                    'processed_records' => $upload->processed_records,
                    'failed_records' => $upload->failed_records,
                    'progress_percentage' => $upload->progress_percentage,
                    'error_message' => $upload->error_message,
                    'created_at' => $upload->created_at,
                    'completed_at' => $upload->completed_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $uploads
        ]);
    }
}