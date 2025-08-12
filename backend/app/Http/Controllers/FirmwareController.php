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
            $search = $request->input('search');
            $query->where('filename', 'like', "%{$search}%")
                  ->orWhere('version', 'like', "%{$search}%");
        }

        $firmwares = $query->orderBy('created_at', 'desc')->paginate(10);

        $firmwares->getCollection()->transform(function ($firmware) {
            if ($firmware->filepath) {
                $firmware->url = asset(str_replace('public/', 'storage/', $firmware->filepath));
            } else {
                $firmware->url = null;
            }
            return $firmware;
        });

        return response()->json($firmwares);
    }

    public function store(Request $request)
    {
        Log::info('Inicio store firmware');
        Log::info('Request data:', $request->except('file'));

        try {

            $request->validate([
                'file' => 'required|file|max:1022976', // máximo ~999MB
                'version' => 'nullable|string|max:50',
                'description' => 'nullable|string',
            ]);
            Log::info('Validación correcta');

            $file = $request->file('file');
            if (!$file) {
                Log::error('No se recibió archivo en la petición');
                return response()->json(['error' => 'Archivo no recibido'], 400);
            }

            $filename = $file->getClientOriginalName();
            $filesize = $file->getSize();

            $path = $file->store('public/firmwares');
            Log::info("Archivo guardado en: {$path}");

            $firmware = Firmware::create([
                'filename' => $filename,
                'filepath' => $path,
                'filesize' => $filesize,
                'version' => $request->input('version'),
                'description' => $request->input('description'),
                'uploaded_by' => 'anonimo',
            ]);

            $firmware->url = asset(str_replace('public/', 'storage/', $path));

            Log::info('Firmware creado en DB', $firmware->toArray());

            return response()->json($firmware, 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            Log::error('Validación fallida: '.$ve->getMessage());
            return response()->json(['error' => 'Validación', 'messages' => $ve->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error en store firmware: '.$e->getMessage());
            return response()->json(['error' => 'Error al subir firmware', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $firmware = Firmware::findOrFail($id);
        if ($firmware->filepath) {
            $firmware->url = asset(str_replace('public/', 'storage/', $firmware->filepath));
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
            $firmware->url = asset(str_replace('public/', 'storage/', $firmware->filepath));
        }

        return response()->json($firmware);
    }

    public function destroy($id)
    {
        $firmware = Firmware::findOrFail($id);

        if ($firmware->filepath) {
            Storage::delete($firmware->filepath);
        }

        $firmware->delete();

        return response()->json(null, 204);
    }
}
