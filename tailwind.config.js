/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./assets/**/*.js",
        "./templates/**/*.html.twig",
    ],
    theme: {
        extend: {},
    },
    plugins: [
        require("daisyui")
    ],
    daisyui: {
        themes: [
            {
                app_light: {
                    "primary": "#326941",
                    "secondary": "#506352",
                    "accent": "#3a656e",
                    "neutral": "#181d18",
                    "base-100": "#f6fbf2",
                    "base-200": "#dde5da",
                    "base-300": "#dfe4dc",
                    "error": "#ba1a1a",
                    "info": "#0284c7",
                    "success": "#16a34a",
                    "warning": "#d97706",
                },

                app_dark: {
                    "primary": "#99d4a2",
                    "secondary": "#b7ccb7",
                    "accent": "#a2ced8",
                    "neutral": "#dfe4dc",
                    "base-100": "#101510",
                    "base-200": "#414941",
                    "base-300": "#313631",
                    "error": "#ffb4ab",
                    "info": "#38bdf8",
                    "success": "#4ade80",
                    "warning": "#fbbd23",
                },
            },
        ],
        darkTheme: "app_dark",
    },
}
