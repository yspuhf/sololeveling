import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                obsidian: {
                    DEFAULT: 'var(--obsidian-default)',
                    dark: 'var(--obsidian-dark)',
                    light: 'var(--obsidian-light)',
                    card: 'var(--obsidian-card)',
                },
                neon: {
                    blue: '#45f3ff',
                    purple: '#8a2be2',
                    blueglow: '#00d2ff',
                },
                gold: {
                    rpg: '#ffd700',
                }
            },
            boxShadow: {
                'neon-blue': '0 0 15px rgba(69, 243, 255, 0.4)',
                'neon-purple': '0 0 15px rgba(138, 43, 226, 0.4)',
                'neon-gold': '0 0 15px rgba(255, 215, 0, 0.4)',
            }
        },
    },

    plugins: [forms],
};

