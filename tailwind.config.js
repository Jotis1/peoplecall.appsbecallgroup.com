/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'selector',
    content: ['./resources/**/*.blade.php', './resources/**/*.js', './resources/**/*.vue'],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Space Grotesk', 'sans-serif'],
            },
        },
    },
    plugins: [
        require('@catppuccin/tailwindcss')({
            prefix: 'ctp',
            defaultFlavour: 'latte',
        }),
    ],
};
