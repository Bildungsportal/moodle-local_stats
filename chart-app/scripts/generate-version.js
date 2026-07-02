const fs = require('fs');
const path = require('path');

const pkg = require('../package.json');
const tsPkg = require('typescript/package.json');
const nccPkg = require('@vercel/ncc/package.json');

const versionInfo = `// Auto-generated - do not edit
export const VERSION = {
  app: '${pkg.version}',
  typescript: '${tsPkg.version}',
  ncc: '${nccPkg.version}',
  buildDate: '${new Date().toISOString()}',
};
`;

fs.writeFileSync(path.join(__dirname, '../src/generated/version.ts'), versionInfo);
console.log('Version info generated');
