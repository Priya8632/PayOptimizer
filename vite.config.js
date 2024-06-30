// import { defineConfig } from 'vite';
// import laravel from 'laravel-vite-plugin';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/css/app.css', 'resources/js/app.js'],
//             refresh: true,
//         }),
//     ],
// });


// vite.config.js
import svgLoader from 'vite-svg-loader';

export default defineConfig({
  plugins: [
    svgLoader(),
  ],

});
