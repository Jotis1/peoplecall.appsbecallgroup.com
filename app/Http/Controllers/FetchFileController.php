<?php

namespace App\Http\Controllers;

use App\Models\Queues;
use App\Jobs\ProcessCsvFile;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FetchFileController extends Controller
{
    public function save(Request $request)
    {
        try {
            $request->validate([
                'csv' => 'required|mimes:csv,txt'
            ]);

            $csv = $request->file('csv');
            $file = new File();
            $file->name = $csv->getClientOriginalName();

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

            $job = new ProcessCsvFile($filePath, $file->id);

            $queue = Queues::first();
            if (!$queue) {
                $queue = new Queues();
                $queue->save();
            }

            $chosenQueue = $queue->secondary >= $queue->primary ? 'primary' : 'secondary';
            if ($chosenQueue === 'primary') {
                $queue->primary = $queue->primary + 1;
            } else {
                $queue->secondary = $queue->secondary + 1;
            }

            $queue->save();

            dispatch($job)->onQueue($chosenQueue);
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
            } else if (!$file->processed) {
                session()->flash('error', 'El archivo aún no ha sido procesado');
                return redirect()->route('dashboard');
            }

            $numbers = $file->numbers;

            $content = '';
            $header = "issued;originalOperator;originalOperatorRaw;currentOperator;currentOperatorRaw;number;prefix;type;typeDescription;queriesLeft;lastPortabilityWhen;lastPortabilityFrom;lastPortabilityFromRaw;lastPortabilityTo;lastPortabilityToRaw";
            $content .= $header . "\n";

            foreach ($numbers as $number) {
                $content .= $number->issued . ';';
                $content .= $number->originalOperator . ';';
                $content .= $number->originalOperatorRaw . ';';
                $content .= $number->currentOperator . ';';
                $content .= $number->currentOperatorRaw . ';';
                $content .= $number->number . ';';
                $content .= $number->prefix . ';';
                $content .= $number->type . ';';
                $content .= $number->typeDescription . ';';
                $content .= $number->queriesLeft . ';';
                $content .= $number->lastPortabilityWhen . ';';
                $content .= $number->lastPortabilityFrom . ';';
                $content .= $number->lastPortabilityFromRaw . ';';
                $content .= $number->lastPortabilityTo . ';';
                $content .= $number->lastPortabilityToRaw . "\n";
            }

            $path = 'download/' . $file->id;
            Storage::disk('public')->put($path, $content);
            return Storage::disk('public')->download($path, $file->name);
        } catch (\Throwable $th) {
            Log::error("Error al descargar el archivo");
            Log::error($th);
        }
    }
}
