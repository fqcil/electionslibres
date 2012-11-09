# Requis
## php.ini

### tl;dr
    short_open_tag = On
    upload_max_filesize = 2000M

Dans PHP.ini, s'assurer qu'on peut uploader au moins 1 Gig (`upload_max_filesize` et `post_max_size`) et qu'il y a assez de place dans le répertoire temporaire (`upload_tmp_dir`): la liste du DGE presqu'un 1GO.

S'assurer également que le temps d'exécution permis est important: 5h par exemple.

    ; Temporary directory for HTTP uploaded files (will use system default if $
    ; specified).
    ;upload_tmp_dir =
    upload_tmp_dir = /var/tmp

    ; Maximum allowed size for uploaded files.
    upload_max_filesize = 2000M

    ; On trouve <? dans le code, alors
    short_open_tag = On

Requis:

catdoc (pour xls2csv)

1. Créer une base de données 'pointage' et un usager avec les droits d'y accéder
2. Modifier config/config.php
3. Créer la structure de la base de données:
        mysql -u user -p pointage < base_schema.sql
4. Configurer le serveur web (apache, nginx, etc.) vers le répertoire html
4. Utiliser le nom d'usager 'admin' et le mot de passe 'admin' pour se connecter à http://localhost/electionslibres/index.php
