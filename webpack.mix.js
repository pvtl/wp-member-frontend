const mix = require('laravel-mix');

mix.setPublicPath('.')
  .webpackConfig({ externals: { jquery: 'jQuery' } })
  .js('assets/src/js/app.js', 'assets/dist/js')
  .sourceMaps()
  .version();
