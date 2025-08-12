<?php

namespace App\Http\Controllers;

use App\Models\Firmware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FirmwareController extends Controller
{
    // Listar firmwares, con búsqueda simple por filename
    public function index(Request $request)
    {
        $query = Firmware::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('filename', 'like', "%{$search}%")
                  ->orWhere('version', 'like', "%{$search}%");
        }

        $firmwares = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($firmwares);
    }

    // Guardar nuevo firmware con archivo
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // max 50MB
            'version' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('file');

        $filename = $file->getClientOriginalName();
        $filesize = $file->getSize();

        // Guardar archivo en storage/app/firmwares
        $path = $file->store('firmwares');

        $firmware = Firmware::create([
            'filename' => $filename,
            'filepath' => $path,
            'filesize' => $filesize,
            'version' => $request->input('version'),
            'description' => $request->input('description'),
            'uploaded_by' => 'anonimo', // o usar auth()->user()->name si autenticás
        ]);

        return response()->json($firmware, 201);
    }

    // Mostrar un firmware específico
    public function show($id)
    {
        $firmware = Firmware::findOrFail($id);
        return response()->json($firmware);
    }

    // Actualizar firmware (opcional, sin archivo)
    public function update(Request $request, $id)
    {
        $firmware = Firmware::findOrFail($id);

        $request->validate([
            'version' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $firmware->update($request->only(['version', 'description']));

        return response()->json($firmware);
    }

    // Eliminar firmware y borrar archivo físico
    public function destroy($id)
    {
        $firmware = Firmware::findOrFail($id);

        // Borrar archivo físico
        Storage::delete($firmware->filepath);

        $firmware->delete();

        return response()->json(null, 204);
    }
}
