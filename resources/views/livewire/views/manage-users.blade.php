<section>
    <livewire:header />
    <section class="mx-auto w-full max-w-5xl px-10">
        <livewire:add-users-modal />
        <section class="mt-5 flex w-full flex-col gap-2.5">
            @php
            $allUsers = App\Models\User::all();
            @endphp
            @foreach ($allUsers as $user)
            <section wire:key="{{$user->id}}" class="flex flex-col bg-ctp-mantle rounded-md">
                <article class="flex w-full flex-col items-center justify-between md:flex-row">
                    <aside
                        class="flex h-auto flex-col items-center gap-1.5 px-5 py-2.5 sm:h-12 sm:flex-row sm:gap-5 sm:py-0">
                        <p class="w-fit sm:w-64">{{ $user->name }}</p>
                        <p class="truncate rounded-md bg-ctp-crust px-5 py-1.5 text-xs text-ctp-subtext0">
                            <span class="text-ctp-blue">
                                {{ $user->monthly_requests === -1 ? 'infinitas' : $user->monthly_requests }}
                            </span>
                            solicitudes mensuales
                        </p>
                    </aside>
                    <aside class="flex items-center gap-2.5">
                        <button
                            wire:click="getFiles({{ $user->id }})"
                            class="flex size-9 items-center justify-center rounded-mdtext-ctp-blue">
                            <x-heroicon-o-folder wire:loading.remove class="size-5" />
                            <x-tabler-loader-2 wire:loading class="size-5 animate-spin" />
                        </button>
                        <livewire:edit-users-modal
                            wire:key="{{ $user->id }}"
                            username="{{ $user->name }}"
                            requests="{{ $user->monthly_requests }}"
                            user_id="{{ $user->id }}" />
                        <button
                            wire:click="delete({{ $user->id }})"
                            class="flex size-9 items-center justify-center rounded-md text-ctp-maroon">
                            <x-heroicon-o-trash wire:loading.remove class="size-5" />
                            <x-tabler-loader-2 wire:loading class="size-5 animate-spin" />
                        </button>
                    </aside>
                </article>
                @if ($currentUserId === $user->id)
                <section class="flex flex-row flex-wrap gap-x-5 gap-y-2.5 w-full items-start px-5 py-2.5">
                    @if (empty($files))
                    <p class="text-xs">No hay archivos disponibles</p>
                    @else
                    @foreach ($files as $file)
                    @php
                    $name = $user->name;
                    $path = 'download/'.$name.'/csv/'.$file;
                    @endphp
                    <aside class="sm:mx-0 mx-auto flex w-full max-w-72 items-center justify-between rounded-md bg-ctp-crust p-2.5 text-ctp-subtext0">
                        <p class="text-xs">
                            {{$file}}
                        </p>
                        <a href="{{$path}}" class="text-ctp-blue">
                            <x-heroicon-o-arrow-down-on-square-stack class="size-5" />
                        </a>
                    </aside>
                    @endforeach
                    @endif
                </section>
                @endif
            </section>
            @endforeach
        </section>
    </section>
</section>