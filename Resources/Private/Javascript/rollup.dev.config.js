import resolve from '@rollup/plugin-node-resolve';
import commonJS from '@rollup/plugin-commonjs';
import json from '@rollup/plugin-json';
import url from "rollup-plugin-url";
import replace from '@rollup/plugin-replace';
import * as fs from 'fs';
import * as yaml from 'yaml'

export const firebaseConfig =
  yaml.parse(
    fs.readFileSync('../../../../../Configuration/Development/Settings.yaml', 'utf8')
  )['Yalento']['Neos']['League']['FirebaseConfig'];

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
    }),
    replace({
      '__FIREBASE_CONFIGURATION__apiKey__': firebaseConfig.apiKey,
      '__FIREBASE_CONFIGURATION__authDomain__': firebaseConfig.authDomain,
      '__FIREBASE_CONFIGURATION__databaseURL__': firebaseConfig.databaseURL,
      '__FIREBASE_CONFIGURATION__projectId__': firebaseConfig.projectId,
      '__FIREBASE_CONFIGURATION__storageBucket__': firebaseConfig.storageBucket,
      '__FIREBASE_CONFIGURATION__messagingSenderId__': firebaseConfig.messagingSenderId,
      '__FIREBASE_CONFIGURATION__appId__': firebaseConfig.appId,
      '__FIREBASE_CONFIGURATION__measurementId__': firebaseConfig.measurementId
    })
  ],
  onwarn(warning) {
    if (warning.code === 'THIS_IS_UNDEFINED') {
      return;
    }

    console.error(warning.message);
  },
};
