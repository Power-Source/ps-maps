#/bin/bash

hasctags=$(which ctags);
if [ "" == "$hasctags" ]; then
	echo "Install ctags"
	exit 1
fi

find psource-maps-plugin_DIR \
	-type f \
	-regextype posix-egrep \
	-regex ".*\.(php|js)" \
	! -path "*/.git*" \
	! -path "*/node_modules/*" \
	! -path "*/build/*"  \
	! -path "*/wp-content/uploads/*" \
	! -path "*/*.min.js" \
| ctags -f "psource-maps-plugin_DIR/psource-maps-plugin.tags" --fields=+KSn -L -
