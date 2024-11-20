<div>
    @php
        use App\Models\User;
        $user = User::find($userId);
    @endphp

    <p>Felicidades {{ $user->name }}, el procesamiento del archivo ha finalizado con éxito.</p>
    <p>
        Tiene
        {{ $user->monthly_requests === -1 ? 'ilimitadas' : $user->monthly_requests - $user->executed_requests }}
        solicitudes restantes.
    </p>
    <a href="https://peoplecall.appsbecallgroup.com/{{ $path }}" class="text-blue-500">
        Pulse aquí para descargar el archivo
    </a>
</div>
