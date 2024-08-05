<section>
    <livewire:header />
    <section class="mx-auto w-full max-w-5xl px-10">
        <livewire:add-users-modal />
        <section class="mt-5 flex w-full flex-col gap-2.5">
            @php
                $allUsers = App\Models\User::all();
            @endphp

            @foreach ($allUsers as $user)
                <article class="flex w-full flex-col items-center justify-between rounded-md bg-ctp-mantle md:flex-row">
                    <aside
                        class="flex h-auto flex-col items-center gap-1.5 px-5 py-2.5 sm:h-12 sm:flex-row sm:gap-5 sm:py-0"
                    >
                        <p class="w-fit sm:w-64">{{ $user->name }}</p>
                        <p class="truncate rounded-md bg-ctp-crust px-5 py-1.5 text-xs text-ctp-subtext0">
                            <span class="text-ctp-blue">
                                {{ $user->monthly_requests === -1 ? 'infinitas' : $user->monthly_requests }}
                            </span>
                            solicitudes mensuales
                        </p>
                    </aside>
                    <aside class="flex items-center gap-2.5">
                        <livewire:edit-users-modal
                            username="{{ $user->name }}"
                            requests="{{ $user->monthly_requests }}"
                            user_id="{{ $user->id }}"
                        />
                        <button
                            wire:click="delete({{ $user->id }})"
                            class="flex size-9 items-center justify-center rounded-md text-ctp-maroon"
                        >
                            <x-heroicon-o-trash wire:loading.remove class="size-5" />
                            <x-tabler-loader-2 wire:loading class="size-5 animate-spin" />
                        </button>
                    </aside>
                </article>
            @endforeach
        </section>
    </section>
</section>
