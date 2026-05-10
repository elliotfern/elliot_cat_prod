import path from 'path';
import { fileURLToPath } from 'url';

// Obtener el directorio actual
const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default (env, argv) => {
  const isProd = argv.mode === 'production';

  return {
    entry: './src/frontend/main.ts', // Punt d'entrada principal
    output: {
      filename: '[name].js',
      path: path.resolve(__dirname, 'dist'),
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
          use: {
            loader: 'ts-loader',
            options: {
              compilerOptions: {
                sourceMap: false,
              },
            },
          },
        },
        {
          test: /\.css$/,
          use: ['style-loader', 'css-loader'],
        },
      ],
    },
    optimization: {
      splitChunks: {
        chunks: 'all',
        cacheGroups: {
          vendors: {
            test: /[\\/]node_modules[\\/]/,
            name: 'vendors',
            chunks: 'all',
          },
        },
      },
    },

    devtool: false,
  };
};
