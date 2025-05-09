<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCsvUpload;
use App\Models\Upload;
use App\Http\Resources\UploadResource;
use Illuminate\Http\Request;

class CsvUploadController extends Controller
{
    /**
     * Show the upload view
     */
    public function uploadView()
    {
        return view('upload');
    }

    /**
     * Upload file post
     */
    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file',
        ]);

        $guestId = $request->input('guest_id');
        $path = $request->file('csv_file')->store('uploads');

        $upload = Upload::create([
            'user_id' => 0,
            'guest_id' => $guestId,
            'file_name' => $request->file('csv_file')->getClientOriginalName(),
            'file_path' => $path,
            'status' => 'pending',
        ]);

        ProcessCsvUpload::dispatch($path, $guestId, $upload->id);

        return response()->json(['status' => 'pending']);
    }

    /**
     * Get uploaded history
     */
    public function uploadHistory(Request $request)
    {
        $guestId = $request->input('guest_id');

        if (!$guestId) {
            return response()->json(['error' => 'guest_id missing'], 400);
        }

        $uploads = Upload::where('guest_id', $guestId)
            ->orderByDesc('created_at')
            ->get();

        return UploadResource::collection($uploads);
    }

    /**
     * Clear uploaded history
     */
    public function clearUploads(Request $request)
    {
        $guestId = $request->header('X-Guest-ID');

        if (!$guestId) {
            return response()->json(['status' => 'guest_id_missing'], 400);
        }

        Upload::where('guest_id', $guestId)->delete();

        return response()->json(['status' => 'cleared']);
    }
}
