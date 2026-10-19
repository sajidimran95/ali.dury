module.exports = {
    content: [
        './Modules/Ecommerce/Resources/views/front/**/*.blade.php',
    ],
    corePlugins: {
        preflight: false,
    },
    theme: {
        extend: {
            colors: {
                brand: '#dd772d',
                brandDark: '#c4621f',
                ink: '#060606',
                slatebar: '#ffffff',
                mist: '#f5f5f5',
                line: '#e6e6e6',
            },
            fontFamily: {
                sans: ['Montserrat', 'Poppins', 'Roboto', 'system-ui', 'sans-serif'],
                serif: ['Roboto', 'Montserrat', 'sans-serif'],
            },
        },
    },
};
