<section class="flex flex-row flex-wrap gap-x-5 gap-y-2.5 w-full items-start max-w-5xl">
    @foreach ($files as $file)
    @php
    $user = auth()->user();
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
</section>