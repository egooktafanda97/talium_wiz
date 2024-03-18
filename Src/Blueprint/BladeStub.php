<?php

namespace TaliumAbstract\Blueprint;

use Touhidurabir\StubGenerator\Facades\StubGenerator;

class BladeStub
{
    private string $html;
    private array $config;
    private array $template;

    public function __construct(public $data)
    {
        $this->config = $data['config'];
        $this->template = $data['template'];
    }

    public function Identify()
    {
        $template_path = $this->config['component_path'];
        foreach ($this->template as $key => $value) {
            $this->createHtml($template_path, $key, $value);
        }
        dd($template_path, $this->template);
    }

    public function createHtml($template_path, $name, $child)
    {
        $template_name = $name;
        $config = $child['config'];
        $template = $child['blade'];
        $root_path = $config['blueprint']['root_path'] ?? 'public';
        if ($root_path === 'public') {
            if (!file_exists($template_path)) {
                throw new \Exception("File not found " . $template_path);
            }
        } else {
            $template_paths = $root_path . '/' . $config['blueprint']['path'];
            if (!file_exists($template_paths)) {
                throw new \Exception("File not found " . $template_paths);
            }
        }

        $html = $this->parseNode($template, 0, $config);
//        dd($html);
        //cek directory
        if (!file_exists(resource_path($config['resource']['path']))) {
            mkdir(resource_path($config['resource']['path']), 0777, true);
        }
        return StubGenerator::from(__DIR__ . '/stub/Blade/Dinamic.stub', true)
            ->to(resource_path($config['resource']['path']))
            ->as($config['resource']['filename'])
            ->withReplacers([
                "children" => $html
            ])->save();

    }

    public function parseNode($node, $indent = 0, $config = [])
    {
        $output = '';
        $template_paths = $config['blueprint']['path'];
        $root_path = $config['blueprint']['root_path'] ?? 'public';
        $folder = $template_paths;

        foreach ($node as $key => $value) {
            if (is_array($value) && isset($value['children'])) {
                // Jika value adalah array dan memiliki children, rekursif parseNode
                $value['children'] = $this->parseNode($value['children'], $indent + 1, $config);
                if (is_string($key) && strpos($key, '$') !== false) {
                    // Jika key adalah tag HTML, transformasi ke HTML dan tambahkan ke output
                    $html = $this->transformKeyToHTML($key, $value);
                    file_put_contents(__DIR__ . '/stub/Blade/Temp.stub', $html . PHP_EOL);
                    try {
                        $output .= StubGenerator::from(__DIR__ . '/stub/Blade/Temp.stub', true)
                            ->replace(true)
                            ->withReplacers($this->merge_value($value))->toString();
                    } catch (\Exception $e) {
                        dd("error parseNode ", $key, $value, $html);
                    }
                    file_put_contents(__DIR__ . '/stub/Blade/Temp.stub', '');
                } else {
                    $output .= $this->processComponentNode($key, $value, $folder, $root_path, $config);
                }
            } else {
                // Jika value bukan array atau tidak memiliki children
                if (is_string($key) && strpos($key, '$') !== false) {
                    // Jika key adalah tag HTML, transformasi ke HTML tanpa key-value dan tambahkan ke output
                    $output .= $this->transformKeyToHTMLNoKeyVal($key, $value);
                } else {
                    // Jika bukan tag HTML, tulis ke file stub
                    $output .= $this->processNonHTMLNode($key, $value, $folder, $root_path, $config);
                }
            }
        }
        return $output;
    }

    public function merge_value($value): array
    {
        $output = [];
        foreach ($value as $key => $val) {
            if (is_array($val)) {
                $output = array_merge($output, $this->merge_value($val));
            } else {
                $output[$key] = $val;
            }
        }
        return $output;
    }

    public function processComponentNode($component, $value, $folder, $root_path, $config)
    {
        // Memproses node yang bukan tag HTML dan menuliskannya ke file stub
        if (is_array($value)) {
            // Jika value adalah array, loop semua komponen dan tulis ke file stub
            $output = '';
            try {
                $file = $folder . '/' . $component . $config['blueprint']['extension'];
                $isFile = $root_path === 'public' ? public_path($file) : $file;
                if (!file_exists($isFile)) {
                    throw new \Exception("File not found " . $isFile);
                }
                $props = $value['props'] ?? [];
                $output .= StubGenerator::from($file)
                    ->withReplacers(array_merge($props, ['children' => $value['children']]))->toString();
            } catch (\Exception $e) {
                dd("error processComponentNode ", [$component, $value]);
            }
            return $output;
        } else {
            // Jika value bukan array, tulis langsung ke file stub
            $file = $folder . '/' . $key . $config['blueprint']['extension'];
            $isFile = $root_path === 'public' ? public_path($file) : $file;
            if (!file_exists($isFile)) {
                throw new \Exception("File not found " . $isFile);
            }
            return StubGenerator::from($file)
                ->withReplacers($value)->toString();
        }
    }

    public function processNonHTMLNode($key, $value, $folder, $root_path, $config)
    {
        if (is_array($value)) {
            $output = '';
            foreach ($value as $component => $props) {
                try {
                    $file = $folder . '/' . $component . $config['blueprint']['extension'];
                    $isFile = $root_path === 'public' ? public_path($file) : $file;
                    if (!file_exists($isFile)) {
                        throw new \Exception("File not found " . $isFile);
                    }
                    $output .= StubGenerator::from($file)
                        ->withReplacers($props)->toString();
                } catch (\Exception $e) {
                    dd("error processNonHTMLNode ", [$component, $value]);
                }
            }
            return $output;
        } else {
            $file = $folder . '/' . $key . $config['blueprint']['extension'];
            $isFile = $root_path === 'public' ? public_path($file) : $file;
            if (!file_exists($isFile)) {
                throw new \Exception("File not found " . $isFile);
            }
            return StubGenerator::from($file)
                ->withReplacers($value)->toString();
        }
    }

    function transformKeyToHTML($structure, $value)
    {
        $cleaned_string = preg_replace('/\?/', '', $structure);
        $el = explode('>', $cleaned_string);
        $html = $this->createElement($el, $value);
        return $html;
    }


    public function createElement($strTag, $attr = null)
    {
        $html = '';
        $endTag = '';
        for ($i = 0; $i < count($strTag); $i++) {
            $elemetOpen = $this->regexElement($strTag[$i], $attr);
            $html .= $elemetOpen;
            $endTag .= "</" . $this->getTag($strTag[$i]) . ">";
        }
        return $html . '{{ children }}' . $endTag;
    }

    public function getTag($el)
    {
        preg_match('/^\$\(([^)]+)\)/', $el, $matches1);
        if (!empty($matches1[1])) {
            return $matches1[1];
        }
        return null;
    }

    public function regexElement($el, $value = [])
    {
        $tag = null;
        preg_match('/^\$\(([^)]+)\)/', $el, $matches1);
        if (!empty($matches1[1])) {
            $tag .= "<" . $matches1[1];
        }
        preg_match_all('/\.\w+(?:-\w+)?/', $el, $matches2);
        if (!empty($matches2[0])) {
            $tag .= ' class="' . (implode(' ', array_map(function ($class) {
                    return str_replace(".", "", $class);
                }, $matches2[0])) ?? null) . '"';
        }
        preg_match('/#(\w+)?/', $el, $matches3);
        if (!empty($matches3[1])) {
            $tag .= ' id="' . $matches3[1] . '"';
        }
        $attributes = '';
        if (count($value) > 0 && !empty($value['props'])) {
            collect($value['props'])->map(function ($value, $key) use (&$attributes) {
                $attributes .= $key . '="' . $value . '" ';
            });
            $tag .= ' ' . $attributes;
        }
        return $tag . ">";
    }

    public function transformKeyToHTMLNoKeyVal($key, $value)
    {

        $html = $this->regexElement($key, $value);
        if (isset($value['children'])) {
            $html .= $value['children'];
        }
        if (isset($value['text'])) {
            $html .= $value['text'];
        }
        if (is_string($value)) {
            $html .= $value;
        }
        // Menutup tag HTML
        $html .= "</" . $this->getTag($key) . ">";
        return $html;
    }

}
