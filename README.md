# Construire son Home server

## Composants

- Ubuntu
- Apache
- PHP
- Transmission (client bittorrent)
- Samba (partage de fichiers)
- MiniDLNA (partage de médias sur le réseau)
- AjaxExplorer


## Prérequis

- 512Mo RAM mini
- 10Go pour le système
- Connexion Internet sur la machine

## Installation de l'OS

- Télécharger [Ubuntu MinimalCD](https://help.ubuntu.com/community/Installation/MinimalCD)
- Le mettre sur un clé usb avec [UNetbootin](http://unetbootin.sourceforge.net)
- Install par défaut
- Ne pas mettre un mot de passe trop faible pour le compte utilisateur.
- Utiliser le partitionnement proposé.
- Ne sélectionner que "Basic Ubuntu Server"

## Tweaks

### Mises à jour automatiques
From http://forum.ubuntu-fr.org/viewtopic.php?id=505021

### Virer les vieux kernels
From http://ubuntuforums.org/showthread.php?t=1961409&p=12444039#post12444039

Cette commande est bien évidemment à ajouter dans un alias, et pourquoi pas à lancer automatiquement de temps en temps. Par défaut on garde le kernel actuel + les deux précédents. C'est `head -n -6` qui définit le nombre de kernels (nb kernels à garder * 2)
  aptitude purge $(dpkg -l 'linux-*' | sed '/^ii/!d;/'"$(uname -r | sed "s/\(.*\)-\([^0-9]\+\)/\1/")"'/d;s/^[^ ]* [^ ]* \([^ ]*\).*/\1/;/[0-9]/!d' | sort -t- -k3 | head -n -6 ) --assume-yes

