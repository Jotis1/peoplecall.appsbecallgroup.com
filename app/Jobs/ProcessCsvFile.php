<?php

namespace App\Jobs;

use GuzzleHttp\Promise\Promise;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Client;

use App\Models\User;
use App\Models\PhoneNumbers;
use App\Mail\CsvSender;

use Exception;
use GuzzleHttp\Psr7\Request;

class ProcessCsvFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 0;
    public $tries = 5;
    public $user = null;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $path, public int $userId, public int $rateLimit, public string $phoneURL)
    {
        $this->path = $path;
        $this->userId = $userId;
        $this->rateLimit = $rateLimit;
        $this->phoneURL = $phoneURL;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            // Obtenemos el usuario
            $user = User::find($this->userId);
            if (!$user) {
                Log::error("User with id $this->userId not found");
                return;
            }

            // Obtenemos el contenido del archivo
            $file = $this->getCSVFileContent();
            if (count($file) === 0) {
                Log::error("No phone numbers found in file");   
                return;
            }

            $this->getDataFromAPI($file, $this->path);

            //Borramos el archivo anterior
            Storage::delete($this->path);

            //Creamos la ruta publica

            // Enviamos el archivo al usuario
            Mail::to($user->email)->send(new CsvSender($this->path, $this->userId, count($file)));

        } catch (Exception $e) {

            Log::error($e->getMessage());

        } finally {

            $user = User::find($this->userId);
            $user->is_running_job = false;
            $user->save();

        }
    }

    public function getCSVFileContent() {
        try {

            // Obtenemos el contenido del archivo
            $fileContent = Storage::get($this->path);
            // Nos quedamos con la primera columna
            $firstColumn = [];
            $fileContent = explode("\n", $fileContent);
            // Recorremos las líneas del archivo
            foreach ($fileContent as $line) {
                // Separamos por punto y coma
                $lines = str_replace(",",";", $line);
                $firstRowData = explode(";", $lines)[0];
                // Quitamos los espacios en blanco
                $firstRowData = preg_replace('/\s+/', '', $line);
                $firstRowData = str_replace("\xEF\xBB\xBF", "", $firstRowData);
                // Teniendo en cuenta que serán números de teléfono, eliminamos los prefijos
                $firstRowData = preg_replace("/^(\+34|0034|34)/", "", $firstRowData);
                // Comprobamos que sea un número de teléfono
                if (empty($firstRowData) || !is_numeric($firstRowData) || strlen($firstRowData) != 9 || in_array($firstRowData, $firstColumn)){
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

    public function getDataFromAPI(array $numbers, string $path) {

        $client = new Client([
            "headers" => [
                "x-api-key" => "69mQ0MjExYzUtMDJlYy0"
            ]
        ]);

        $endpointURL = $this->phoneURL;

        $response = $client->post($endpointURL, [
            "json" => $numbers
        ]);

        $dataArray = json_decode($response->getBody(), true);

        $header = "issued;originalOperator;originalOperatorRaw;currentOperator;currentOperatorRaw;number;prefix;type;typeDescription;queriesLeft;lastPortabilityWhen;lastPortabilityFrom;lastPortabilityFromRaw;lastPortabilityTo;lastPortabilityToRaw";
        
        Storage::put("public/".$path, $header);

        foreach ($dataArray as $data) {
            $issued = $data["issued"] ?? "";
            $originalOperator = $data["originalOperator"] ?? "";
            $originalOperatorRaw = $data["originalOperatorRaw"] ?? "";
            $currentOperator = $data["currentOperator"] ?? "";
            $currentOperatorRaw = $data["currentOperatorRaw"] ?? "";
            $number = $data["number"] ?? "";
            $prefix = $data["prefix"] ?? "";
            $type = $data["type"] ?? "";
            $typeDescription = $data["typeDescription"] ?? "";
            $lastPortabilityWhen = $data["lastPortability"]["when"] ?? "";
            $lastPortabilityFrom = $data["lastPortability"]["from"] ?? "";
            $lastPortabilityFromRaw = $data["lastPortability"]["fromRaw"] ?? "";
            $lastPortabilityTo = $data["lastPortability"]["to"] ?? "";
            $lastPortabilityToRaw = $data["lastPortability"]["toRaw"] ?? "";

            $message = "$issued;$originalOperator;$originalOperatorRaw;$currentOperator;$currentOperatorRaw;$number;$prefix;$type;$typeDescription;$lastPortabilityWhen;$lastPortabilityFrom;$lastPortabilityFromRaw;$lastPortabilityTo;$lastPortabilityToRaw";
            Storage::append("public/".$path, $message);
        }

    }

}