<?php

namespace App\Http\Controllers;

use App\Exports\FilesExport;
use App\Jobs\ProcessDownload;
use App\Models\Queues;
use App\Jobs\ProcessCsvFile;
use App\Models\File;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Response;

class FetchFileController extends Controller
{
    public function save(Request $request)
    {
        try {
            error_log("Save file");
            $request->validate([
                // max 10mb
                'csv' => 'required|mimes:csv,txt|max:10000',
            ]);

            error_log("Validated");
            $csv = $request->file('csv');
            $file = new File();
            // check if a file with the same name exists and add a number to the name
            $exists = File::where('name', $csv->getClientOriginalName())->first();
            if ($exists) {
                $file->name = time() . '_' . $csv->getClientOriginalName();
            } else {
                $file->name = $csv->getClientOriginalName();
            }

            // Check if csv has more rows than the user limit
            $rows = count(file($csv));

            $user = Auth::user();
            $monthlyRequests = $user->monthly_requests;
            $excutedRequests = $user->executed_requests;

            if ($monthlyRequests !== -1 && $excutedRequests + $rows > $monthlyRequests) {
                session()->flash('error', 'No tienes suficientes solicitudes restantes para procesar este archivo.');
                return redirect()->route('dashboard');
            }

            $path = Auth::user()->name . '/tmp/';
            $filePath = $path . $file->name;
            Storage::disk('public')->putFileAs($path, $csv, $file->name);

            if (!Storage::disk('public')->exists($filePath)) {
                session()->flash('error', 'Error al guardar el archivo');
                return redirect()->route('dashboard');
            }

            $file->user_id = Auth::id();
            $file->save();

            $job = new ProcessCsvFile($filePath, $file->id, $rows);

            $queue = Queues::first();
            if (!$queue) {
                $queue = new Queues();
                $queue->save();
            }

            $queue->save();

            dispatch($job);
            session()->flash('success', 'Archivo CSV procesando en segundo plano. Te enviaremos un correo cuando esté listo.');

            return redirect()->route('dashboard');
        } catch (\Throwable $th) {
            Log::error("Error al guardar el archivo");
            Log::error($th);
            return redirect()->route('dashboard');
        }
    }

    public function download($fileId)
    {
        try {
            $user = Auth::user();
            $file = File::find($fileId);

            if (!$file || $file->user_id !== $user->id) {
                session()->flash('error', 'No se encontró el archivo');
                return redirect()->route('dashboard');
            } elseif (!$file->processed) {
                session()->flash('error', 'El archivo aún no ha sido procesado');
                return redirect()->route('dashboard');
            }

            $path = 'csv/' . $file->name;
            $path = addslashes($path);

            if (file_exists('/var/lib/mysql/' + $path)) {
                unlink($path);  // Elimina el archivo si ya existe
            }

            $query = "
                SELECT 
                    'number', 
                    'originalOperator', 
                    'currentOperator', 
                    'type', 
                    'lastPortability', 
                    'lastPortabilityWhen',
                    'lastPortabilityFrom', 
                    'lastPortabilityTo'
                UNION ALL
                SELECT 
                    n.number, 
                    n.originalOperator, 
                    n.currentOperator, 
                    n.type, 
                    n.lastPortability, 
                    n.lastPortabilityWhen,
                    n.lastPortabilityFrom, 
                    n.lastPortabilityTo
                FROM numbers n
                JOIN file_number fn ON n.id = fn.number_id
                WHERE fn.file_id = $fileId
                INTO OUTFILE '$path'
                FIELDS TERMINATED BY ';' 
                ENCLOSED BY '\"' 
                LINES TERMINATED BY '\\n'
            ";

            DB::statement($query);

            error_log("File downloaded at: " . $path);
            session()->flash('success', 'Archivo descargado');
            return response()->download($path);
        } catch (\Throwable $th) {
            Log::error("Error al descargar el archivo");
            Log::error($th);
            session()->flash('error', 'Error al descargar el archivo');
            return redirect()->route('dashboard');
        }
    }

}
