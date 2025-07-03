# 🐘 ElephScraper

**ElephScraper** is a lightweight and PHP-native web scraping toolkit built using Guzzle and Symfony DomCrawler. It provides a clean and powerful interface to extract HTML content, metadata, and structured data from any website.

> Fast. Clean. Eleph-style scraping. 🐘⚡

---
## 🚀 Features

- ✅ Extract metadata: title, description, keywords, author, charset, canonical, and more
- ✅ Supports Open Graph, Twitter Card, CSRF tokens, and HTTP-equiv headers
- ✅ Extract headings, paragraphs, images, lists, and links
- ✅ Powerful `filter()` method with support for class/ID/tag-based selectors
- ✅ Return raw HTML or clean plain text
- ✅ Clean return types: string, array, or associative array
- ✅ Built with Guzzle + Symfony DomCrawler + CssSelector

---

## 📦 Installation

Install via Composer:

```bash
composer require riodevnet/elephscraper
```

> Requires PHP 7.4 or newer.

---

## 🛠️ Basic Usage

```php
<?php

require_once 'vendor/autoload.php';
require_once 'ElephScraper.php';

$scraper = new ElephScraper("https://example.com");

echo $scraper->title(); // "Welcome to Example.com"
echo $scraper->description(); // "Example site for testing"
print_r($scraper->h1()); // ["Main Title", "News"]
print_r($scraper->openGraph());
```

---

## 🧪 Available Methods

### 🔹 Page Metadata

```php
$scraper->title();
$scraper->description();
$scraper->keywords();
$scraper->keywordString();
$scraper->charset();
$scraper->canonical();
$scraper->contentType();
$scraper->author();
$scraper->csrfToken();
$scraper->image();
```

### 🔹 Open Graph & Twitter Card

```php
$scraper->openGraph();                 // All OG meta
$scraper->openGraph("og:title");      // Specific OG tag

$scraper->twitterCard();              // All Twitter tags
$scraper->twitterCard("twitter:title");
```

### 🔹 Headings & Text

```php
$scraper->h1();
$scraper->h2();
$scraper->h3();
$scraper->h4();
$scraper->h5();
$scraper->h6();
$scraper->p();
```

### 🔹 Lists

```php
$scraper->ul(); // all <ul><li> text
$scraper->ol(); // all <ol><li> text
```

### 🔹 Images

```php
$scraper->images();         // just src URLs
$scraper->imageDetails();   // src, alt, title
```

### 🔹 Links

```php
$scraper->links();        // just hrefs
$scraper->linkDetails();  // full detail
```

---

## 🔍 Custom DOM Filtering

### ▸ Example: Filter Single Element

```php
$scraper->filter(
    element: 'div',
    attributes: ['id' => 'main'],
    multiple: false,
    extract: ['.title', '#desc', 'p'],
    returnHtml: false
);
```

### ▸ Example: Filter Multiple Elements

```php
$scraper->filter(
    element: 'div',
    attributes: ['class' => 'card'],
    multiple: true,
    extract: ['h2', '.subtitle', '#info'],
    returnHtml: false
);
```

### ▸ Example: Return HTML Content

```php
$scraper->filter(
    element: 'section',
    attributes: ['class' => 'hero'],
    returnHtml: true
);
```

> Extract selectors support:
> - Tag names: `h1`, `p`, `span`, etc.
> - Class: `.className`
> - ID: `#idName`
>
> Output keys auto-normalized to original selector.

## 🤝 Contributing

Found a bug? Want to add features?
Open an issue or create a pull request!

---

## 📄 License

MIT License © 2025 — ElephScraper

---

## 🔗 Related Libraries

- [Guzzle](https://github.com/guzzle/guzzle)
- [Symfony DomCrawler](https://symfony.com/doc/current/components/dom_crawler.html)
- [Symfony CssSelector](https://symfony.com/doc/current/components/css_selector.html)

---

## 💡 Why ElephScraper?

> ElephScraper is your dependable PHP elephant — strong, smart, and always ready to extract the right data.