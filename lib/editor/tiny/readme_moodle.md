# This is a description for the TinyMCE 6 library integration with Moodle.

## Upgrade procedure for TinyMCE Editor

1. Check out a clean copy of TinyMCE of the target version.

 ```
 tinymce=`mktemp -d`
 cd "${tinymce}"
 git clone https://github.com/tinymce/tinymce.git
 cd tinymce
 git checkout [version]
 ```

2. Update the typescript configuration to generate ES6 modules with ES2020 target.

 ```
 sed -i 's/"module".*es.*",/"module": "es6",/' tsconfig.shared.json
 sed -i 's/"target.*es.*",/"target": "es2020",/' tsconfig.shared.json
 ```

3. Rebuild TinyMCE

 ```
 yarn
 yarn build
 ```

4. Remove the old TinyMCE configuration and replace it with the newly built version.

 ```
 rm -rf path/to/moodle/lib/editor/tiny/js
 cp -r modules/tinymce/js path/to/moodle/lib/editor/tiny/js
 ```
