# Les Salsifis - un serveur maison pour la maison

Les Salsifis, en plus d'avoir un nom complètement débile, sont un petit serveur de media pour chez soi sans prise de tête.  
On est bien d'accord que la sécurité n'est pas son point fort, on vise ici la facilité d'utilisation par des gens n'y connaissant absolument rien.

Il fait donc office de serveur de média pour afficher sur un téléviseur/décodeur/smartphone/tablette/Windows media player/VLC, de serveur de fichiers et de serveur de téléchargement bittorrent.

Il n'y a pas d'interface graphique, en revanche il possède une interface web pour effectuer les actions de base.  
Celle-ci est compatible avec les smartphones et tablettes, pour peu qu'ils soient connectés en Wifi.

![WebUI 1](https://raw.github.com/Dric/salsifis/master/img/screenshot.png "Interface Web v1.2.1")

## Changelog

- **v1.6 beta 2**
	- Correction de quelques libellés
	- Ajout d'une interface de gestion des téléchargements torrents un peu plus conviviale
	- Ajout d'un paramètre $download_dirs pour définir quels sont les répertoires où stocker les fichiers téléchargés
	- Ajout d'une constante TRANSMISSION_URL pour définir le chemin de l'url de transmission-RPC
	- L'état du serveur est maintenant rafraîchi automatiquement
	- Correction d'un ou deux bugs
- **v1.5** - *14/11/2013*
	- Ajout d'une FAQ
	- Correction de doc
	- Optimisation de la RAM/SWAP
- **v1.4** - *29/10/13*
	- Ajout de l'uptime du serveur
- **v1.3** - *29/10/13*
	- Les données sont maintenant stockées sur une partition séparée
	- Les fichiers de l'interface web sont récupérés par git
	- Correction et ajout de doc
	- La quantité de mémoire libre est maintenant correctement calculée (cache déduit)
- **v1.2.1** - *28/10/2013*
	- Ajout de paramètres pour transmission-daemon
	- Ajout d'un paramètre $partition pour définir la partition dont on veut surveiller l'espace disque
	- Ajout d'un paramètre $fm pour définir quel outil on utilise pour visualiser les fichiers via l'interface web (jQueryFM ou Pydio)
	- Ajout d'un paramètre $dlna_path pour définir le chemin du répertoire dlna
- **v1.2** - *28/10/2013*
	- Ajout d'un fichier de configuration pour la WebUI
	- On peut choisir la partition dont il faut surveiller l'espace disque
- **v1.1** - *18/10/2013*
	- Ajout d'un exploreur de fichiers *light* jQueryFM
	- Doc sur l'interface web
- **v1.0.1** - *16/10/2013*
	- Correction de doc
	- Ajout d'un fichier css externe
	- Les explications des services sont maintenant mises en valeur
- **v1.0** - *15/10/2013*
  - Version initiale

## TODO

- MAJ de la FAQ
- Paramétrage de Transmission depuis la WebUI.
- Ajouter un gestionnaire de téléchargements directs (ftp, liens directs...)
- Ajouter une interface d'exploration des fichiers mieux foutue que jQueryFM et moins lourde que Pydio
- Alertes et notifications (disque presque plein, erreurs, etc.)
- Revoir le code php qui est loin d'être propre

## Composants

- Ubuntu
- Lighttpd
- PHP
- Transmission (client bittorrent)
- Samba (partage de fichiers)
- MiniDLNA (partage de médias sur le réseau)
- Pydio (anciennement Ajaxplorer - explorateur de fichiers via une interface web)

### Interface Web

- [Twitter Bootstrap](http://http://getbootstrap.com) 3
- [jQuery File Manager](https://github.com/javiermarinros/jquery_fm) 11/07/2013 par [javiermarinos](https://github.com/javiermarinros) 

## Prérequis

- 512Mo RAM mini
- 4Go pour le système seul
- Connexion Internet sur la machine

## Installation de l'OS

- Télécharger [Ubuntu MinimalCD](https://help.ubuntu.com/community/Installation/MinimalCD)
- Le mettre sur un clé usb avec [UNetbootin](http://unetbootin.sourceforge.net) ou [Rufus](http://rufus.akeo.ie)
- Install par défaut
- Ne pas mettre un mot de passe trop faible pour le compte utilisateur salsifis.
- Effectuer un partionnement manuel.
- Créer une partition `/` de 4Go en `ext4` et vérifier qu'elle est amorçable.
- Créer une partition `swap` de 512Mo.
- Créer une partition dédiée aux fichiers (formatée en `ext4`) de la taille restante du disque et la monter dans `/media/salsifis`.
- Pour les mises à jour automatiques, optez pour la tranquillité en désactivant cette fonction vu que le serveur est de toute façon une passoire en matière de sécurité.

## Paramétrage du système

### Mise à jour du système

	sudo apt-get update
	sudo apt-get upgrade

### Adresse IP

L'adresse IP peut rester en DHCP, mais on prend alors le risque que celle-ci change. Pour passer en IP fixe, il faut saisir :

	sudo nano /etc/network/interfaces

Passer en IP statique avec les valeurs suivantes :

	iface eth0 static
		address		192.168.1.253
		netmask		255.255.255.0
		network 	192.168.1.0
		broadcast	192.168.1.255
		gateway 	192.168.1.1
		dns-nameservers 192.168.1.1 8.8.8.8

Ici la box est en `192.168.1.1`, il faudra changer `gateway` et le premier enregistrement de `dns-nameservers` si la box est en `192.168.1.254` par exemple (freebox).  
`8.8.8.8` est l'adresse du serveur DNS de Google.

	sudo reboot

### Installation et paramétrage de base

	sudo apt-get install zip software-properties-common openssh-server git zram-config
	echo vm.swappiness=5 | sudo tee -a /etc/sysctl.conf
	sudo nano /etc/ssh/sshd_config

Chercher et modifier les lignes suivantes comme indiqué :

	PermitRootLogin	no
	X11Forwarding		no
	Banner					/etc/issue.net (Décommentez cette ligne)
	AllowUsers			salsifis (Il vous faudra sans doute rajouter cette ligne)

Modifier ensuite la bannière d'accueil

	sudo nano /etc/issue.net

Remplacer le texte par

	
	Les Salsifis - Accès SSH
	************************
	
	- Les salsifis c'est dégoûtant.
	- Si vous n'êtes pas certain(e) de ce que vous faites là, inutile d'aller plus loin.
	
	C'est à vous...
	
	
Relancer ensuite le serveur ssh

	sudo service ssh restart

Coloriser le shell :

	nano ~/.bashrc

Décommenter ensuite `force_color_prompt=yes` (Virer le dièse qui est devant la ligne).  
Faire de même avec

	sudo nano /root/.bashrc
	
Création des répertoires de partage (si les données sont sur une partition séparée - recommandé)

	sudo mkdir /media/salsifis/tmp
	sudo mkdir /media/salsifis/tmp/incomplete
	sudo mkdir /media/salsifis/tmp/torrents
	sudo mkdir /media/salsifis/dlna
	sudo mkdir /media/salsifis/dlna/videos
	sudo mkdir /media/salsifis/dlna/photos
	sudo mkdir /media/salsifis/dlna/musique
	sudo chmod -R 777 /media/salsifis

### Installation du serveur web

Installation du trio lighttpd, MySQL et PHP. (d'après [howtoforge.com](http://www.howtoforge.com/installing-lighttpd-with-php5-php-fpm-and-mysql-support-on-ubuntu-13.04))

Les versions de ces composants sur les dépôts Ubuntu sont un peu défraîchies, il vaut mieux les prendre sur [dotdeb.org](http://www.dotdeb.org) et sur [ppa:ondrej/php5](https://launchpad.net/~ondrej/+archive/php5)

	sudo nano /etc/apt/sources.list
	
Ajouter à la fin du fichier :

	deb http://packages.dotdeb.org wheezy all
	deb-src http://packages.dotdeb.org wheezy all

Ajouter ensuite la clé de contrôle

	wget http://www.dotdeb.org/dotdeb.gpg
	cat dotdeb.gpg | sudo apt-key add -
	sudo apt-get update
	sudo apt-get install mysql-server mysql-client lighttpd php5-fpm php5 php5-mysql php5-curl php5-gd php5-intl php-pear php5-apcu
	sudo apt-get install phpmyadmin

Pour un minimum de cohérence, mettre des mots de passe identiques à celui de l'utilisateur principal Ubuntu.

	sudo nano /etc/php5/fpm/php.ini
	
Décommenter la ligne `cgi.fix_pathinfo=1`

	cd /etc/lighttpd/conf-available/
	sudo cp 15-fastcgi-php.conf 15-fastcgi-php-spawnfcgi.conf
	sudo nano 15-fastcgi-php.conf
	
Remplacer le texte par

	# /usr/share/doc/lighttpd-doc/fastcgi.txt.gz
	# http://redmine.lighttpd.net/projects/lighttpd/wiki/Docs:ConfigurationOptions#mod_fastcgi-fastcgi

	## Start an FastCGI server for php (needs the php5-cgi package)
	fastcgi.server += ( ".php" =>
	        ((
	                "socket" => "/var/run/php5-fpm.sock",
	                "broken-scriptfilename" => "enable"
	        ))
	)

Activer la config

	sudo lighttpd-enable-mod fastcgi
	sudo lighttpd-enable-mod fastcgi-php
	
Editer le fichier de config général

	sudo nano /etc/lighttpd/lighttpd.conf
	
Décommenter la ligne `mod_rewrite`

Relancer la config de lighttd

	sudo service lighttpd force-reload
	sudo chmod -R 777 /var/www
	sudo rm /var/www/*

Copier ensuite les fichiers de l'interface web dans `/var/www` :

	git clone https://github.com/Dric/Salsifis.git /var/www

Donner la possibilité d'éteindre et de redémarrer le serveur via l'interface web (d'après [Giacomo Drago](http://yatb.giacomodrago.com/en/post/10/shutdown-linux-system-from-within-php-script.html))

	sudo mv /var/www/*_suid /usr/local/bin
	sudo chown root:root /usr/local/bin/*_suid
	sudo chmod 4755 /usr/local/bin/*_suid


### Installation de transmission (client bittorrent)

	sudo add-apt-repository ppa:transmissionbt/ppa
	sudo apt-get update
	sudo apt-get install transmission-daemon
	sudo service transmission-daemon stop
	sudo nano /etc/transmission-daemon/settings.json
	
Vous pouvez remplacer le contenu du fichier par ce qui suit :

	{
		"alt-speed-down": 100, 
	  "alt-speed-enabled": true, 
	  "alt-speed-time-begin": 450, 
	  "alt-speed-time-day": 127, 
	  "alt-speed-time-enabled": true, 
	  "alt-speed-time-end": 1410, 
	  "alt-speed-up": 30, 
	  "bind-address-ipv4": "0.0.0.0", 
	  "bind-address-ipv6": "::", 
	  "blocklist-enabled": false, 
	  "blocklist-url": "http://www.example.com/blocklist", 
	  "cache-size-mb": 4, 
	  "dht-enabled": true, 
	  "download-dir": "/media/salsifis/dlna/videos", 
	  "download-limit": 100, 
	  "download-limit-enabled": 0, 
	  "download-queue-enabled": true, 
	  "download-queue-size": 5, 
	  "encryption": 1, 
	  "idle-seeding-limit": 30, 
	  "idle-seeding-limit-enabled": false, 
	  "incomplete-dir": "/media/salsifis/tmp/incomplete", 
	  "incomplete-dir-enabled": true, 
	  "lpd-enabled": false, 
	  "max-peers-global": 200, 
	  "message-level": 2, 
	  "peer-congestion-algorithm": "", 
	  "peer-id-ttl-hours": 6, 
	  "peer-limit-global": 200, 
	  "peer-limit-per-torrent": 50, 
	  "peer-port": 51413, 
	  "peer-port-random-high": 65535, 
	  "peer-port-random-low": 49152, 
	  "peer-port-random-on-start": false, 
	  "peer-socket-tos": "default", 
	  "pex-enabled": true, 
	  "port-forwarding-enabled": false, 
	  "preallocation": 1, 
	  "prefetch-enabled": 1, 
	  "queue-stalled-enabled": true, 
	  "queue-stalled-minutes": 30, 
	  "ratio-limit": 1.2000, 
	  "ratio-limit-enabled": true, 
	  "rename-partial-files": true, 
	  "rpc-authentication-required": false, 
	  "rpc-bind-address": "0.0.0.0", 
	  "rpc-enabled": true, 
	  "rpc-password": "{1c79deb1d0c97ee4c580d5761aaaf3f281a48e3cSD1f0byj", 
	  "rpc-port": 9091, 
	  "rpc-url": "/bt/", 
	  "rpc-username": "transmission", 
	  "rpc-whitelist": "127.0.0.1", 
	  "rpc-whitelist-enabled": false, 
	  "scrape-paused-torrents-enabled": true, 
	  "script-torrent-done-enabled": false, 
	  "script-torrent-done-filename": "", 
	  "seed-queue-enabled": false, 
	  "seed-queue-size": 10, 
	  "speed-limit-down": 350, 
	  "speed-limit-down-enabled": true, 
	  "speed-limit-up": 100, 
	  "speed-limit-up-enabled": true, 
	  "start-added-torrents": true, 
	  "trash-original-torrent-files": true, 
	  "umask": 0, 
	  "upload-limit": 100, 
	  "upload-limit-enabled": 0, 
	  "upload-slots-per-torrent": 14, 
	  "utp-enabled": true, 
	  "watch-dir": "/media/salsifis/tmp/torrents", 
	  "watch-dir-enabled": true
	}


### Installation de miniDLNA (serveur média)

	sudo add-apt-repository ppa:stedy6/stedy-minidna
	sudo nano /etc/apt/sources.list.d/stedy6-stedy-minidna-raring.list
	
Changer raring par precise dans la première ligne (le ppa de minidlna pour ubuntu 13 n'est pas dispo, on le prend pour une version précédente d'ubuntu)
	
	sudo apt-get update
	sudo apt-get install minidlna
	sudo nano /etc/minidlna.conf

Remplacer `media_dir=/opt` par

	media_dir=V,/media/salsifis/dlna/videos
	media_dir=A,/media/salsifis/dlna/musique
	media_dir=P,/media/salsifis/dlna/photos

Autres paramètres :

	friendly_name=Les Salsifis
	root_container=B (virer le # devant)

### Installation de Samba (partage de fichiers Windows)

	sudo apt-get install samba
	sudo nano /etc/samba/smb.conf
	
Modifier le nom du serveur samba (nom qui sera vu par les postes Windows) :

	server string = Les Salsifis

A la fin du fichier, ajouter

	[Torrents]
	path = /media/salsifis/tmp/torrents
	guest ok = yes
	read only = no
	browseable = yes
	inherit acls = yes
	inherit permissions = yes
	ea support = no
	store dos attributes = no
	printable = no
	create mask = 0755
	force create mode = 0644
	directory mask = 0755
	force directory mode = 0755
	hide dot files = yes
	invalid users =
	read list =
	
	[Vidéos]
	path = /media/salsifis/dlna/videos
	guest ok = yes
	read only = no
	browseable = yes
	inherit acls = yes
	inherit permissions = yes
	ea support = no
	store dos attributes = no
	printable = no
	create mask = 0755
	force create mode = 0644
	directory mask = 0755
	force directory mode = 0755
	hide dot files = yes
	invalid users =
	read list =
	
	[Musique]
	path = /media/salsifis/dlna/musique
	guest ok = yes
	read only = no
	browseable = yes
	inherit acls = yes
	inherit permissions = yes
	ea support = no
	store dos attributes = no
	printable = no
	create mask = 0755
	force create mode = 0644
	directory mask = 0755
	force directory mode = 0755
	hide dot files = yes
	invalid users =
	read list =
	
	[Photos]
	path = /media/salsifis/dlna/photos
	guest ok = yes
	read only = no
	browseable = yes
	inherit acls = yes
	inherit permissions = yes
	ea support = no
	store dos attributes = no
	printable = no
	create mask = 0755
	force create mode = 0644
	directory mask = 0755
	force directory mode = 0755
	hide dot files = yes
	invalid users =
	read list =
	
Redémarrer le service samba

	sudo service smbd restart
	
### Interface Web des Salsifis

Si des modifications doivent être faites (partition à surveiller, emploi de Pydio,...), il faut modifier le fichier de config.  
Le mieux est de copier `config.php` en `config_local.php` afin d'éviter de perdre le paramétrage lors de la mise à jour de l'interface Web.

### (Facultatif) Installation de XBMC

Tiré de <http://forum.xbmc.org/showthread.php?tid=174854>

Derniers pilotes vidéo pour Intel, nVidia et ATI :

	sudo add-apt-repository ppa:oibaf/graphics-drivers
	sudo apt-get update
	sudo apt-get install udisks upower xorg alsa-utils mesa-utils librtmp0 libmad0 lm-sensors libmpeg2-4 avahi-daemon libnfs1 consolekit pm-utils
	sudo dpkg-reconfigure x11-common
	
Répondre `N'importe qui` à la question posée.

	sudo adduser xbmc
	sudo usermod -a -G cdrom,audio,video,plugdev,users,dialout,dip xbmc
	sudo nano /etc/init/xbmc.conf

Insérer ceci dans le fichier :
	
	# xbmc-upstart
	# starts XBMC on startup by using xinit.
	# by default runs as xbmc, to change edit below.
	env USER=xbmc
	
	description     "XBMC-barebones-upstart-script"
	author          "Matt Filetto"
	
	# if you use mysql you need to wait for your network device
	# that means you should add 'and net-device-up IFACE!=lo' behind the udevtrigger
	
	start on (filesystem and stopped udevtrigger)
	stop on runlevel [016]
	
	# tell upstart to respawn the process if abnormal exit
	respawn
	respawn limit 10 5
	limit nice 21 21
	
	script
	exec su -c "xinit /usr/bin/xbmc --standalone :0" $USER
	# the following two are to get an idea, if you want to user a window manager
	#   exec su -c "xinit /usr/bin/fluxbox :0" $USER
	end script

Editer ce fichier

	sudo nano /etc/security/limits.conf
	
Ajouter ceci :

	xbmc             -       nice            -1

Créer le fichier suivant :

	sudo nano /etc/polkit-1/localauthority/50-local.d/custom-actions.pkla

Et insérer dedans :
	
	[Actions for xbmc user]
	Identity=unix-user:xbmc
	Action=org.freedesktop.upower.*;org.freedesktop.consolekit.system.*;org.freedesk​top.udisks.*
	ResultAny=yes
	ResultInactive=yes
	ResultActive=yes
	
	[Untrusted Upgrade]
	Identity=unix-user:xbmc
	Action=org.debian.apt.upgrade-packages;org.debian.apt.update-cache
	ResultAny=yes
	ResultInactive=yes
	ResultActive=yes

Installer xbmc :

	sudo add-apt-repository ppa:team-xbmc/ppa
	sudo apt-get update
	sudo apt-get install xbmc

### (Facultatif) Installation de Pydio (interface web de serveur de fichiers)

	sudo nano /etc/apt/sources.list
	
Ajouter la ligne suivante à la fin
	
	deb http://dl.ajaxplorer.info/repos/apt stable main
	
Récupération de la clé
	
	wget -O - http://dl.ajaxplorer.info/repos/charles@ajaxplorer.info.gpg.key | sudo apt-key add -
	sudo apt-get update
	sudo apt-get install pydio
	sudo nano /etc/lighttpd/conf-available/51-pydio.conf

Remplir le fichier avec 

	# Alias for pydio directory
	alias.url += (
		"/fichiers" => "/usr/share/pydio",
	)

	# Disallow access to libraries
	$HTTP["host"] =~ "/fichiers" {
  	$HTTP["url"] =~ "^(/files/.*|/plugins/.*|/server/.*|/tests/.*)" {
  		url.access-deny = ( "" )
  	}
	}

Activer le site et relancer la config de lighttpd

	sudo lighty-enable-mod pydio
	sudo service lighttpd force-reload
	sudo nano /etc/php5/fpm/php.ini

Passer `output_buffering` à `Off` et faire

	sudo service php5-fpm restart
	sudo service lighttpd restart

Editer maintenant les fichiers de conf de Pydio :

	sudo nano /usr/share/pydio/conf/bootstrap_conf.php
	
Décommenter et modifier `define("AJXP_LOCALE", "fr_FR.UTF-8");`

	sudo nano /usr/share/pydio/conf/bootstrap_repositories.php
	
Mettre en commentaires les 3 premiers repositories (shared inclus)

Créer un utilisateur ajaxplorer dans phpmyadmin et créer une base dont il sera le propriétaire. Passer l'encodage de la base en UTF-8.
  
Lancer `http://<nom du serveur>/fichiers`. Passer les avertissements et lancer l'install. Créer un compte `admin` avec mdp `salsifis`, indiquer la base MySQL pour l'install.
  
Une fois Pydio lancé, aller dans `admin/paramètres`. Dans la rubrique déôts, renommer le modèle en `Dépots` et l'éditer :

	File creation Mask : 0777
	Droits par défaut : Read and Write
	
Créer ensuite un dépôt pour chaque partage DLNA (musique, videos et photos).  
Aller ensuite dans `Configurations globales/Options principales/Authentification` et activer le guest.  
Aller dans `Configurations globales/Options principales/Configurations Management` et activer `Skip user history`
Aller dans `Rôles` et sélectionner `Root Role`. Mettre le pays et la langue en français, vérifier que tous les dépôts dlna sont bien accessibles en lecture/écriture.
Se déconnecter, rafraîchir la page pour se connecter en guest et se reconnecter en admin. Dans la liste des utilisateurs, éditer le compte guest et passer le dépôt par défaut sur `Vidéos`.

## Tweaks

### Mettre à jour l'interface web

	cd /var/www
	git pull

Si un message signale que des fichiers locaux ont été modifiés et que Git refuse de synchroniser les fichiers distants pour ne pas les perdre, il suffit de lancer `git stash` avant.

### Mises à jour automatiques
Voir le sujet sur [ubuntu.fr](http://forum.ubuntu-fr.org/viewtopic.php?id=505021)

### Virer les vieux kernels
D'après [ubuntuforums.org](http://ubuntuforums.org/showthread.php?t=1961409&p=12444039#post12444039)

Cette commande est bien évidemment à ajouter dans un alias, et pourquoi pas à lancer automatiquement de temps en temps. Par défaut on garde le kernel actuel + les deux précédents. C'est `head -n -6` qui définit le nombre de kernels (nb kernels à garder * 2)

	sudo aptitude purge $(dpkg -l 'linux-*' | sed '/^ii/!d;/'"$(uname -r | sed "s/\(.*\)-\([^0-9]\+\)/\1/")"'/d;s/^[^ ]* [^ ]* \([^ ]*\).*/\1/;/[0-9]/!d' | sort -t- -k3 | head -n -6 ) --assume-yes
