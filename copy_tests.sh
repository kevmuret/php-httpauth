if [ "$1" == "" ] || [ ! -d "$1" ]
then
	exit 1
fi
cp tests/www_*.php "$1"
cp tests/.htaccess "$1"
