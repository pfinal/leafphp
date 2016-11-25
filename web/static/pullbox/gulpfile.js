var gulp = require('gulp');
var rename = require('gulp-rename');
var uglify = require('gulp-uglify');

var minFiles = ['pull-refresh.js'];

gulp.task('minjs', function () {
	// place code for your default task here
	return gulp.src(minFiles)
		.pipe(rename({suffix: '.min'}))
		.pipe(uglify())
		.pipe(gulp.dest('./'))
});

gulp.task('default', function () {
	gulp.watch(minFiles, ['minjs']);
});
