<section>
    <livewire:header />
    <main class="mx-auto w-full max-w-5xl px-10">
        <livewire:admin-buttons />
        <section class="mt-5 flex flex-col items-center gap-5 sm:items-start">
            <h1 class="text-lg sm:text-xl">Métricas</h1>
            <aside class="flex flex-wrap items-center gap-5">
                <article class="mx-auto flex h-40 min-w-72 flex-col items-center justify-around gap-2.5 rounded-md bg-ctp-mantle p-5">
                    <p class="text-ctp-subtext0">Peticiones realizadas</p>
                    <p class="text-4xl font-bold sm:text-5xl">{{ auth()->user()->executed_requests }}</p>
                    <p class="text-sm text-ctp-subtext0">este mes</p>
                </article>
                <article class="mx-auto flex h-40 min-w-72 flex-col items-center justify-around gap-2.5 rounded-md bg-ctp-mantle p-5">
                    <p class="text-ctp-subtext0">Peticiones disponibles</p>
                    <p class="text-4xl font-bold sm:text-5xl">
                        {{ auth()->user()->monthly_requests === -1 ? '∞' : auth()->user()->monthly_requests }}
                    </p>
                    <p class="text-sm text-ctp-subtext0">este mes</p>
                </article>
                <article class="mx-auto flex h-40 min-w-72 flex-col items-center justify-around gap-2.5 rounded-md bg-ctp-mantle p-5">
                    <p class="text-ctp-subtext0">Peticiones restantes</p>
                    <p class="text-4xl font-bold sm:text-5xl">
                        {{ auth()->user()->monthly_requests === -1 ? '∞' : auth()->user()->monthly_requests - auth()->user()->executed_requests }}
                    </p>
                    <p class="text-sm text-ctp-subtext0">este mes</p>
                </article>
            </aside>
        </section>
        <section class="mt-5 flex flex-col items-center gap-5 sm:items-start">
            <h1 class="text-lg sm:text-xl">Subida de archivos</h1>
            @if (@session('success'))
            <p class="text-sm text-ctp-green">{{ session('success') }}</p>
            @endif
            @if ($isRunningJob)
            <section wire:poll='refreshIsRunningJob' class="w-72 rounded-md bg-ctp-mantle p-5">
                <p class="text-xs font-medium">
                    Tu archivo se está procesando en segundo plano. Te enviaremos un correo cuando esté listo.
                </p>
            </section>
            @else
            <form action="post-csv" method="POST" class="flex flex-col gap-5" enctype="multipart/form-data">
                @csrf
                @if (@session('error'))
                <p class="text-sm text-ctp-maroon">{{ session('error') }}</p>
                @endif
                @error('csv')
                <p class="text-sm text-ctp-maroon">{{ $message }}</p>
                @enderror
                <aside class="relative flex h-40 w-full max-w-80 items-center justify-center rounded-md border-2 border-dashed border-ctp-blue bg-ctp-mantle p-5 text-center text-ctp-subtext0">
                    <p class="text-xs">Haz click o arrastra aquí tus archivos CSV</p>
                    <input id="csv" name="csv" type="file" class="absolute size-full cursor-pointer opacity-0" accept=".csv" />
                </aside>
                <section id="file-section" class="hidden w-full max-w-80 items-center justify-between rounded-md bg-ctp-mantle px-5 py-2 text-ctp-blue">
                    <p>Archivo.csv</p>
                    <button type="button" class="size-5 text-ctp-maroon" id="remove-file">
                        <x-heroicon-s-x-mark class="size-5" />
                    </button>
                </section>
                <button type="submit" id="submit-button" class="hidden h-10 w-fit items-center justify-center rounded-md bg-ctp-blue p-5 px-5 text-center text-ctp-crust">
                    <p id="submit-text">Subir archivo</p>
                    <x-tabler-loader-2 id="submit-loader" class="hidden size-5 animate-spin" />
                </button>
            </form>
            @endif
        </section>
        <section class="my-5 flex flex-col items-center gap-5 sm:items-start">
            <h1 class="text-lg sm:text-xl">Mis archivos</h1>
            <livewire:processed-files wire:poll />
        </section>
    </main>
</section>
<script>
    const fileInput = document.querySelector('input[type=file]');
    const fileSection = document.getElementById('file-section');
    const submitButton = document.getElementById('submit-button');
    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        fileSection.classList.remove('hidden');
        fileSection.classList.add('flex');
        submitButton.classList.remove('hidden');
        submitButton.classList.add('flex');
        fileSection.children[0].textContent = file.name;
    });
    const removeFile = document.getElementById('remove-file');
    removeFile.addEventListener('click', () => {
        fileInput.value = '';
        fileSection.classList.add('hidden');
        fileSection.classList.remove('flex');
        submitButton.classList.add('hidden');
        submitButton.classList.remove('flex');
        fileButton.classList.remove('hidden');
        fileButton.classList.add('flex');
    });
    submitButton.addEventListener('click', () => {
        const submitText = document.getElementById('submit-text');
        const submitLoader = document.getElementById('submit-loader');
        submitText.classList.add('hidden');
        submitLoader.classList.remove('hidden');
        document.getElementById('submit-button').submit();
        submitButton.disabled = true;
    });
</script>