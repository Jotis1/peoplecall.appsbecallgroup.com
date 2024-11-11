<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Models\User;
use App\Mail\CsvSender;
use Exception;

class ProcessCsvFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 0;
    public $tries = 5;
    public $user = null;

    public function __construct(public string $path, public int $userId, public int $rateLimit, public string $phoneURL)
    {
        $this->path = $path;
        $this->userId = $userId;
        $this->rateLimit = $rateLimit;
        $this->phoneURL = $phoneURL;
    }

    public function handle(): void
    {
        try {
            $user = User::find($this->userId);
            if (!$user) {
                Log::error("User with id $this->userId not found");
                return;
            }
            
            $user->progress = 0;
            $user->save();

            $file = $this->getCSVFileContent();
            if (count($file) === 0) {
                Log::error("No phone numbers found in file");
                return;
            }

            // Procesar los datos y escribir en tmp
            $tmpPath = "tmp/" . $this->path;
            $this->getDataFromAPI($file, $tmpPath);

            Log::info("Moving file from tmp to public");
            error_log("Moving file from tmp to public");
            // Mover el archivo de tmp a public
            $publicPath = "public/" . $this->path;
            Storage::move($tmpPath, $publicPath);

            // Enviar el archivo al usuario
            Log::info("Sending email to $user->email");
            Mail::to($user->email)->send(new CsvSender($this->path, $this->userId, count($file)));

            // Borrar archivo de tmp (si aún existe)
            Storage::delete($tmpPath);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        } finally {
            $user = User::find($this->userId);
            $user->is_running_job = false;
            $user->save();
        }
    }

    public function getCSVFileContent()
    {
        try {
            $fileContent = Storage::get($this->path);
            $firstColumn = [];
            $fileContent = explode("\n", $fileContent);

            foreach ($fileContent as $line) {
                $lines = str_replace(",", ";", $line);
                $lines = preg_replace('/\s+/', '', $lines);
                $firstRowData = explode(";", $lines)[0];
                $firstRowData = str_replace("\xEF\xBB\xBF", "", $firstRowData);
                $firstRowData = preg_replace("/^(\+34|0034|34)/", "", $firstRowData);

                if (empty($firstRowData) || !is_numeric($firstRowData) || strlen($firstRowData) != 9 || in_array($firstRowData, $firstColumn)) {
                    Log::info("Invalid phone number: $firstRowData");
                    continue;
                }
                $firstColumn[] = $firstRowData;
            }

            return $firstColumn;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return null;
        }
    }

    public function getDataFromAPI(array $numbers, string $tmpPath)
    {
        $user = User::find($this->userId);
        $client = new Client([
            "headers" => [
                "x-api-key" => "69mQ0MjExYzUtMDJlYy0"
            ]
        ]);

        $header = "issued;originalOperator;originalOperatorRaw;currentOperator;currentOperatorRaw;number;prefix;type;typeDescription;queriesLeft;lastPortabilityWhen;lastPortabilityFrom;lastPortabilityFromRaw;lastPortabilityTo;lastPortabilityToRaw";
        Storage::put($tmpPath, $header); // Guardamos el header en tmp

        $chunks = array_chunk($numbers, 10000);
        $totalChunks = count($chunks);
        $currentChunk = 1;
        foreach ($chunks as $chunk) {
            Log::info("Processing chunk $currentChunk of $totalChunks");
            error_log("Processing chunk $currentChunk of $totalChunks");
            $response = $client->post($this->phoneURL, ["json" => $chunk]);
            $dataArray = json_decode($response->getBody(), true);

            foreach ($dataArray as $data) {
                // Extraemos los datos como antes
                $message = implode(";", [
                    $data["issued"] ?? "",
                    $data["originalOperator"] ?? "",
                    $data["originalOperatorRaw"] ?? "",
                    $data["currentOperator"] ?? "",
                    $data["currentOperatorRaw"] ?? "",
                    $data["number"] ?? "",
                    $data["prefix"] ?? "",
                    $data["type"] ?? "",
                    $data["typeDescription"] ?? "",
                    $data["lastPortability"]["when"] ?? "",
                    $data["lastPortability"]["from"] ?? "",
                    $data["lastPortability"]["fromRaw"] ?? "",
                    $data["lastPortability"]["to"] ?? "",
                    $data["lastPortability"]["toRaw"] ?? "",
                ]);
                Storage::append($tmpPath, $message); // Guardamos los mensajes en tmp
            }

            // Guardar respuesta completa en un archivo separado por bloque
            Storage::append("public/save/" . basename($tmpPath), $response->getBody());
            
            $user->progress = ($currentChunk / $totalChunks) * 100;
            $user->save();
            $currentChunk++;
        }
    }
}
