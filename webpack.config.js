const TerserPlugin = require( 'terser-webpack-plugin' );

module.exports = {
	mode: 'production',
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /(node_modules)/,
				use: {
					loader: 'babel-loader',
					options: {
						presets: [ '@babel/preset-env' ],
						plugins: [ '@babel/plugin-transform-react-jsx' ],
					},
				},
			},
		],
	},
	optimization: {
		minimize: true,
		minimizer: [ new TerserPlugin( {
			terserOptions: {},
			extractComments: {
				condition: ( node, comment ) => {
					return /@/.test( comment.value );
				},
			},
		} ) ],
	},
	devtool: 'source-map',
};
