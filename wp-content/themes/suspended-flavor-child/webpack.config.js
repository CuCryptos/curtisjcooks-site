const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        // Main homepage React app
        index: path.resolve(__dirname, 'src/index.js'),
        // Recipe frontend bundle
        recipe: path.resolve(__dirname, 'src/recipe.js'),
        // Recipe Gutenberg editor bundle
        'recipe-editor': path.resolve(__dirname, 'src/recipe-editor.js'),
    },
    externals: {
        // Don't treat React as external for frontend bundles
        // The editor bundle will use WordPress's React
    },
};
