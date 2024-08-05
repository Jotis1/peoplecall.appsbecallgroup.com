<section>
    <section class="mt-5 flex items-center justify-between">
        <h1 class="text-lg sm:text-xl">Usuarios</h1>
        <button wire:click="toggleModal" class="flex size-9 items-center justify-center rounded-md bg-ctp-crust">
            <x-heroicon-o-plus wire:loading.remove class="size-5" />
            <x-tabler-loader-2 wire:loading class="size-5 animate-spin" />
        </button>
    </section>
    @if ($showModal)
        <section
            class="absolute left-0 top-0 flex h-dvh w-dvw items-center justify-center bg-ctp-mantle/60 backdrop-blur-sm"
        >
            <form wire:submit="save" class="flex w-full max-w-lg flex-col gap-10 rounded-md bg-ctp-base p-10">
                @error('name')
                    <span class="text-center text-sm text-ctp-red">{{ $message }}</span>
                @enderror

                @error('requests')
                    <span class="text-center text-sm text-ctp-red">{{ $message }}</span>
                @enderror

                <header class="flex items-center justify-between gap-5">
                    <p>Agregando usuario</p>
                    <button
                        type="button"
                        wire:click="toggleModal"
                        class="flex size-9 items-center justify-center rounded-md text-ctp-maroon"
                    >
                        <x-heroicon-o-x-mark wire:loading.remove class="size-5" />
                        <x-tabler-loader-2 wire:loading class="size-5 animate-spin" />
                    </button>
                </header>
                <section class="flex w-full flex-col gap-5">
                    <input
                        placeholder="Nombre de usuario"
                        type="text"
                        wire:model="name"
                        class="h-10 rounded-md bg-ctp-mantle px-5 outline-none placeholder:text-ctp-subtext0"
                    />
                    <input
                        placeholder="Número de solicitudes"
                        type="number"
                        wire:model="requests"
                        class="h-10 appearance-none rounded-md bg-ctp-mantle px-5 outline-none placeholder:text-ctp-subtext0"
                    />
                </section>
                <button
                    type="submit"
                    class="flex h-10 w-fit items-center justify-center rounded-md bg-ctp-blue p-5 px-5 text-center text-ctp-crust"
                >
                    <p wire:loading.remove>Continuar</p>
                    <x-tabler-loader-2 wire:loading class="size-5 animate-spin" />
                </button>
            </form>
        </section>
    @endif
</section>
