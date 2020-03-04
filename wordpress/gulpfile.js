var gulp = require('gulp');
var sass = require('gulp-sass');
// gulp.task('sass', function () {
//   // pretty sure these dont do anything other than using them the first time to create the initial css files.
//   return gulp.src('wp-content/themes/photography/styles/gallery-styles.scss')
//     .pipe(sass().on('error', sass.logError))
//     .pipe(gulp.dest('wp-content/themes/photography/styles/compiled'));
//     // pretty sure these dont do anything other than using them the first time to create the initial css files.
//   // return gulp.src('wp-content/themes/photography/styles/styles-test.scss')
//   //   .pipe(sass().on('error', sass.logError))
//   //   .pipe(gulp.dest('wp-content/themes/photography/styles/compiled'));
// });
 
gulp.task('sass', function() {
  return gulp.src('wp-content/themes/dicks/styles/**/*.scss') // Gets all files ending with .scss in app/scss and children dirs
    .pipe(sass())
    .pipe(gulp.dest('wp-content/themes/dicks/styles/compiled'))
});

gulp.task('sass:watch', function () {
  gulp.watch('wp-content/themes/dicks/styles/**/*.scss', gulp.series(['sass']));
}); 