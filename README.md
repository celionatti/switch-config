# Switch Config (`switch/config`)

> A flexible configuration loader supporting nested dot-notation access, default values, and environment variable overrides.

---

## 📦 Installation

```bash
composer require switch/config
```

---

## 🚀 Usage

```php
use Switch\Config\Config;

$config = new Config([
    'app' => ['name' => 'Switch App', 'debug' => true],
    'db' => ['host' => '127.0.0.1', 'port' => 5432]
]);

// Dot-notation retrieval
echo $config->get('app.name');
echo $config->get('db.host', 'localhost');

// ArrayAccess syntax
$config['app.debug'] = false;
```

---

## 📄 License
MIT License.
