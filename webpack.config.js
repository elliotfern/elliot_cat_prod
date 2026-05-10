import path from 'path';
import { fileURLToPath } from 'url';

// Obtener el directorio actual
const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default {
  entry: './src/frontend/main.ts', // Punt d'entrada principal
  output: {
    filename: 'bundle.js',
    path: path.resolve(__dirname, 'dist'),
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
  scripts: {
    dev: 'webpack --mode development',
    build: 'webpack --mode production',
  },
  devtool: 'source-map',
};
