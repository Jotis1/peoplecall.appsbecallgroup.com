<?php

namespace App\Livewire;

use Livewire\Component;
use GuzzleHttp\Client;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Exception;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
class Login extends Component
{
    #[Validate]
    public $username = '';
    #[Validate('min:8')]
    public $password = '';

    public function rules()
    {
        return [
            'username' => 'required',
            'password' => 'min:8|required',
        ];
    }

    public function messages(){
        return [
            'username.required' => 'El campo usuario es requerido',
            'password.required' => 'El campo contraseña es requerido',
            'password.min' => 'Tu contraseña debe tener al menos 8 caracteres',
        ];
    }

    public function login(){
        // Validación de los campos
        $validated = $this->validate(); 
        $valid_username = $validated['username'];
        $valid_password = $validated['password'];
        // Variables de entorno
        $API_URL = env('AUTH_API_URL');
        $API_INFO_URL = env('AUTH_API_INFO_URL');
        $API_TOKEN = env('AUTH_API_TOKEN');
        $API_USER = env('AUTH_API_USER');
        $API_PASS = env('AUTH_API_PASSWORD');
        // Validación de usuario y contraseña
        try {
            $client = new Client();
            // Creamos el body de la petición
            $body = [
                'token' => $API_TOKEN,
                'user' => $valid_username,
                'password' => $valid_password,
                'grupo' => 'peoplecall'
            ];
            // Creamos el header auth de la petición
            $auth = [
                $API_USER,
                $API_PASS
            ];
            // Realizamos la petición
            $response = $client->post($API_URL, [
                'json' => $body,
                'auth' => $auth,
                'verify' => false,
                'timeout' => 10
            ]);
            // Decodificamos la respuesta
            $responseStatus = $response->getStatusCode();
            $reponseOk = $response->getReasonPhrase();  
            $responseBody = json_decode($response->getBody());
            // Si la respuesta es fallida
            if($responseStatus != 200 || $reponseOk != 'OK' || $responseBody->grupo != 'True'){
                return session()->flash('error', 'Usuario o contraseña incorrectos');
            }
            // Si es exitosa sacamos la información del usuario
            $body = [
                'token' => $API_TOKEN,
                'user' => $valid_username,
            ];
            $response = $client->post($API_INFO_URL, [
                'json' => $body,
                'auth' => $auth,
                'verify' => false,
                'timeout' => 10
            ]);
            // Decodificamos la respuesta
            $responseBody = json_decode($response->getBody());
            $email = $responseBody->email; 
            // Sacamos todos los grupos del usuario
            $grupos = $responseBody->grupo;
            $grupos = explode(',', $grupos);
            $isAdmin = False;
            // Si el usuario tiene el grupo de peoplecalladmin le ponemos "admin"
            if(in_array('peoplecalladmin', $grupos)){
                $isAdmin = True;
            }
            // Buscamos al usuario en la base de datos
            $user = User::where('name', $valid_username)->first();
            // Si el usuario no existe lo creamos
            if(!$user){
                $user = new User();
                $user->name = $valid_username;
                $user->password = Hash::make($valid_password);
                $user->email = $email ?? '';
                $user->is_admin = $isAdmin;
                $user->save();
            }
            // Si su contraseña es __init__ la cambiamos por la introducida
            if($user->password == '__init__'){
                $user->email = $email ?? '';
                $user->password = Hash::make($valid_password);
                $user->save();
            }
            // Iniciamos sesión y redirigimos al dashboard
            Auth::login($user);
            return $this->redirectRoute('dashboard');
        } catch (Exception $e) {
            Log::info($e);
            return session()->flash('error', 'Error en la autenticación');
        }
    }

    #[Title('Login')]
    public function render()
    {   
        return view('livewire.views.login');
    }
}
