<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentLabel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ShipmentLabelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:shipment-label-download', ['only' => ['download']]);
    }

    public function download(ShipmentLabel $label)
    {
        try {
            if (!Storage::exists($label->file_path)) {
                return back()->withErrors(['error' => 'Label file not found']);
            }

            return Storage::download($label->file_path, $label->filename);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to download label: ' . $e->getMessage()]);
        }
    }

    public function store(Request $request, Shipment $shipment)
    {
        $request->validate([
            'label_file' => 'required|file|mimes:pdf,png,jpg,jpeg|max:10240',
            'label_type' => 'required|in:shipping,return,customs',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $file = $request->file('label_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = 'shipment-labels/' . $shipment->id . '/' . $filename;

            Storage::putFileAs('public/' . dirname($filePath), $file, $filename);

            $label = ShipmentLabel::create([
                'shipment_id' => $shipment->id,
                'label_type' => $request->label_type,
                'filename' => $filename,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'notes' => $request->notes,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Label uploaded successfully',
                'label' => $label
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload label: ' . $e->getMessage()
            ], 500);
        }
    }
} 