<button
    type="button"
    wire:click="logout"
    class="flex h-9 items-center justify-center gap-2.5 rounded-full bg-ctp-crust px-5 text-sm text-ctp-maroon"
>
    Cerrar sesión
    <x-tabler-logout wire:loading.remove class="size-4" />
    <x-tabler-loader-2 wire:loading class="size-5 animate-spin" />
</button>
