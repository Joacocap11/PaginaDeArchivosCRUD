<?php

namespace App\Http\Controllers;

use App\Models\Firmware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FirmwareController extends Controller
{
    public function index(Request $request)
    {
        $query = Firmware::query();
        if ($request->has('search')) {
            $s = $request->input('search');
            $query->where('filename', 'like', "%{$s}%")
                  ->orWhere('version', 'like', "%{$s}%");
        }

        $firmwares = $query->orderBy('created_at', 'desc')->paginate(10);

        $firmwares->getCollection()->transform(function ($fw) {
            $fw->url = $fw->filepath ? asset('storage/' . $fw->filepath) : null;
            return $fw;
        });

        return response()->json($firmwares);
    }

    public function store(Request $request)
    {
        Log::info('Inicio store firmware', $request->all());

        $request->validate([
            'file' => 'required|file|max:1022976', // ~999MB
            'version' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('file');
        if (!$file) {
            Log::error('No se recibió archivo');
            return response()->json(['error' => 'Archivo no recibido'], 400);
        }

        $originalName = $file->getClientOriginalName();
        $filesize = $file->getSize();

        $uniqueName = uniqid() . '_' . preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $originalName);

        $path = $file->storeAs('firmwares', $uniqueName, 'public');

        $firmware = Firmware::create([
            'filename'    => $originalName,
            'filepath'    => $path, 
            'filesize'    => $filesize,
            'version'     => $request->input('version'),
            'description' => $request->input('description'),
            'uploaded_by' => 'anonimo',
        ]);

        $firmware->url = asset('storage/' . $path);

        return response()->json($firmware, 201);
    }

    public function show($id)
    {
        $firmware = Firmware::findOrFail($id);
        if ($firmware->filepath) {
            $firmware->url = asset('storage/' . $firmware->filepath);
        }
        return response()->json($firmware);
    }

    public function update(Request $request, $id)
    {
        $firmware = Firmware::findOrFail($id);

        $request->validate([
            'version' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $firmware->update($request->only(['version', 'description']));

        if ($firmware->filepath) {
            $firmware->url = asset('storage/' . $firmware->filepath);
        }

        return response()->json($firmware);
    }

    public function destroy($id)
    {
        $firmware = Firmware::findOrFail($id);

        if ($firmware->filepath) {
            Storage::disk('public')->delete($firmware->filepath);
        }

        $firmware->delete();

        return response()->json(null, 204);
    }
}
