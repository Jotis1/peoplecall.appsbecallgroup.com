<section>
    <livewire:header />
    <section class="mx-auto w-full max-w-5xl px-10">
        <livewire:add-ips-modal />
        <section class="mt-5 flex w-full flex-col gap-2.5">
            @php
                $allIps = App\Models\IpWhitelist::all();
            @endphp

            @foreach ($allIps as $ip)
                <article class="flex w-full items-center justify-between rounded-md bg-ctp-mantle">
                    <aside class="flex h-12 items-center gap-5 px-5">
                        <p>{{ $ip->ip }}</p>
                    </aside>
                    <aside class="flex items-center gap-2.5">
                        <livewire:edit-ips-modal ip="{{ $ip->ip }}" id="{{ $ip->id }}" />
                        <button
                            wire:click="delete({{ $ip->id }})"
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
