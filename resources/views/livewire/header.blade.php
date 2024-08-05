<section class="border-b border-ctp-surface2">
    <header
        class="mx-auto flex w-full max-w-5xl flex-col items-center justify-between gap-5 p-10 text-center sm:flex-row sm:text-start"
    >
        <section class="flex flex-col gap-2.5">
            <a href="{{ route('dashboard') }}" class="text-2xl font-bold sm:text-3xl">PeopleCall</a>
            <p class="text-ctp-subtext0">
                Autenticado como
                <strong class="font-medium text-ctp-text">{{ auth()->user()->name }}</strong>
            </p>
        </section>
        <section class="flex gap-5">
            <livewire:dark-mode-button />
            <livewire:logout-button />
        </section>
    </header>
</section>
