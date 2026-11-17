import { src, dest, watch, series, parallel } from 'gulp'
import * as dartSass from 'sass'
import gulpSass from 'gulp-sass'

const sass = gulpSass(dartSass)

const rutas = {
  scss: 'src/scss/**/*.scss',
  js: 'src/js/**/*.js',
  img: ['src/img/**/*', 'src/icons/**/*', 'src/logo/**/*'],
}

export function css() {
  return src('src/scss/app.scss', { sourcemaps: true })
    .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
    .pipe(dest('public/build/css', { sourcemaps: '.' }))
}

export function js() {
  return src(rutas.js).pipe(dest('public/build/js'))
}

const copiar = (origen, destino) => () =>
  src(origen, { encoding: false }).pipe(dest(destino))

// src/img -> build/img, src/icons -> build/img/icons, src/logo -> build/img/logo
export const imagenes = parallel(
  copiar('src/img/**/*', 'public/build/img'),
  copiar('src/icons/**/*', 'public/build/img/icons'),
  copiar('src/logo/**/*', 'public/build/img/logo'),
)

export function dev() {
  watch(rutas.scss, css)
  watch(rutas.js, js)
  watch(rutas.img, imagenes)
}

export const build = parallel(css, js, imagenes)

export default series(build, dev)
