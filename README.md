# Construire son Home server

## Composants

- Ubuntu
- Apache
- PHP
- Transmission (client bittorrent)
- Samba (partage de fichiers)
- MiniDLNA (partage de m�dias sur le r�seau)
- AjaxExplorer


## Pr�requis

- 512Mo RAM mini
- 10Go pour le syst�me
- Connexion Internet sur la machine

## Installation de l'OS

- T�l�charger [Ubuntu MinimalCD](https://help.ubuntu.com/community/Installation/MinimalCD)
- Le mettre sur un cl� usb avec [UNetbootin](http://unetbootin.sourceforge.net)
- Install par d�faut
- Ne pas mettre un mot de passe trop faible pour le compte utilisateur.
- Utiliser le partitionnement propos�.
- Ne s�lectionner que "Basic Ubuntu Server"

## Tweaks

### Mises � jour automatiques
From http://forum.ubuntu-fr.org/viewtopic.php?id=505021

### Virer les vieux kernels
From http://ubuntuforums.org/showthread.php?t=1961409&p=12444039#post12444039

Cette commande est bien �videmment � ajouter dans un alias, et pourquoi pas � lancer automatiquement de temps en temps. Par d�faut on garde le kernel actuel + les deux pr�c�dents. C'est `head -n -6` qui d�finit le nombre de kernels (nb kernels � garder * 2)
  aptitude purge $(dpkg -l 'linux-*' | sed '/^ii/!d;/'"$(uname -r | sed "s/\(.*\)-\([^0-9]\+\)/\1/")"'/d;s/^[^ ]* [^ ]* \([^ ]*\).*/\1/;/[0-9]/!d' | sort -t- -k3 | head -n -6 ) --assume-yes

