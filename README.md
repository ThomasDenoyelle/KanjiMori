# 🔤 Kanji - Plateforme d'Apprentissage des Kanji

Une application web pour apprendre et maîtriser les kanji japonais à travers des quiz interactifs, avec un système de dossiers de collection et suivi de progression.

## ✨ Fonctionnalités principales

- **📚 Quiz personnalisés** : Créez et organisez des quiz de kanji avec leurs lectures et traductions
- **📁 Dossiers de collection** : Organisez vos quiz par thème ou niveau de difficulté
- **👥 Collaboration** : Partagez vos collections avec d'autres utilisateurs
- **📊 Suivi de progression** : Suivez vos tentatives de quiz et votre évolution
- **🔐 Authentification sécurisée** : Système d'inscription et connexion avec vérification d'email

## 🚀 Démarrage rapide

### Prérequis

- **Docker** et **Docker Compose**
- **PHP 8.4+** (si sans Docker)
- **Node.js** (pour Tailwind CSS)
- **PostgreSQL** (si sans Docker)

### Installation avec Docker (recommandé)

1. **Clonez le repository** :
```bash
git clone https://github.com/ToMaTo1206/kanji.git
cd kanji
```

2. **Créez votre fichier `.env.local`** :
```bash
cp .env.dev .env.local
```
Remplissez les variables d'environnement :
```env
DB_NAME=kanji
DB_USER=kanji
DB_PASSWORD=your_secure_password
```

3. **Démarrez les conteneurs** :
```bash
docker-compose up -d
```

4. **Installez les dépendances** :
```bash
docker-compose exec php composer install
docker-compose exec php npm install
```

5. **Créez la base de données** :
```bash
docker-compose exec php php bin/console doctrine:migrations:migrate
```

6. **Générez les assets** (Tailwind CSS) :
```bash
docker-compose exec php npm run build
```

7. **Chargez les données de test** (optionnel) :
```bash
docker-compose exec php php bin/console doctrine:fixtures:load
```

L'application est accessible sur **http://localhost:8080**

### Installation locale (sans Docker)

1. **Clonez le repository** et installez les dépendances :
```bash
git clone https://github.com/ToMaTo1206/kanji.git
cd kanji
composer install
npm install
```

2. **Configurez la base de données** dans `.env.local`

3. **Créez les tables** :
```bash
php bin/console doctrine:migrations:migrate
```

4. **Générez les assets** :
```bash
npm run build
```

5. **Lancez le serveur Symfony** :
```bash
symfony server:start
```

L'application est accessible sur **http://localhost:8000**

## 💻 Utilisation de l'application

### 1️⃣ Créer un compte

- Accédez à la page d'inscription
- Remplissez vos informations et confirmez votre email
- Connectez-vous

### 2️⃣ Créer une collection de kanji

- Rendez-vous dans **Ma Bibliothèque** → **Mes Dossiers**
- Créez un nouveau dossier avec un titre (ex: "JLPT N2", "Kanji courants")
- Optionnel : Invitez des amis pour collaborer

### 3️⃣ Ajouter des quiz

- Accédez à votre dossier
- Ajoutez des quiz en remplissant :
  - **Titre** du quiz
  - **Questions** avec :
    - Le kanji (ex: 日)
    - La lecture (ex: にち)
    - La traduction (ex: jour/soleil)

### 4️⃣ Passer les quiz

- Accédez à votre dossier et sélectionnez un quiz
- Testez-vous sur les kanji
- Visualisez votre progression et vos résultats

### 5️⃣ Explorer les collections publiques

- Rendez-vous dans **Explorer** → **Les classes**
- Découvrez les collections créées par la communauté
- Ajoutez des quiz à votre bibliothèque personnelle

## 📁 Structure du projet

```
kanji/
├── src/
│   ├── Controller/       # Contrôleurs web
│   ├── Entity/          # Entités Doctrine (User, Quiz, Question, etc.)
│   ├── Repository/      # Requêtes personnalisées
│   ├── Form/            # Formulaires Symfony
│   ├── Security/        # Authentification et sécurité
│   └── ApiResource/     # Ressources API Platform
├── templates/           # Templates Twig
├── public/              # Fichiers statiques
├── migrations/          # Migrations de base de données
├── docker/              # Configuration Docker
└── config/              # Configuration Symfony
```

## 🛠 Stack technique

- **Backend** : Symfony 8.1
- **Base de données** : PostgreSQL
- **Frontend** : Twig, Tailwind CSS, DaisyUI
- **API** : API Platform
- **Authentification** : JWT (lexik/jwt-authentication)
- **Email** : Symfony Mailer + Mailpit (développement)

## 📧 Développement

### Consulter les emails en développement

Mailpit est inclus dans Docker Compose. Accédez à **http://localhost:8025** pour voir les emails de vérification.

### Lancer les tests

```bash
docker-compose exec php php bin/phpunit
```

### Générer les assets en mode watch

```bash
npm run watch
```

## 🔒 Sécurité

- Authentification JWT sécurisée
- Vérification d'email à l'inscription
- Protection CSRF sur tous les formulaires
- Validation côté serveur des données
- Chiffrement des mots de passe avec bcrypt

## 📄 License

Propriétaire - Tous droits réservés

## 🤝 Contribution

Pour toute contribution, veuillez créer une issue ou une pull request sur [GitHub](https://github.com/ToMaTo1206/kanji).

---

**Bon apprentissage ! 学べ頑張って！**
