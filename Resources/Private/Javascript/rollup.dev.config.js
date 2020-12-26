import resolve from '@rollup/plugin-node-resolve';
import commonJS from '@rollup/plugin-commonjs';
import json from '@rollup/plugin-json';
import url from "rollup-plugin-url";

export default {
  input: 'src/lib.js',
  output: {
    dir: 'projects/league-web-component/src/assets/temp',
    format: 'iife',
  },
  plugins: [
    resolve(),
    commonJS({
      include: 'node_modules/**'
    }),
    json({
      compact: true
    }),
    url({
      limit: 0,
      include: [
        /.*assets\/.*\.json/,
      ],
      emitFiles: true,
      fileName: "[name].[hash][extname]",
      publicPath: "/assets/temp/",
    })
  ],
  onwarn(warning) {
    if (warning.code === 'THIS_IS_UNDEFINED') {
      return;
    }

    console.error(warning.message);
  },
};
