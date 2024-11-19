<section class="flex flex-row flex-wrap gap-x-5 gap-y-2.5 w-full items-start max-w-5xl">
    @foreach ($files as $file)
    <aside class="sm:mx-0 mx-auto flex w-full max-w-72 items-center justify-between rounded-md bg-ctp-mantle p-2.5 text-ctp-subtext0">
        <p class="text-xs">
            {{$file->name}}
        </p>
        @if ($file->downloading)
            <x-tabler-loader-2 class="size-5 animate-spin" />
        @endif
        <a href="download/{{$file->id}}" class="text-ctp-blue">
            <x-heroicon-o-arrow-down-on-square-stack class="size-5" />
        </a>
    </aside>
    @endforeach
    @if (count($files) == 0)
    <p class="text-ctp-subtext0 text-xs">No hay archivos procesados</p>
    @endif
</section>  