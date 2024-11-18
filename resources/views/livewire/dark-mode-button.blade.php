<button
    id="dark-mode-button"
    class="flex size-9 items-center justify-center rounded-full bg-ctp-mantle text-ctp-subtext0"
>
    <x-heroicon-s-moon class="size-5" />
</button>
<script>
    const darkModeButton = document.querySelector('#dark-mode-button');
    const currentTheme = localStorage.getItem('theme');

    if (currentTheme) {
        document.body.classList.add(currentTheme);
    } else {
        localStorage.setItem('theme', 'light');
    }

    darkModeButton.addEventListener('click', () => {
        if (document.body.classList.contains('dark')) {
            document.body.classList.remove('dark');
            localStorage.setItem('theme', 'light');
            return;
        } else {
            document.body.classList.add('dark');
            localStorage.setItem('theme', 'dark');
            return;
        }
    });
</script>
