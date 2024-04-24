const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const autoprefixer = require('gulp-autoprefixer');
const concat = require('gulp-concat');
const babel = require('gulp-babel');
const uglify = require('gulp-uglify');
const debug = require('gulp-debug');

const projectDir = 'ipav-theme';
const initSCSSfile = 'style.scss';
const finalCSSfile = 'style.css';

// control vars
const control = {
    "files": {
        "styles": [
            // "_src/"+projectDir+"/mixins.scss",
            // "_src/_utilities/mixins.scss",
            "_src/"+projectDir+"/"+initSCSSfile,
        ],
        "scripts": [
            "_src/"+projectDir+"/thh183.js",
        ],
    },
    //"dist" : 'dist/'+projectDir
    "dist" : '_src/'+projectDir
};

// project tasks
const tasks = {
    "styles": () => {
        return gulp
            .src(control.files.styles)
            .pipe(debug({title: 'file:'}))
            .pipe(concat(finalCSSfile))
            .pipe(sass({outputStyle: 'compressed'}))
            .pipe(autoprefixer({cascade: false}))
            .pipe(gulp.dest(control.dist));
    },
    "scripts": () => {
        return gulp
            .src(control.files.scripts)
            //.pipe(debug({title: 'file:'}))
            //.pipe(babel({presets: ['@babel/preset-env']}))
            .pipe(uglify())
            .pipe(gulp.dest(control.dist));
    },
}


// gulp tasks
gulp.task('styles', (done) => {tasks.styles(); done()});
gulp.task('scripts', (done) => {tasks.scripts(); done()});
gulp.task('watch', () => {
    gulp.watch('_src/'+projectDir+'/*.scss', tasks.styles);
    gulp.watch('_src/**/*.js', tasks.scripts);
});
gulp.task('default', gulp.parallel('styles', 'scripts'));



