apt-get update
apt-get install -y sqlite3 php-sqlite3
if [ -f /tmp/install.sh ]; then 
	rm /tmp/install.sh
fi
