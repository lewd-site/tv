const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

const scripts = ['app', 'login', 'register', 'create-room', 'room', 'add-video'];
const styles = ['app'];
const libs = ['axios', 'hls.js', 'laravel-echo', 'pusher-js', 'vue'];

scripts.forEach(script => mix.ts(`resources/ts/${script}.ts`, 'public/js'));
styles.forEach(style => mix.sass(`resources/sass/${style}.scss`, 'public/css'));
mix.extract(libs).version();
