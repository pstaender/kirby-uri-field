<?php

namespace Zeitpulse\LinkField;

class Link
{
    protected $data;
    protected $page;
    protected $file;

    function __construct(array $data)
    {
        $this->data = $data;
        $this->page = null;
        $this->file = null;

        if ($this->value()) {
            $this->resolveUrl();
        }
    }

    function __call($name, $arguments = [])
    {
        return $this->$name ?? $this->data[$name] ?? null;
    }

    function __toString()
    {
        return $this->url();
    }

    public function title()
    {
        $text = $this->text();

        if (!empty($text)) {
            return $text;
        }

        if ($this->page()) {
            $text = $this->page()->title()->value();
        }

        if ($this->file()) {
            $text = $this->file()->title()->value();

            if (!$text) {
                $text = $this->file()->filename();
            }
        }

        if (empty($text)) {
            $text = $this->value();
        }

        return $text;
    }

    public function url()
    {
        return $this->resolveUrl();
    }

    public function resolveUrl() {
        if (self::type_of_url($this->value()) === 'url') {
            return $this->value();
        } else {
            if (self::type_of_url($this->value()) === 'file') {
                if (!$this->file) {
                    if ($this->type() === 'file') {
                        $this->file = kirby()->file($this->value());
                    }
                }
                return $this->file()->url();
            } else if (self::type_of_url($this->value()) === 'page') {
                if (!$this->page) {
                    $this->page = kirby()->page($this->value());
                }
                return $this->page()->url();
            }
        }
    }

    private function type() {
        return self::type_of_url($this->value());
    }

    public static function type_of_url($url) {
        if (empty($url) || $url === '/' || $url[0] === '#' || preg_match('/^(javascript|tel|http[s]{0,1}|mailto)\:/', $url)) {
            return 'url';
        } else {
            $fileExtension = pathinfo($url)['extension'] ?? null;
            if (!$fileExtension || $fileExtension === 'html') {
                return 'page';
            } else {
                return 'file';
            }
        }
        return null;
    }

}
