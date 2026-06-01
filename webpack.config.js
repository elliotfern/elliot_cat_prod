const path = require('path');
const webpack = require('webpack');
const dotenv = require('dotenv');

const envFile = process.env.NODE_ENV === 'production' ? '.env.production' : '.env.local';

const result = dotenv.config({ path: envFile });

if (result.error) {
  console.warn(`⚠️ Missing env file: ${envFile}`);
}

const env = {
  API_BASE: '',
  ...result.parsed,
};

const envKeys = Object.keys(env).reduce((acc, key) => {
  acc[`process.env.${key}`] = JSON.stringify(env[key] ?? '');
  return acc;
}, {});

module.exports = {
  entry: './src/frontend/main.ts',

  output: {
    path: path.resolve(__dirname, 'public/dist'),
    filename: 'bundle.js',
    publicPath: '/dist/',
    clean: true,
  },

  resolve: {
    extensions: ['.ts', '.js'],
  },

  module: {
    rules: [
      {
        test: /\.ts$/,
        exclude: /node_modules/,
        use: 'ts-loader',
      },
      {
        test: /\.css$/,
        use: ['style-loader', 'css-loader'],
      },
    ],
  },

  plugins: [new webpack.DefinePlugin(envKeys)],

  devtool: false,
};
