const mix = require('laravel-mix');
require('laravel-mix-polyfill');

// Use external jQuery.
const externals = { jquery: 'jQuery' };

mix
  .webpackConfig({ externals })
  .js('assets/src/js/app.js', 'assets/dist/js')
  .polyfill({
    enabled: true,
    useBuiltIns: 'usage',
  });
