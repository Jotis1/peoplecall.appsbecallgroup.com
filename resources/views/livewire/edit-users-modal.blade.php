<section>
    <button wire:click="toggleModal" class="flex size-9 items-center justify-center rounded-md">
        <x-heroicon-o-pencil wire:loading.remove class="size-5" />
        <x-tabler-loader-2 wire:loading class="size-5 animate-spin" />
    </button>
    @if ($showModal)
        <section
            class="absolute left-0 top-0 flex h-dvh w-dvw items-center justify-center bg-ctp-base/60 backdrop-blur-sm"
        >
            <form wire:submit="save" class="flex w-full max-w-lg flex-col gap-10 rounded-md bg-white dark:bg-ctp-mantle  p-10">
                @error('user_id')
                    <span class="text-center text-sm text-ctp-red">{{ $message }}</span>
                @enderror

                @error('requests')
                    <span class="text-center text-sm text-ctp-red">{{ $message }}</span>
                @enderror

                <header class="flex items-center justify-between gap-5">
                    <header>
                        <p>Editando usuario {{ $username }}</p>
                        <span class="text-xs text-ctp-subtext0">* -1 equivale a infinitas solicitudes</span>
                    </header>
                    <button
                        type="button"
                        wire:click="toggleModal"
                        class="flex size-9 items-center justify-center rounded-md text-ctp-maroon"
                    >
                        <x-heroicon-o-x-mark
                        wire:loading.remove
                        wire:target="toggleModal"
                        class="size-5" />
                        <x-tabler-loader-2
                        wire:loading
                        wire:target="toggleModal"
                        class="size-5 animate-spin" />
                    </button>
                </header>
                <section class="flex w-full flex-col gap-5">
                    <input
                        value="{{ $requests }}"
                        placeholder="Número de solicitudes"
                        type="number"
                        wire:model="requests"
                        class="h-10 appearance-none rounded-md bg-ctp-base px-5 outline-none placeholder:text-ctp-subtext0"
                    />
                </section>
                <button
                    type="submit"
                    class="flex h-10 w-fit items-center justify-center rounded-md bg-ctp-blue p-5 px-5 text-center text-ctp-mantle"
                >
                    <p
                    wire:loading.remove
                    wire:target="save">Continuar</p>
                    <x-tabler-loader-2
                    wire:loading
                    wire:target="save"
                    class="size-5 animate-spin"
                    />
                </button>
            </form>
        </section>
    @endif
</section>
