const Path = require('path');
const webpackConfig = require('@silverstripe/webpack-config');
const {
  resolveJS,
  externalJS,
  moduleJS,
  pluginJS,
  moduleCSS,
  pluginCSS,
} = webpackConfig;

const ENV = process.env.NODE_ENV;
const PATHS = {
  MODULES: 'node_modules',
  FILES_PATH: '../',
  ROOT: Path.resolve(),
  SRC: Path.resolve('client/src'),
  DIST: Path.resolve('client/dist'),
};

const externals = externalJS(ENV, PATHS);
delete externals.reactstrap;

const config = [
  {
    name: 'js',
    entry: {
      bundle: `${PATHS.SRC}/bundles/bundle.js`,
    },
    output: {
      path: PATHS.DIST,
      filename: 'js/[name].js',
    },
    resolve: resolveJS(ENV, PATHS),
    externals,
    module: moduleJS(ENV, PATHS),
    plugins: pluginJS(ENV, PATHS)
  },
  {
    name: 'css',
    entry: {
      bundle: `${PATHS.SRC}/bundles/bundle.scss`,
    },
    output: {
      path: PATHS.DIST
    },
    module: moduleCSS(ENV, PATHS),
    plugins: pluginCSS(ENV, PATHS, 'styles/[name].css'),
  },
];

// Use WEBPACK_CHILD=js or WEBPACK_CHILD=css env var to run a single config
module.exports = (process.env.WEBPACK_CHILD)
  ? config.find((entry) => entry.name === process.env.WEBPACK_CHILD)
  : module.exports = config;
