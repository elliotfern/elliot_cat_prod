const path = require('path');

module.exports = (env, argv) => {
  return {
    entry: './src/frontend/main.ts',

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
          use: 'ts-loader',
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
      },
    },

    devtool: false,
  };
};
