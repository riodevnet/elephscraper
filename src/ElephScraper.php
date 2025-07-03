<?php
namespace Riodevnet\Elephscraper;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class ElephScraper {
    protected $url;
    protected $html;
    protected $crawler;

    public function __construct($url) {
        $this->url = $url;

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return;
        }

        try {
            $client = new Client(['timeout' => 10]);
            $response = $client->get($url);
            $this->html = $response->getBody()->getContents();
            $this->crawler = new Crawler($this->html);
        } catch (\Exception $e) {
            $this->crawler = null;
        }
    }

    public function title() {
        return $this->getSingle('title');
    }

    public function charset() {
        return $this->getMetaAttr('charset');
    }

    public function viewport() {
        $tag = $this->getMetaContent('viewport');
        return $tag ? explode(',', $tag) : null;
    }

    public function viewportString() {
        return $this->getMetaContent('viewport');
    }

    public function canonical() {
        return $this->getLinkAttr('canonical');
    }

    public function contentType() {
        return $this->getMetaContentHttp('Content-Type');
    }

    public function csrfToken() {
        $content = $this->getMetaContent('csrf-token');
        if ($content) return $content;

        return $this->getInputAttr('csrf-token', 'value');
    }

    public function author() {
        return $this->getMetaContent('author');
    }

    public function description() {
        return $this->getMetaContent('description');
    }

    public function image() {
        return $this->getMetaProperty('og:image');
    }

    public function keywords() {
        $content = $this->getMetaContent('keywords');
        return $content ? explode(',', $content) : null;
    }

    public function keywordString() {
        return $this->getMetaContent('keywords');
    }

    public function openGraph($prop = null) {
        if ($prop) return $this->getMetaProperty($prop);

        $props = ['og:site_name', 'og:type', 'og:title', 'og:description', 'og:url', 'og:image'];
        $result = [];
        foreach ($props as $p) {
            $result[$p] = $this->getMetaProperty($p);
        }
        return $result;
    }

    public function twitterCard($prop = null) {
        if ($prop) return $this->getMetaContent($prop);

        $props = ['twitter:card', 'twitter:title', 'twitter:description', 'twitter:url', 'twitter:image'];
        $result = [];
        foreach ($props as $p) {
            $result[$p] = $this->getMetaContent($p);
        }
        return $result;
    }

    public function h1() { return $this->getTagTexts('h1'); }
    public function h2() { return $this->getTagTexts('h2'); }
    public function h3() { return $this->getTagTexts('h3'); }
    public function h4() { return $this->getTagTexts('h4'); }
    public function h5() { return $this->getTagTexts('h5'); }
    public function h6() { return $this->getTagTexts('h6'); }
    public function p()  { return $this->getTagTexts('p');  }

    public function ul() {
        return $this->getListTexts('ul');
    }

    public function ol() {
        return $this->getListTexts('ol');
    }

    public function images() {
        return $this->getAttributes('img', 'src');
    }

    public function imageDetails() {
        if (!$this->crawler) return null;

        return $this->crawler->filter('img')->each(function ($node) {
            return [
                'url' => $node->attr('src'),
                'alt_text' => $node->attr('alt'),
                'title' => $node->attr('title')
            ];
        });
    }

    public function links() {
        return $this->getAttributes('a', 'href');
    }

    public function linkDetails() {
        if (!$this->crawler) return null;

        return $this->crawler->filter('a')->each(function ($node) {
            $href = $node->attr('href');
            $rel = $node->attr('rel') ?? '';
            $rels = is_string($rel) ? explode(' ', $rel) : [];

            return [
                'url' => $href,
                'protocol' => (strpos($href, ':') !== false) ? explode(':', $href)[0] : '',
                'text' => trim($node->text()),
                'title' => $node->attr('title') ?? '',
                'target' => $node->attr('target') ?? '',
                'rel' => $rels,
                'is_nofollow' => in_array('nofollow', $rels),
                'is_ugc' => in_array('ugc', $rels),
                'is_noopener' => in_array('noopener', $rels),
                'is_noreferrer' => in_array('noreferrer', $rels),
            ];
        });
    }

    public function filter($element, $attributes = [], $multiple = false, $extract = [], $returnHtml = true) {
        if (!$this->crawler) return null;

        $filter = $element;
        foreach ($attributes as $key => $value) {
            $filter .= "[$key='$value']";
        }

        try {
            if ($multiple) {
                return $this->crawler->filter($filter)->each(function ($node) use ($extract, $returnHtml) {
                    if (!empty($extract)) {
                        $result = [];
                        foreach ($extract as $sel) {
                            $inner = $sel[0] === '.' ? "[class='" . substr($sel, 1) . "']" :
                                     ($sel[0] === '#' ? "[id='" . substr($sel, 1) . "']" : $sel);
                            $found = $node->filter($inner)->first();
                            $result[$sel] = $found->count() ? trim($found->text()) : null;
                        }
                        return $result;
                    }
                    return $returnHtml ? $node->html() : trim($node->text());
                });
            } else {
                $node = $this->crawler->filter($filter)->first();
                if ($node->count()) {
                    if (!empty($extract)) {
                        $result = [];
                        foreach ($extract as $sel) {
                            $inner = $sel[0] === '.' ? "[class='" . substr($sel, 1) . "']" :
                                     ($sel[0] === '#' ? "[id='" . substr($sel, 1) . "']" : $sel);
                            $found = $node->filter($inner)->first();
                            $result[$sel] = $found->count() ? trim($found->text()) : null;
                        }
                        return $result;
                    }
                    return $returnHtml ? $node->html() : trim($node->text());
                }
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    protected function getSingle($tag) {
        return $this->crawler && $this->crawler->filter($tag)->count()
            ? trim($this->crawler->filter($tag)->text())
            : null;
    }

    protected function getMetaContent($name) {
        $node = $this->crawler->filter("meta[name='$name']");
        return $node->count() ? $node->attr('content') : null;
    }

    protected function getMetaContentHttp($httpEquiv) {
        $node = $this->crawler->filter("meta[http-equiv='$httpEquiv']");
        return $node->count() ? $node->attr('content') : null;
    }

    protected function getMetaProperty($property) {
        $node = $this->crawler->filter("meta[property='$property']");
        return $node->count() ? $node->attr('content') : null;
    }

    protected function getInputAttr($name, $attr) {
        $node = $this->crawler->filter("input[name='$name']");
        return $node->count() ? $node->attr($attr) : null;
    }

    protected function getLinkAttr($rel) {
        $node = $this->crawler->filter("link[rel='$rel']");
        return $node->count() ? $node->attr('href') : null;
    }

    protected function getTagTexts($tag) {
        if (!$this->crawler) return null;
        return $this->crawler->filter($tag)->each(fn($n) => trim($n->text()));
    }

    protected function getListTexts($tag) {
        if (!$this->crawler) return null;
        return $this->crawler->filter("$tag li")->each(fn($n) => trim($n->text()));
    }

    protected function getAttributes($tag, $attr) {
        if (!$this->crawler) return null;
        return $this->crawler->filter($tag)->each(fn($n) => $n->attr($attr));
    }
}
