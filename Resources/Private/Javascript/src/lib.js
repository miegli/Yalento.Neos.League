/** sap ui5 webcomponents **/
import '@ui5/webcomponents/dist/Assets';
import '@ui5/webcomponents/dist/Button';
import '@ui5/webcomponents/dist/Tab';
import '@ui5/webcomponents/dist/TabContainer';
import "@ui5/webcomponents/dist/Icon"
import "@ui5/webcomponents/dist/DatePicker"
import "@ui5/webcomponents/dist/DurationPicker"
import "@ui5/webcomponents/dist/Panel"
import "@ui5/webcomponents/dist/List"
import "@ui5/webcomponents/dist/ListItem"

/** sap ui5 fiori **/
import "@ui5/webcomponents-fiori/dist/Assets.js";
import "@ui5/webcomponents-fiori/dist/ShellBar.js";
import "@ui5/webcomponents-fiori/dist/ShellBarItem.js";
import "@ui5/webcomponents-fiori/dist/ProductSwitch.js";
import "@ui5/webcomponents-fiori/dist/ProductSwitchItem.js";
import "@ui5/webcomponents-fiori/dist/SideNavigation.js";
import "@ui5/webcomponents-fiori/dist/SideNavigationItem.js"
import "@ui5/webcomponents-fiori/dist/SideNavigationSubItem.js";

/** sap ui5 icons **/
import "@ui5/webcomponents-icons/dist/Assets";


/** add yalento firebase configuration **/
window['yalento'] = {};
window['yalento']['firebaseConfiguration'] = {
  apiKey: '__FIREBASE_CONFIGURATION__apiKey__',
  authDomain: '__FIREBASE_CONFIGURATION__authDomain__',
  databaseURL: '__FIREBASE_CONFIGURATION__databaseURL__',
  projectId: '__FIREBASE_CONFIGURATION__projectId__',
  storageBucket: '__FIREBASE_CONFIGURATION__storageBucket__',
  messagingSenderId: '__FIREBASE_CONFIGURATION__messagingSenderId__',
  appId: '__FIREBASE_CONFIGURATION__appId__',
  measurementId: '__FIREBASE_CONFIGURATION__measurementId__'
}
