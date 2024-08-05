<button
    id="dark-mode-button"
    class="flex size-9 items-center justify-center rounded-full bg-ctp-crust text-ctp-subtext0"
>
    <x-heroicon-s-moon class="size-5" />
</button>
<script>
    const darkModeButton = document.querySelector('#dark-mode-button');
    const currentTheme = localStorage.getItem('theme');

    if (currentTheme) {
        document.body.classList.add(currentTheme);
    } else {
        document.body.classList.add('ctp-latte');
        localStorage.setItem('theme', 'ctp-latte');
    }

    darkModeButton.addEventListener('click', () => {
        if (document.body.classList.contains('ctp-latte')) {
            document.body.classList.remove('ctp-latte');
            document.body.classList.add('ctp-mocha');
            localStorage.setItem('theme', 'ctp-mocha');
            return;
        } else if (document.body.classList.contains('ctp-mocha')) {
            document.body.classList.remove('ctp-mocha');
            document.body.classList.add('ctp-latte');
            localStorage.setItem('theme', 'ctp-latte');
            return;
        }
    });
</script>
