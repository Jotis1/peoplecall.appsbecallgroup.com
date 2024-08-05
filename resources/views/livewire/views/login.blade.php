<main class="flex h-dvh items-center justify-center">
    <section class="absolute right-0 top-0 p-10">
        <livewire:dark-mode-button />
    </section>
    <form
        class="flex w-full max-w-lg flex-col gap-10 rounded-md border-2 border-ctp-crust bg-ctp-mantle p-5"
        wire:submit="login"
    >
        <header class="flex flex-col items-center gap-2.5">
            <h1 class="text-3xl font-bold">PeopleCall</h1>
            <p class="text-ctp-subtext0">Inicia sesión para continuar</p>
        </header>
        @if (@session('error'))
            <p class="text-center text-sm text-ctp-red">{{ session('error') }}</p>
        @endif

        @error('password')
            <span class="text-center text-sm text-ctp-red">{{ $message }}</span>
        @enderror

        @error('username')
            <span class="text-center text-sm text-ctp-red">{{ $message }}</span>
        @enderror

        @if (@session('success'))
            <span class="text-center text-sm text-ctp-green">{{ session('success') }}</span>
        @endif

        <section class="flex flex-col gap-5">
            <input
                type="text"
                placeholder="Nombre de usuario"
                wire:model="username"
                class="h-10 rounded-md bg-ctp-base px-5 outline-none placeholder:text-ctp-subtext0"
            />
            <section class="flex h-10 w-full justify-between">
                <input
                    id="password"
                    placeholder="Contraseña"
                    class="h-10 w-full rounded-l-md bg-ctp-base px-5 outline-none placeholder:text-ctp-subtext0"
                    type="password"
                    wire:model="password"
                />
                <button
                    type="button"
                    id="show-password"
                    class="flex size-10 items-center justify-center rounded-r-md bg-ctp-base"
                >
                    <x-heroicon-s-eye class="h-5 text-ctp-text" />
                </button>
            </section>
        </section>
        <button type="submit" class="flex h-10 w-full items-center justify-center rounded-md bg-ctp-crust">
            <p wire:loading.remove>Iniciar sesión</p>
            <p wire:loading>
                <x-tabler-loader-2 class="size-5 animate-spin" />
            </p>
        </button>
    </form>
</main>
<script>
    const showPassword = document.getElementById('show-password');
    const password = document.getElementById('password');
    showPassword.addEventListener('click', () => {
        if (password.type === 'password') {
            password.type = 'text';
        } else {
            password.type = 'password';
        }
    });
</script>
