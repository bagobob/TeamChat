# TeamChat
Une fois le projet cloné creer un fichier .env.local:
Rajouter cette ligne et la modifier avec les valeurs correspondantes a votre bd
DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7

Puis faire la commande: php/bin console doctrine:database:create
Ensuite : php/bin console doctrine:schema:update --force

Puis faire les commandes suivantes afin de faire macher l'application:

composer update
yarn install
yarn add --dev @symfony/webpack-encore
yarn add webpack-notifier --dev
yarn encore dev


Afin de faire marcher les messages 
Se placer dans le dossier mercure en tapant cette commande à partir
de la racine du projet : cd mercure
Puis copier cette ligne et appuyer sur la touche entree pour lancer 
le serveur de Mercure

./mercure --jwt-key "I-c4N_H@Z{M3rCuR3}&SymF0nY~1n~AFSY" --addr "localhost:3000" --cors-allowed-origins "http://localhost:8000/" --publish-allowed-origins "*"


Puis creer ce declencheur dans sa bd
After insert
UPDATE conversation
SET last_message_id = new.id
WHERE conversation_id = new.conversation_id


