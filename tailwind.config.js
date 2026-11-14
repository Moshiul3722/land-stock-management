/**
 * Tailwind CSS runs side by side with the existing AdminLTE / Bootstrap 4 UI.
 *
 * - `prefix: 'tw-'`     every utility is prefixed, so no class name clashes with Bootstrap.
 * - `preflight: false`  Tailwind's CSS reset is disabled, so existing pages render unchanged.
 * - `important`         utilities only apply inside an element with the `tw-root` class and
 *                       win over Bootstrap's rules there.
 *
 * New Livewire screens wrap their markup in <div class="tw-root">...</div>.
 */
/** @type {import('tailwindcss').Config} */
module.exports = {
    prefix: 'tw-',
    important: '.tw-root',
    corePlugins: {
        preflight: false,
    },
    content: [
        './resources/views/livewire/**/*.blade.php',
        './resources/views/components/ui/**/*.blade.php',
        // Blade pages that use the Tailwind look outside Livewire
        './resources/views/requisition/pending.blade.php',
        './resources/views/requisition/_decision_modal.blade.php',
        './resources/views/stock/index.blade.php',
        './resources/views/stock/issues.blade.php',
        './resources/views/stock/show.blade.php',
        './resources/views/stock/department_issues.blade.php',
        './resources/views/issued_item/index.blade.php',
        './app/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['kalpurush', 'Hind-Siliguri', 'Nunito', 'system-ui', '-apple-system', 'Segoe UI', 'sans-serif'],
            },
            colors: {
                // Brand blue, matching the AdminLTE primary used across the app.
                brand: {
                    50: '#eef5ff',
                    100: '#d9e8ff',
                    200: '#bcd7ff',
                    500: '#2f7cf6',
                    600: '#1d65db',
                    700: '#1a51b0',
                },
                // Warm accent, used sparingly (header rule, highlights).
                accent: {
                    100: '#fde7df',
                    500: '#d4532f',
                    600: '#b9411f',
                },
            },
            boxShadow: {
                surface: '0 1px 2px rgba(15, 23, 42, 0.04), 0 4px 16px rgba(15, 23, 42, 0.06)',
                dialog: '0 24px 64px rgba(15, 23, 42, 0.22), 0 2px 8px rgba(15, 23, 42, 0.08)',
            },
        },
    },
    plugins: [],
};
