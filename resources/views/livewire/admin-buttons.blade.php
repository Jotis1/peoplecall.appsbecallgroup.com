<div>
    @if (auth()->user()->is_admin)
        <section class="mt-5 flex flex-col items-center gap-5 text-center sm:items-start sm:text-start">
            <button wire:click="toggleOptions" class="flex h-9 w-fit items-center gap-2.5 rounded-md bg-ctp-mantle px-5">
                Opciones de administrador
                <x-heroicon-o-cog-8-tooth wire:loading.remove class="size-5" />
                <x-tabler-loader-2 wire:loading class="size-5 animate-spin" />
            </button>
            @if ($isOpen)
                <section
                    class="flex flex-col items-center *:flex *:h-9 *:items-center *:gap-2.5 *:text-ctp-blue *:decoration-ctp-blue *:underline-offset-2 sm:items-start"
                >
                    <a href="{{ route('manage-users') }}" class="w-fit hover:underline">
                        Administrar usuarios
                        <x-heroicon-o-user-group class="size-5" />
                    </a>
                    <a href="{{ route('manage-ips') }}" class="w-fit hover:underline">
                        Administrar IPs
                        <x-heroicon-o-map-pin class="size-5" />
                    </a>
                </section>
            @endif
        </section>
    @endif
</div>
