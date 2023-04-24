# Co to je?

Jedná se o jednoduchou aplikaci na správu lidí a místností. Lidem jdou přidělit klíče k místnostem a domovská místnost. Lidi jdou upravovat, jdou jim měnit hesla a každý člověk se může přihlásit.
Jdou také zakládat, upravovat a mazat místnosti.

# Návod na vývoj

1. Pro plné fungování je potřeba doplnit kód v **config_local.json** a **config.json**</br>

```
{
  "db": {
    "user" : "váš uživatel",
    "password" : "vaše heslo"
  }
}
```

2. Dále je potřeba nainstalovat composer a v terminalu spustit tyto commandy: **composer install** a **composer update**
