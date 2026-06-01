const path = require('path');
const webpack = require('webpack');
const dotenv = require('dotenv');

// 🔥 Detectar entorno
const envFile = process.env.NODE_ENV === 'production' ? '.env.production' : '.env.local';

// 🔥 Cargar variables
const env = dotenv.config({ path: envFile }).parsed || {};

// 🔥 Convertir a DefinePlugin format
const envKeys = Object.keys(env).reduce((acc, key) => {
  acc[`process.env.${key}`] = JSON.stringify(env[key]);
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

  plugins: [
    // 🔥 INYECTA VARIABLES DE ENTORNO EN FRONTEND
    new webpack.DefinePlugin(envKeys),
  ],
  

  devtool: false,
};
