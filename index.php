<?php

namespace Zeitpulse\LinkField;

load([
    'Zeitpulse\\LinkField\\Link' => 'src/Link.php'
], __DIR__);

use Kirby\Cms\App;
use Kirby\Data\Yaml;

App::plugin('pstaender/uri-field', [
    'fields' => [
        'uri' => [
            'mixins' => ['pagepicker', 'filepicker'],
            'props' => [
                'value' => function ($input = null) {
                    // Value comes from a TXT file.
                    $data = Yaml::decode($input);

                    if (empty($data['type'])) {
                        // Handles cases where the field was previously of type
                        // `url` and was not correctly formatted.
                        $data = [
                            'type' => 'url',
                            'value' => $input
                        ];
                    }

                    if (!empty($data['value'])) {
                        if (is_string($data['value'])) {
                            $data['type'] = Link::type_of_url($data['value']);
                            if ($data['type'] === 'page') {
                                $targetPage = kirby()->page($data['value']);

                                if ($targetPage) {
                                    // Value is put in an array because the Panel field expects one.
                                    $data['value'] = [$this->pageResponse($targetPage)];
                                } else {
                                    $data['value'] = [];
                                }
                            } else if ($data['type'] === 'file') {
                                $targetFile = kirby()->file($data['value']);

                                if ($targetFile) {
                                    $data['value'] = [$this->fileResponse($targetFile)];
                                } else {
                                    $data['value'] = [];
                                }
                            }
                        } else {
                            // Value came from the panel and is a serialized Page or File.
                        }
                    }

                    return $data;
                },
                'options' => function ($value = null) {
                    if (!is_array($value)) {
                        $config = kirby()->option('oblik.linkField.options');

                        if (is_array($config)) {
                            $value = $config;
                        } else {
                            $value = ['url', 'page'];
                        }
                    }

                    if (!in_array('url', $value)) {
                        // url is not optional, otherwise page select may produce an error in vue
                        $value[] = 'url';
                    }

                    return $value;
                },
                'settings' => function ($value = null) {
                    $config = kirby()->option('oblik.linkField.settings');

                    if (is_array($value) && is_array($config)) {
                        $value = array_replace_recursive($config, $value);
                    } else if ($value === null) {
                        if (is_bool($config)) {
                            $value = $config;
                        } else {
                            $value = true;
                        }
                    }

                    return $value;
                },
                'pages' => function ($value = []) {
                    return array_merge([
                        'query' => 'site.pages'
                    ], $value);
                },
                'files' => function ($value = []) {
                    return array_merge([
                        'query' => 'site.files.add(site.index.files)'
                    ], $value);
                }
            ],
            'methods' => [
                'pageResponse' => function ($page) {
                    $config = $this->pages() ?? [];
                    $settings = array_intersect_key($config, [
                        'image' => true,
                        'info' => true,
                        'text' => true
                    ]);

                    return $page->panel()->pickerData($settings);
                },
                'fileResponse' => function ($file) {
                    $config = $this->files() ?? [];
                    $settings = array_intersect_key($config, [
                        'image' => true,
                        'info' => true,
                        'text' => true
                    ]);

                    return $file->panel()->pickerData($settings);
                }
            ],
            'api' => function () {
                return [
                    [
                        'pattern' => '/link-pages',
                        'method' => 'GET',
                        'action' => function () {
                            $config = $this->field()->pages();
                            $settings = array_merge($config, [
                                'page' => $this->requestQuery('page'),
                                'parent' => $this->requestQuery('parent'),
                                'search' => $this->requestQuery('search')
                            ]);

                            return $this->field()->pagepicker($settings);
                        }
                    ],
                    [
                        'pattern' => '/link-files',
                        'method' => 'GET',
                        'action' => function () {
                            $config = $this->field()->files();
                            $settings = array_merge($config, [
                                'page'   => $this->requestQuery('page'),
                                'search' => $this->requestQuery('search'),
                            ]);

                            return $this->field()->filepicker($settings);
                        }
                    ]
                ];
            },
            'save' => function ($data) {
                $data = array_filter($data);
                $type = $data['type'] ?? null;
                $value = $data['value'] ?? null;

                if (!$type || !$value) {
                    return null;
                }

                // Store just the id of a file or a page.
                if ($type === 'page') {
                    $data['value'] = ($value[0]['id'] ?? null) ? '/' . $value[0]['id'] : null;
                } else if ($type === 'file') {
                    $data['value'] = $value[0]['id'] ?? null;
                }

                return $data['value'];
            }
        ]
    ],
    'translations' => [
        'en' => require_once __DIR__ . '/languages/en.php',
        'de' => require_once __DIR__ . '/languages/de.php'
    ],
    'fieldMethods' => [
        'url' => function ($field) {
            if ($field->isEmpty()) {
                return null;
            }

            $data = $field->yaml();

            if (!is_array($data)) {
                $data = [];
            }

            if (empty($data['type'])) {
                $data['type'] = 'url';
            }

            if (empty($data['value'])) {
                $data['value'] = $field->value();
            }

            return (new Link($data))->resolveUrl();
        }
    ]
]);
