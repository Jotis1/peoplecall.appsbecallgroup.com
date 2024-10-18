<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessCsvFile;
use Illuminate\Support\Facades\Log;
use App\Models\Queues;

class FetchFileController extends Controller
{
    public function save(Request $request)
    {
        try {
            Log::info("Procesando archivo CSV");
            // Validación de los campos
            $request->validate([
                'csv' => 'required|mimes:csv,txt'
            ]);
            $file = $request->file('csv');
            $fileName = $file->getClientOriginalName();
            // Si el archivo ya existe, le añadimos un número al final
            $i = 1;
            while (Storage::exists(Auth::user()->name . '/' . $fileName)) {
                $fileName = $i . '_' . $file->getClientOriginalName();
                $i++;
            }
            // Guardar el archivo en la carpeta del usuario
            $user = Auth::user();
            $name = $user->name;
            $day = date('d-m-Y');
            $hour = date('H-i-s');
            $path = "$name/csv/$day";
            Storage::disk('local')->put("$path/$hour.csv", file_get_contents($file));
            // Comprobamos que el usuario tiene los suficientes "créditos"
            $content = Storage::get("$path/$hour.csv");
            $rows = explode("\n", $content);
            $count = count($rows);
            $monthlyRequests = $user->monthly_requests;
            $excutedRequests = $user->executed_requests;
            if ($monthlyRequests !== -1 && $count > $monthlyRequests) {
                session()->flash('error', 'No tienes suficientes solicitudes restantes para procesar este archivo.');
                return redirect()->route('dashboard');
            } else if ($user->is_running_job) {
                session()->flash('error', 'Ya tienes un proceso en ejecución, espera a que termine.');
                return redirect()->route('dashboard');
            }
            // Obtenemos las variables necesarias para el trabajo en segundo plano
            $rateLimit = env('PHONE_API_RATE_LIMIT', 300);
            $phoneURL = env('PHONE_API_URL', 'https://numclass-api.nubefone.com/v2/numbers/');
            // Actualizar el contador de solicitudes
            $user->executed_requests = $excutedRequests + $count;
            $user->is_running_job = true;
            $userId = $user->id;
            $user->save();
            $job = new ProcessCsvFile("$path/$hour.csv", $userId, $rateLimit, $phoneURL);
            // Comprobar si hay trabajos en la cola
            $queue = Queues::first();
            if (!$queue) {
                $queue = new Queues();
                $queue->save();
            }
            $chosenQueue = /*$queue->secondary >= $queue->primary ? 'primary' : 'secondary';*/ 'primary';
            if ($chosenQueue === 'primary') {
                $queue->primary = $queue->primary + 1;
            } else {
                $queue->secondary = $queue->secondary + 1;
            }
            $queue->save();
            // Redirigir a la página principal y ejecutar el trabajo en segundo plano
            dispatch($job)->onQueue($chosenQueue);
            return redirect()->route('dashboard');
        } catch (\Throwable $th) {
            $user = Auth::user();
            $user->is_running_job = false;
            $user->save();
            session()->flash('error', 'Error al procesar el archivo CSV');
            Log::error("Error al procesar el archivo CSV");
            Log::error($th);
        }
    }

    public function download($username, $folder, $file)
    {
        try {
            $path = "$username/csv/$folder/$file";
            $path = "/public/$path";
            return Storage::download($path);
        } catch (\Throwable $th) {
            Log::error("Error al descargar el archivo");
            Log::error($th);
        }
    }
}
