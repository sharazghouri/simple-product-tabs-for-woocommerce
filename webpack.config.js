// WordPress webpack config.
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const { CleanWebpackPlugin } = require( 'clean-webpack-plugin' );

// Utilities.
const path = require( 'path' );
console.log(defaultConfig)

defaultConfig.plugins.forEach( (p,i) => {
	if( p instanceof CleanWebpackPlugin) {
			p.cleanOnceBeforeBuildPatterns= [] // Don't clean before build
			const dirInc = ['!/assets/scss/**/*', '!/assets/admin/**/*', '!/assets/public/**/*' ];

			p.cleanAfterEveryBuildPatterns.push( ...dirInc ); // Ignore this directory after build to cleanup

			console.log( p);
		}
})
// Add any a new entry point by extending the webpack config.
module.exports = {
	...defaultConfig,
	...{
		entry: {
			...defaultConfig.entry,
			'css/public': path.resolve( process.cwd(), 'assets/scss', 'public.scss' ),
			'css/admin': path.resolve( process.cwd(), 'assets/scss/admin', 'admin.scss' ),
			'js/admin': path.resolve( process.cwd(), 'assets/admin', 'admin.js' ), // // TODO: select all files in admin directory
			'js/public': path.resolve( process.cwd(), 'assets/public', 'public.js' ), // TODO: select all files in public directory
			
		},
		output: {
			
			path: path.resolve( process.cwd(), 'assets' ),
		},
		plugins: [
			// Include WP's plugin config.
			...defaultConfig.plugins,

			// new CleanWebpackPlugin( {
			// 	cleanAfterEveryBuildPatterns: [ '!fonts/**', '!images/**', '!/assets/scss/**/*', '!admin/**', '!public/**' ],
			// 	// Prevent it from deleting webpack assets during builds that have
			// 	// multiple configurations returned in the webpack config.
			// 	cleanStaleWebpackAssets: false,
			// } ),
		]
	}
};