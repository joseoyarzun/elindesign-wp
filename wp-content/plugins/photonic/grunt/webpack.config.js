const path = require('path');
const webpack = require('webpack');

// Create the entry points - one per lightbox
const entryPoints = {
	solo: {
		baguettebox: ['../include/js/front-end/src/Entries/BaguetteBox.js'],
		bigpicture: ['../include/js/front-end/src/Entries/BigPicture.js'],
		colorbox: ['../include/js/front-end/src/Entries/Colorbox.js'],
		fancybox: ['../include/js/front-end/src/Entries/Fancybox.js'],
		fancybox2: ['../include/js/front-end/src/Entries/Fancybox2.js'],
		fancybox3: ['../include/js/front-end/src/Entries/Fancybox3.js'],
		fancybox4: ['../include/js/front-end/src/Entries/Fancybox4.js'],
		featherlight: ['../include/js/front-end/src/Entries/Featherlight.js'],
		glightbox: ['../include/js/front-end/src/Entries/GLightbox.js'],
		imagelightbox: ['../include/js/front-end/src/Entries/ImageLightbox.js'],
		lightcase: ['../include/js/front-end/src/Entries/Lightcase.js'],
		lightgallery: ['../include/js/front-end/src/Entries/LightGallery.js'],
		magnific: ['../include/js/front-end/src/Entries/Magnific.js'],
		none: ['../include/js/front-end/src/Entries/None.js'],
		photoswipe: ['../include/js/front-end/src/Entries/PhotoSwipe.js'],
		photoswipe5: ['../include/js/front-end/src/Entries/PhotoSwipe5.js'],
		prettyphoto: ['../include/js/front-end/src/Entries/PrettyPhoto.js'],
		spotlight: ['../include/js/front-end/src/Entries/Spotlight.js'],
		strip: ['../include/js/front-end/src/Entries/Strip.js'],
		swipebox: ['../include/js/front-end/src/Entries/Swipebox.js'],
		thickbox: ['../include/js/front-end/src/Entries/Thickbox.js'],
		venobox: ['../include/js/front-end/src/Entries/VenoBox.js'],
	}
};

const esmEntryPoints = {
/*
	esm: {
		photoswipe5: ['../include/js/front-end/src/Entries/PhotoSwipe5.js'],
	}
*/
};

const toImport = ['baguettebox', 'photoswipe', 'spotlight'];
const toIgnore = ['none', 'fancybox2', 'thickbox'];

// Plugins
const plugins = [
	"@babel/plugin-transform-arrow-functions",
	"@babel/plugin-transform-async-to-generator",
	"@babel/plugin-transform-modules-commonjs",
	"@babel/plugin-transform-runtime",
	"@babel/plugin-proposal-class-properties",
	"@babel/plugin-syntax-class-properties"
];

const providerPlugins = {};
providerPlugins['solo'] = {};
providerPlugins['solo-slider'] = {
	Splide: '../../../../ext/splide/splide.js',
};
providerPlugins['combo'] = {
	baguetteBox: '../../../../ext/baguettebox/baguettebox.js',
	BigPicture: '../../../../ext/bigpicture/bigpicture.js',
	GLightbox: '../../../../ext/glightbox/glightbox.js',
	PhotoSwipe: '../../../../ext/photoswipe/photoswipe.js',
	PhotoSwipeUI_Default: '../../../../ext/photoswipe/photoswipe-ui-default.js',
	Spotlight: '../../../../ext/spotlight/spotlight.js',
};
providerPlugins['combo-slider'] = {... providerPlugins['combo']};
providerPlugins['combo-slider'].Splide = '../../../../ext/splide/splide.js';

const moduleTypes = [
	{
		type: 'es6',
		module: {
			rules: [{
				test: /\.js$/, // Look for any .js files.
				// exclude: /node_modules/, // Exclude the node_modules folder.
				// Use babel loader to transpile the JS files.
				use: {
					loader: 'babel-loader',
					options: {
						presets: [
							["@babel/preset-env", {
								targets: {
									esmodules: true
								},
								// useBuiltIns: "entry"
							}]
						],
						plugins: plugins
					}
				}
			}],
		},
		folder: 'out',
		target: 'es6',
	}
];

const environments = [
	{
		type: 'dev',
		mode: 'development',
		extension: '',
		// sourceMap: 'source-map',
		sourceMap: false,
	},
	{
		type: 'prod',
		mode: 'production',
		extension: '.min',
		sourceMap: false,
	}
];

/**
 * For each lightbox, we have two variants of files generated: ES6 modules, and ES5 code. This goes into 2 top level folders:
 * 	- module		: Contains files pertaining to ES6 modules
 * 	- nomodule		: Contains transpiled files for older JS versions
 *
 * Each top-level folder has 4 types of file combinations:
 * 	- solo			: Just the Photonic code, nothing else; lightbox and slider are supplied by theme / another plugin
 * 	- solo-slider	: Slider script + Photonic code; lightbox is supplied by theme / another plugin
 * 	- combo			: Lightbox script + Photonic code; slider is supplied by theme / another plugin
 * 	- combo-slider	: Lightbox script + Third party slider script + Photonic code; this is an all-inclusive bundle
 *
 * Each file combination folder has 4 files per lightbox:
 * 	- DEV + Map		: An unminified file and its sourcemap
 * 	- PROD + Map	: A minified file and its sourcemap
 */
const config = [];
Object.entries(entryPoints).forEach(([combination, entry]) => {
	moduleTypes.forEach((module) => {
		if (module.type === 'es5') {
			Object.entries(entry).forEach(([lightbox, files]) => {
				files.unshift('../include/js/front-end/src/Polyfill.js');
			});
		}

		environments.forEach((env) => {
			// Configuration object
			// 	-	entry:				Lists all entry points
			//	-	output:				Generates an output file per entry
			//	-		.filename:		[name] uses an object key from "entry" as a file name
			//	-		.path:			Specifies where the file should be written
			config.push({
				name: module.type + '-' + env.type + '-' + combination,
				mode: env.mode,
				devtool: env.sourceMap,// 'source-map',
				entry: entry,
				module: module.module,
				target: module.target,
				output: {
					filename: 'photonic-[name]' + env.extension + '.js',
					path: path.join(__dirname, '/../include/js/front-end/' + module.folder)
				},
				plugins: [
//					new webpack.ProvidePlugin(providerPlugins[combination]),
				],
				externals: {
					jquery: 'jQuery',
				},
			});
		});
	});
});

/*
Object.entries(esmEntryPoints).forEach(([combination, entry]) => {
	moduleTypes.forEach((module) => {
		if (module.type === 'es5') {
			Object.entries(entry).forEach(([lightbox, files]) => {
				files.unshift('../include/js/front-end/src/Polyfill.js');
			});
		}

		environments.forEach((env) => {
			config.push({
				name: module.type + '-' + env.type + '-' + combination,
				mode: env.mode,
				devtool: env.sourceMap,// 'source-map',
				entry: entry,
				module: module.module,
				target: module.target,
				output: {
					filename: 'photonic-[name]' + env.extension + '.js',
					path: path.join(__dirname, '/../include/js/front-end/' + module.folder),
					environment: {
						module: true,
						dynamicImport: true,
					},
				},
				experiments: {
					outputModule: true,
				},
				externalsType: 'import',
				externals: {
					'../../../../ext/photoswipe5/photoswipe5.js': 'https://unpkg.com/photoswipe',
					'../../../../ext/photoswipe5/photoswipe5-lightbox.js': 'https://unpkg.com/photoswipe/dist/photoswipe-lightbox.esm.js',
				},
			});
		});
	});
});
*/

Object.entries(esmEntryPoints).forEach(([combination, entry]) => {
	moduleTypes.forEach((module) => {
		if (module.type === 'es5') {
			Object.entries(entry).forEach(([lightbox, files]) => {
				files.unshift('../include/js/front-end/src/Polyfill.js');
			});
		}

		environments.forEach((env) => {
			config.push({
				name: module.type + '-' + env.type + '-' + combination,
				mode: env.mode,
				devtool: env.sourceMap,// 'source-map',
				entry: entry,
				module: module.module,
				target: module.target,
				output: {
					filename: 'photonic-[name]' + env.extension + '.js',
					path: path.join(__dirname, '/../include/js/front-end/' + module.folder),
				},
				resolve: {
					alias: {
						"photoswipe": '/../include/ext/photoswipe5/photoswipe5.js',
						"photoswipeLightbox": '/../include/ext/photoswipe5/photoswipe5-lightbox.js',
					}
				},
			});
		});
	});
});

// Export the config object.
module.exports = config;
